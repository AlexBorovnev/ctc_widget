<?php
namespace library;
use model\Categories;

class UpdateDB
{
    /**
     * @var \PDO
     */
    private $dbh;
    private $config = array();
    private $pathToXml;
    private $shopName;
    private $shopUrl;
    private $offerInsertData = array();
    private $offerInsertQuery = '';
    private $elementsInOneRow = 10;
    private $patchElements = 170;
    private $categoryTitleType;
    /**
     * @var XMLParser
     */
    private $xmlParser;

    public function __construct($shopName, $shopUrl)
    {
        $this->shopName = $shopName;
        $this->shopUrl = $shopUrl;
        $this->config = Config::getInstance()->getConfig();
        $this->dbh = Config::getInstance()->getDbConnection();
    }

    public function setParser($baseName)
    {
        $this->pathToXml = __DIR__ . '/../' . $baseName;
        $this->xmlParser = $this->getParser($this->shopUrl);
    }

    public function beforeAddAction()
    {
        try{
            $this->createTmpTable('goods_tmp', 'goods');
            $this->copyDataToTmpTable('goods_tmp', 'goods');
            $this->createTmpTable('categories_tmp', 'categories');
            $this->copyDataToTmpTable('categories_tmp', 'categories');
            $this->createTmpTable('goods_param_tmp', 'goods_param');
            $shopId = $this->getShopId($this->shopName, $this->shopUrl);
            $this->dbh->exec("INSERT INTO goods_param_tmp SELECT * FROM goods_param WHERE shop_id<>{$shopId}");
            $this->dbh->exec("UPDATE LOW_PRIORITY goods_tmp SET is_available = 0 WHERE shop_id={$shopId}");
            return true;
        } catch(\Exception $e){
            Config::getInstance()->setBusyStatus(false);
            echo $e->getMessage();
            echo $e->getLine();
            echo $e->getFile();
            return false;
        }

    }

    public function afterAddAction()
    {
        try {
            $shopId = $this->getShopId($this->shopName, $this->shopUrl);
            $this->dbh->exec('DROP TABLE categories');
            $this->dbh->exec('RENAME TABLE categories_tmp TO categories');
            $this->dbh->exec('DROP TABLE goods');
            $this->dbh->exec('RENAME TABLE goods_tmp TO goods');
            $this->dbh->exec('DROP TABLE goods_param');
            $this->dbh->exec('RENAME TABLE goods_param_tmp TO goods_param');
            $categoriesModel = new Categories($this->dbh);
            $categoriesModel->setCategoryList($categoriesModel->getCategoriesList(array('shopId' => $shopId)));
            $categoryChilds = $categoriesModel->getCategoryWithChildList();
            $categoriesModel->setPrepareQuery($this->dbh->prepare("SELECT p.title FROM goods_param gp JOIN params p ON p.id=gp.param_id WHERE gp.category_id=:cat_id AND gp.shop_id=:shop_id GROUP BY p.title"));
            foreach ($categoryChilds as $catId => $childs){
                $categoriesModel->addCategoriesParam($shopId, $catId, $childs);
            }
        } catch (\Exception $e) {
            Config::getInstance()->setBusyStatus(false);
        }
    }

    public function updateBase($titleType)
    {
        if ($this->downloadedBase()) {
            $this->categoryTitleType = $titleType;
            $this->updateDB();
        }
    }

    private function downloadedBase()
    {
        if (file_exists($this->pathToXml)){
            return true;
        }
        return false;
    }


    private function updateDB()
    {
        try{
            $shopId = $this->getShopId($this->shopName, $this->shopUrl);
            while ($row = $this->xmlParser->getValues()){
                switch (key($row)){
                    case 'currency':
                        $this->addCurrency($shopId, $row['currency']);
                        break;
                    case 'category':
                        $this->updateCategories($shopId, $row['category']);
                        break;
                    case 'offer':
                        $this->updateDataInTmpTable($shopId, $row['offer']);
                        break;
                    default:
                        throw new \Exception('undefined key');
                }
            }
            $this->savePatchToDb();
        } catch (\Exception $e){
            echo $e->getMessage();
            echo $e->getLine();
            echo $e->getFile();
        }
        $this->removeTmp();
    }

