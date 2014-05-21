<?php
function __autoload($class_name)
{
    require_once __DIR__ . '/' . str_replace('\\', '/', $class_name) . '.php';
}

use library\Config;
use model\Categories;


class initBase
{
    const BASE_TMP_NAME = 'base_tmp.xml';
    const BASE_NAME = 'base_db.xml';
    const SHOP_URL = 'sweetme.com';
    const CATEGORY_TITLE = 'Общая категория';
    const PARAM_TITLE = 'Размер';
    private $backupName = '';
    private $projectDir = __DIR__;

    /**
     * @var \PDO
     */
    private $dbh;
    private $config = array();

    public function __construct()
    {
        chdir($this->projectDir);
        $this->config = Config::getInstance()->getConfig();
    }

    public function updateBase()
    {
        if (!Config::getInstance()->getBusyStatus()){
            Config::getInstance()->setBusyStatus(true);
            foreach ($this->getBaseUrl() as $shopName => $baseUrl) {
                if ($this->downloadBase($baseUrl, $shopName)) {
                    $this->updateDB($shopName);
                    $this->removeTmp();
                }
            }
        }
    }

    private function getBaseUrl()
    {
        return $this->config['sweet_me'];
    }

    private function downloadBase($baseUrl, $shopName)
    {
        if ($this->fileExists($baseUrl) && @copy($baseUrl, self::BASE_TMP_NAME)) {
            rename(self::BASE_TMP_NAME, self::BASE_NAME);
        } else {
            return false;
        }
        $this->makeBackup($shopName);
        return true;
    }

    private function fileExists($baseUrl)
    {
        $file_headers = @get_headers($baseUrl);
        if (strpos($file_headers[0], 'HTTP/1.1 200 OK') === false) {
            return false;
        } else {
            return true;
        }
    }

    private function setupBackup($shopName)
    {
        if ($backupName = $this->getLastBackup($this->prependBackupFolder($shopName))) {
            copy(
                $this->projectDir . '/' . $this->config['backup']['folder'] . $shopName . '/' . $backupName,
                self::BASE_NAME
            );
        }
    }

    private function getLastBackup($filesList)
    {
        if ($filesList) {
            return array_shift($filesList);
        }
        return false;
    }

    private function prependBackupFolder($shopName)
    {
        chdir($this->projectDir . '/' . $this->config['backup']['folder'] . $shopName);
        $filesList = glob('*.xml');
        rsort($filesList);
        if (count($filesList) > $this->config['backup']['max_backup_file']) {
            foreach (array_slice($filesList, $this->config['backup']['max_backup_file']) as $fileName) {
                unlink($fileName);
            }
        }
        chdir($this->projectDir);
        return $filesList;
    }

    private function makeBackup($shopName)
    {
        $this->backupName = $this->config['backup']['folder'] . $shopName . '/' . date('YmdHi') . '.xml';
        if (!file_exists($this->projectDir . '/' . $this->config['backup']['folder'] . $shopName)) {
            mkdir($this->projectDir . '/' . $this->config['backup']['folder'] . $shopName, 0777);
        }
        $this->prependBackupFolder($shopName);
        if (@copy(self::BASE_NAME, $this->backupName)) {
            chmod($this->backupName, 0777);
        }
    }

    private function removeTmp()
    {
        unlink(self::BASE_NAME);
    }

    private function updateDB($shopName)
    {
        if (file_exists(self::BASE_NAME)) {
            try {
                $this->dbh = Config::getInstance()->getDbConnection();
                $newData = simplexml_load_file(self::BASE_NAME);
                $shopId = $this->getShopId($shopName, self::SHOP_URL);
                $this->preparedAction($shopId);
                $this->updateCategories($shopId);
                $this->updateWithTmpTable($shopId, $newData);
                $this->addParamsListForCategory($shopId);
            } catch (\Exception $e) {
                var_dump($e->getMessage(), $e->getLine(), $e->getFile());
                $this->setupBackup($shopName);
            }
        } else {
            $this->setupBackup($shopName);
        }
    }

    private function preparedAction($shopId)
    {
        $this->dbh->exec("DROP TABLE IF EXISTS goods_param_tmp");
        $this->dbh->exec("CREATE TABLE goods_param_tmp LIKE goods_param");
        $this->dbh->exec("INSERT INTO goods_param_tmp SELECT * FROM goods_param WHERE shop_id<>{$shopId}");
    }

    private function addParamsListForCategory($shopId)
    {
        $categoriesModel = new Categories($this->dbh);
        $categoriesModel->setCategoryList($categoriesModel->getCategoriesList(array('shopId' => $shopId)));
        foreach ($categoriesModel->getCategoryWithChildList() as $catId => $childs){
            $categoriesModel->addCategoriesParam($shopId, $catId, $childs);
        }

    }

    private function getShopId($shopName, $url = '')
    {
        $STH = $this->dbh->prepare('SELECT id from shops WHERE title = :shop_name LIMIT 1');
        $STH->bindValue(':shop_name', $shopName);
        $STH->execute();
        if (!$shopId = $STH->fetch()) {
            $shopId['id'] = $this->addShop($shopName, $url);
        }
        return $shopId['id'];
    }

