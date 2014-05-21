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
        try{

        } catch (Exception $e){
            echo $e->getMessage();
            echo $e->getFile();
            echo $e->getLine();
        }
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
        return $this->config['base_url'];
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
                $shopId = $this->getShopId($shopName, (string)$newData->shop->url);
                $this->preparedAction($shopId);
                $this->addCurrency($shopId, $newData->shop->currencies);
                $this->updateCategories($shopId, $newData->shop->categories);
                $this->updateWithTmpTable($shopId, $newData->shop->offers);
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
    private function addCurrency($shopId, $data = array())
    {
        $currencyList = array();
        $currencyQuery = $this->dbh->prepare('DELETE FROM currency WHERE shop_id=:shop_id');
        $currencyQuery->execute(array(':shop_id' => $shopId));
        $currencyQuery = $this->dbh->prepare(
            'INSERT INTO currency (currency_id, rate, shop_id) VALUES (:currency_id, :rate, :shop_id) ON DUPLICATE KEY UPDATE rate=:rate'
        );
        foreach ($data->currency as $value) {
            $currencyList[$shopId][(string)$value->attributes()->id] = (string)$value->attributes()->rate;
            $currencyQuery->execute(
                array(
                    ':currency_id' => (string)$value->attributes()->id,
                    ':rate' => (string)$value->attributes()->rate,
                    ':shop_id' => $shopId
                )
            );
        }
        return $currencyList;
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

    private function updateCategories($shopId, $categories)
    {
        $stmt = $this->dbh->prepare(
            "INSERT LOW_PRIORITY INTO categories (category_id, shop_id,parent_id, title) VALUES (:category_id, :shop_id, :parent_id, :title) ON DUPLICATE KEY UPDATE parent_id=:parent_id, title=:title"
        );
        foreach ($categories->children() as $category) {
            $stmt->execute(
                array(
                    'category_id' => (int)$category->attributes()->id,
                    'shop_id' => (int)$shopId,
                    'parent_id' => (int)$category->attributes()->parentId,
                    'title' => (string)$category
                )
            );
        }
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
            "INSERT INTO goods_tmp (offer_id, category_id,shop_id, is_available, url, price, currency, picture, title, common_data) VALUES (:offer_id, :category_id, :shop_id, :is_available, :url, :price, :currency, :picture, :title, :common_data) ON DUPLICATE KEY UPDATE is_available=:is_available, url=:url, price=:price, currency=:currency, picture=:picture, title=:title, common_data=:common_data"
        );
        foreach ($offers->children() as $offer) {
            $data = $this->prepareCommonData($offer);
            if ($data['attributes']['available']){
                $offerUpdate->execute(
                    array(
                        ':offer_id' => $data['attributes']['id'],
                        ':category_id' => $data['categoryId'],
                        ':shop_id' => (int)$shopId,
                        ':is_available' => ($data['attributes']['available'] == true) ? 1 : 0,
                        ':url' => $data['url'],
                        ':price' => $data['price'],
                        ':currency' => $data['currencyId'],
                        ':picture' => $data['picture'],
                        ':title' => sprintf("%s %s", $data['model'], $data['vendor']),
                        ':common_data' => serialize($this->prepareCommonData($offer)),
                    )
                );
                $this->addParamsForOffer($shopId,  $data['attributes']['id'], $data['categoryId'], $offer);
            }
        }
    }

    private function addParamsForOffer($shopId, $offerId, $categoryId, $data)
    {
        $paramId = null;
        $queryNewParam = $this->dbh->prepare("INSERT INTO params (title) VALUES(:title)");
        $queryParamExists = $this->dbh->prepare("SELECT id FROM params WHERE title LIKE :title");
        $queryAddValue = $this->dbh->prepare("INSERT INTO goods_param_tmp (shop_id, offer_id, category_id, param_id, value) VALUES(:shop_id, :offer_id, :category_id, :param_id, :value) ON DUPLICATE KEY UPDATE value=:value");
        foreach ($data->param as $value) {
            $title = (string)$value->attributes()->name;
            $queryParamExists->execute(array(':title' => $title));
            if (!$data = $queryParamExists->fetch(PDO::FETCH_ASSOC)){
                $queryNewParam->execute(array(':title' => $title));
                $paramId = $this->dbh->lastInsertId();
            } else {
                $paramId = $data['id'];
            }
            $queryAddValue->execute(array(
                    ':shop_id' => $shopId,
                    ':offer_id' => $offerId,
                    ':category_id' => $categoryId,
                    ':param_id' => $paramId,
                    ':value' => (string) $value
                ));


        }
    }
    private function prepareCommonData($data)
    {
        $paramsNameConvert = array('Цвет' => 'color', 'Размеры' => 'size');
        $params = array('param' => array(), 'attributes' => array());
        foreach ($data->param as $value) {
            $params['param'][$paramsNameConvert[(string)$value->attributes()->name]] = (string)$value;
        }
        foreach ($data->attributes() as $key => $value) {
            $params['attributes'][$key] = (string)$value;
        }
        $preparedData = array();
        foreach ($data as $key=>$value){
            $preparedData[(string)$key] = (string)$value;
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