    private function addCurrency($shopId, $data = array())
    {
        $currencyList = array();
        $currencyQuery = $this->dbh->prepare(
            'INSERT INTO currency (currency_id, rate, shop_id) VALUES (:currency_id, :rate, :shop_id) ON DUPLICATE KEY UPDATE rate=:rate'
        );
        $currencyList[$shopId][$data['attributes']['id']] = $data['attributes']['rate'];
        $currencyQuery->execute(
            array(
                ':currency_id' => $data['attributes']['id'],
                ':rate' => $data['attributes']['rate'],
                ':shop_id' => $shopId
            )
        );
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
        $stmt = $this->dbh->prepare("INSERT INTO shops (title, url, prefix) values (:title, :url, :prefix)");
        $prefix = mb_strtolower(str_replace('.', '', $shopName));
        $stmt->bindValue(':title', $shopName);
        $stmt->bindValue(':url', $url);
        $stmt->bindValue(':prefix', $prefix);
        $stmt->execute();
        return $this->dbh->lastInsertId();
    }

    private function updateCategories($shopId, $category)
    {
        $stmt = $this->dbh->prepare(
            "INSERT LOW_PRIORITY INTO categories_tmp (category_id, shop_id,parent_id, title, title_type) VALUES (:category_id, :shop_id, :parent_id, :title, :title_type) ON DUPLICATE KEY UPDATE parent_id=:parent_id, title=:title, title_type=:title_type"
        );
        $stmt->execute(
            array(
                'category_id' => $category['attributes']['id'],
                'shop_id' => (int)$shopId,
                'parent_id' => empty($category['attributes']['parentId']) ? 0 : $category['attributes']['parentId'],
                'title' => $category['textValue'],
                'title_type' => $this->categoryTitleType
            )
        );
    }

    private function updateDataInTmpTable($shopId, $offer)
    {
        if (($this->patchElements*$this->elementsInOneRow) > $count = count($this->offerInsertData)){
            $this->addOffer($offer, $shopId, $count);
        } else {
            $this->addOffer($offer, $shopId, $count);
            $this->savePatchToDb();
        }
    }

    protected function savePatchToDb()
    {
        if ($this->offerInsertData){
            $queryInsert = $this->getInsertOfferQuery();
            $offerUpdate = $this->dbh->prepare($queryInsert['insert'] . substr($this->offerInsertQuery, 0 ,-1) .$queryInsert['update']);
            $offerUpdate->execute($this->offerInsertData);
            $this->offerInsertQuery = '';
            $this->offerInsertData = array();
        }
    }

    protected function addOffer($offer, $shopId, $count){
        if (($offer['attributes']['available'] == 'true' || $offer['attributes']['available'] === true)) {
            $title = $this->getTitle($offer);
            foreach ($offer['categoryId'] as $category){

                $preparedData = $this->preparedOfferData($shopId, $offer, $count, $title);
                $preparedData[':category_id' . $count] = $category;
                if (($offer['attributes']['available'] == 'true' || $offer['attributes']['available'] === true)){
                    $this->addParamsForOffer($shopId, $offer['attributes']['id'], $category, $offer);
                }
                $this->offerInsertData = array_merge($this->offerInsertData, $preparedData);
                $this->offerInsertQuery .= " (:offer_id{$count}, :category_id{$count}, :shop_id{$count}, :is_available{$count}, :url{$count}, :price{$count}, :currency{$count}, :picture{$count}, :title{$count}, :common_data{$count}),";
                $count += $this->elementsInOneRow;
            }
        }
    }

    protected function getInsertOfferQuery()
    {
        $insertPart = "INSERT LOW_PRIORITY INTO goods_tmp (offer_id, category_id,shop_id, is_available, url, price, currency, picture, title, common_data) VALUES";
        $updatePart = " ON DUPLICATE KEY UPDATE is_available=VALUES(is_available), url=VALUES(url), price=VALUES(price), currency=VALUES(currency), picture=VALUES(picture), title=VALUES(title), common_data=VALUES(common_data)";
        return array('insert' => $insertPart, 'update' => $updatePart);
    }