    private function addShop($shopName, $url)
    {
        $stmt = $this->dbh->prepare("INSERT INTO shops (title, url) values (:title, :url)");
        $stmt->bindValue(':title', $shopName);
        $stmt->bindValue(':url', $url);
        $stmt->execute();
        return $this->dbh->lastInsertId();
    }

    private function updateCategories($shopId)
    {
        $stmt = $this->dbh->prepare(
            "INSERT LOW_PRIORITY INTO categories (category_id, shop_id,parent_id, title) VALUES (:category_id, :shop_id, :parent_id, :title) ON DUPLICATE KEY UPDATE parent_id=:parent_id, title=:title"
        );
        $stmt->execute(
            array(
                'category_id' => 1,
                'shop_id' => (int)$shopId,
                'parent_id' => 0,
                'title' => self::CATEGORY_TITLE
            )
        );
    }

    private function updateWithTmpTable($shopId, $offers)
    {
        $this->createTmpTable();
        $this->copyDataToTmpTable();
        $this->resetAvailableValue($shopId);
        $this->updateDataInTmpTable($shopId, $offers);
        $this->dbh->prepare('DROP TABLE goods')->execute();
        $this->dbh->prepare('RENAME TABLE goods_tmp TO goods')->execute();
        $this->dbh->exec('DROP TABLE goods_param');
        $this->dbh->exec('RENAME TABLE goods_param_tmp TO goods_param');
        return true;
    }

    private function updateDataInTmpTable($shopId, $offers)
    {
        $offerUpdate = $this->dbh->prepare(
            "INSERT INTO goods_tmp (offer_id, category_id,shop_id, is_available, url, price, title, common_data) VALUES (:offer_id, :category_id, :shop_id, :is_available, :url, :price, :title, :common_data) ON DUPLICATE KEY UPDATE is_available=:is_available, url=:url, price=:price, title=:title, common_data=:common_data"
        );
        foreach ($offers as $offer) {
            $data = $this->prepareCommonData($offer);
            $offerUpdate->execute(
                array(
                    ':offer_id' => $data['offer_id'],
                    ':category_id' => 1,
                    ':shop_id' => (int)$shopId,
                    ':is_available' => 1,
                    ':url' => $data['url'],
                    ':price' => $data['price'],
                    ':title' => $data['desc'] . ' ' . $data['brand'],
                    ':common_data' => serialize($this->prepareCommonData($offer)),
                )
            );
            $this->addParamsForOffer($shopId,  $data['skuNumber'], 1, $offer);
        }
    }

    private function addParamsForOffer($shopId, $offerId, $categoryId, $data)
    {
        $paramId = null;
        $queryNewParam = $this->dbh->prepare("INSERT INTO params (title) VALUES(:title)");
        $queryParamExists = $this->dbh->prepare("SELECT id FROM params WHERE title LIKE :title");
        $queryAddValue = $this->dbh->prepare("INSERT INTO goods_param_tmp (shop_id, offer_id, category_id, param_id, value) VALUES(:shop_id, :offer_id, :category_id, :param_id, :value) ON DUPLICATE KEY UPDATE value=:value");
        $queryParamExists->execute(array(':title' => self::PARAM_TITLE));
        if (!$id = $queryParamExists->fetch(PDO::FETCH_ASSOC)){
            $queryNewParam->execute(array(':title' => self::PARAM_TITLE));
            $paramId = $this->dbh->lastInsertId();
        } else {
            $paramId = $id['id'];
        }
        foreach ($data->available->variant as $value) {
            $params = $value->attributes();
            $queryAddValue->execute(array(
                    ':shop_id' => $shopId,
                    ':offer_id' => $offerId,
                    ':category_id' => $categoryId,
                    ':param_id' => $paramId,
                    ':value' => $params['code-ru']
                ));
        }
    }

    private function prepareCommonData($data)
    {
        $params = [];
        foreach ($data->attributes() as $key => $value) {
            $params['attributes'][$key] = (string)$value;
        }
        $preparedData = array();
        foreach ($data as $key=>$value){
            $preparedData[(string)$key] = (string)$value;
            if ($key == 'skuNumber') {
                $preparedData['offer_id'] = (string)$value;
                $params['attributes']['id'] = (string)$value;
            }
        }
        return array_merge($preparedData, $params);
    }

    private function resetAvailableValue($shopId)
    {
        $offerAvaliableReset = $this->dbh->prepare('UPDATE goods_tmp SET is_available = 0 WHERE shop_id=:shop_id');
        $offerAvaliableReset->bindValue(':shop_id', $shopId);
        $offerAvaliableReset->execute();
    }

    private function createTmpTable()
    {
        $this->dbh->exec("DROP TABLE IF EXISTS goods_tmp ");
        $this->dbh->prepare($this->createTmpTableCode('goods_tmp'))->execute();
    }

    private function copyDataToTmpTable()
    {
        $copyDataInTmpTable = $this->dbh->prepare('INSERT INTO goods_tmp SELECT * FROM goods');
        $copyDataInTmpTable->execute();
    }

    private function createTmpTableCode($tableName)
    {
        return <<<EOL
CREATE TABLE `{$tableName}` LIKE goods
EOL;
    }

    public function __destruct()
    {
        Config::getInstance()->setBusyStatus(false);
        $this->dbh = null;
    }
}

$db = new initBase();
$db->updateBase();