    protected function preparedOfferData($shopId, $offer, $index, $title)
    {
        $result = array(
            ':url' . $index => '',
            ':price' . $index => '',
            ':currency' . $index => '',
            ':title' . $index => $title,
            ':picture' . $index => '',
        );
        foreach ($offer as $key => $value) {
            if (is_array($value)) {
                switch ($key) {
                    case 'url':
                        $result[':url' . $index] = $offer['url'][0];
                        break;
                    case 'price':
                        $result[':price' . $index] = $offer['price'][0];
                        break;
                    case 'currencyId':
                        $result[':currency' . $index] = $offer['currencyId'][0];
                        break;
                    case 'picture':
                        $result[':picture' . $index] = serialize($offer['picture']);
                }
            }
        }
        $result[':offer_id' . $index] = $offer['attributes']['id'];
        $result[':common_data' . $index] = serialize($offer);
        $result[':shop_id' . $index] = (int)$shopId;
        $result[':is_available' . $index] = ($offer['attributes']['available'] == 'true' || $offer['attributes']['available'] === true) ? 1 : 0;
        return $result;
    }

    private function getTitle($data)
    {
        $title = '';
        switch($this->categoryTitleType){
            case Categories::MODEL_VENDOR_TITLE:
                if (isset($data['model'][0]) && isset($data['vendor'][0])) {
                    $title = sprintf("%s %s", $data['model'][0], $data['vendor'][0]);
                }
                break;
            case Categories::AUTHOR_TITLE:
                $originalName = '';
                if (isset($data['param']['Оригинальное название'])){
                    $originalName = ' ' . $data['param']['Оригинальное название'];
                }
                if (isset($data['author'][0])) {
                    $title = $data['author'][0] . $originalName;
                }
                break;
            case Categories::NAME_TITLE:
                if (isset( $data['name'][0])) {
                    $title = $data['name'][0];
                }
                break;
        }
        if (!$title){
            if (isset( $data['name'][0])) {
                $title = $data['name'][0];
            }
        }
        if (!$title) {
            if (isset($data['model'][0]) && isset($data['vendor'][0])) {
                $title = sprintf("%s %s", $data['model'][0], $data['vendor'][0]);
            }
        }

        return $title;
    }

    private function createTmpTable($tmpTableName, $sourceTableName)
    {
        $this->dbh->exec("DROP TABLE IF EXISTS {$tmpTableName}");
        $this->dbh->exec($this->createTmpTableCode($tmpTableName, $sourceTableName));
    }

    private function copyDataToTmpTable($tmpTableName, $sourceTableName)
    {
        $this->dbh->exec("INSERT INTO {$tmpTableName} SELECT * FROM {$sourceTableName};");
    }

    private function createTmpTableCode($tmpTableName, $sourceTableName)
    {
        $this->dbh->exec("DROP TABLE IF EXISTS " . $tmpTableName);
        return <<<EOL
CREATE TABLE `{$tmpTableName}` LIKE {$sourceTableName}
EOL;

    }

    public function __destruct()
    {
//        $removeTmpTableQuery = $this->dbh->prepare('SHOW TABLES LIKE "goods_ozon_tmp"');
//        $removeTmpTableQuery->execute();
//        if ($removeTmpTableQuery->fetch()){
//            $this->dbh->prepare('DROP TABLE goods_ozon_tmp')->execute();
//        }
        $this->dbh = null;

       // $this->removeTmp();
    }

    private function removeTmp()
    {
        unlink($this->pathToXml);
    }

    protected function getParser($shopUrl)
    {
        switch ($shopUrl){
            case 'http://www.ozon.ru/':
                return new OzonParser($this->pathToXml);
            default:
                return null;
        }
    }

    private function addParamsForOffer($shopId, $offerId, $categoryId, $data)
    {
        $paramId = null;
        $param = array();
        $queryNewParam = $this->dbh->prepare("INSERT INTO params (title) VALUES(:title)");
        $queryParamExists = $this->dbh->prepare("SELECT id FROM params WHERE title LIKE :title");
        $queryAddValue = $this->dbh->prepare("INSERT INTO goods_param_tmp (shop_id, offer_id, category_id, param_id, value) VALUES(:shop_id, :offer_id, :category_id, :param_id, :value) ON DUPLICATE KEY UPDATE value=:value");
        if (isset($data['param'])){
            $param = array_merge($param, $data['param']);
            array_shift($param);
        }
        foreach ($param as $title => $value) {
            $queryParamExists->execute(array(':title' => $title));
            if (!$data = $queryParamExists->fetch(\PDO::FETCH_ASSOC)){
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
                    ':value' =>  $value
                ));

        }
    }
}
