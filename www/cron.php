<?php
class initBase
{
    const BASE_TMP_NAME = 'base_tmp.xml';
    const BASE_NAME = 'base_db.xml';
    const BACKUP_FOLDER = 'backup/';
    const PROJECT_DIR = '/home/developer/dev/projects/test.loc';
    const MAX_BACKUP_FILE = '20';
    const CONFIG_PATH = 'base_url.ini';

    private $backupCreate = false;
    private $backupName = '';
    private $dbh = null;
    private $config = array();

    public function __construct()
    {
        chdir(self::PROJECT_DIR);
        $this->config = parse_ini_file(self::CONFIG_PATH, true);
    }

    public function updateBase()
    {
        foreach ($this->getBaseUrl() as $shopName => $baseUrl) {
            echo "Begin download {$shopName}: ". time();
            if ($this->downloadBase($baseUrl, $shopName)){
                echo "Begin DB {$shopName}: ". time();
                $this->updateDB($shopName);
                echo "Begin remove {$shopName}: ". time();
                $this->removeTmp();
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

    private function fileExists($baseUrl){
        $file_headers = @get_headers($baseUrl);
        if(strpos($file_headers[0], 'HTTP/1.1 200 OK') === false) {
            return false;
        }
        else {
            return true;
        }
    }
    private function setupBackup($shopName)
    {
        if ($backupName = $this->getLastBackup($this->prependBackupFolder($shopName))) {
            copy(self::PROJECT_DIR . '/' . $this->config['backup']['folder'] . $shopName . '/' . $backupName, self::BASE_NAME);
        }
    }

    private function getLastBackup($filesList)
    {
        if ($filesList) {
            return array_pop($filesList);
        }
        return false;
    }

    private function prependBackupFolder($shopName)
    {
        chdir(self::PROJECT_DIR . '/' . $this->config['backup']['folder'] . $shopName);
        $filesList = glob('*.xml');
        if (count($filesList) > $this->config['backup']['max_backup_file']) {
            foreach (array_slice($filesList, $this->config['backup']['max_backup_file']) as $fileName) {
                unlink($fileName);
            }
        }
        chdir(self::PROJECT_DIR);
        return $filesList;
    }

    private function makeBackup($shopName)
    {
        $this->backupName = $this->config['backup']['folder'] . $shopName . '/' . date('YmdHi') . '.xml';
        if (!file_exists(self::PROJECT_DIR . '/' . $this->config['backup']['folder'] . $shopName)) {
            mkdir(self::PROJECT_DIR . '/' . $this->config['backup']['folder'] . $shopName, 0777);
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
                $this->dbh = new PDO(sprintf(
                    "mysql:host=%s;dbname=%s;charset=UTF8",
                    $this->config['db']['db_host'],
                    $this->config['db']['db_name']
                ), $this->config['db']['login'], $this->config['db']['password']);
                $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $newData = simplexml_load_file(self::BASE_NAME);
                $shopId = $this->getShopId($shopName, (string)$newData->shop->url);
                $this->updateCategories($shopId, $newData->shop->categories);
                $this->updateOffers($shopId, $newData->shop->offers);

            } catch (PDOException $e) {
                die("Error: " . $e->getMessage());
            }
        }
    }
    private function getShopId($shopName, $url = ''){
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
            "INSERT INTO categories (category_id, shop_id,parent_id, title) VALUES (:category_id, :shop_id, :parent_id, :title) ON DUPLICATE KEY UPDATE parent_id=:parent_id, title=:title"
        );
        $this->dbh->beginTransaction();
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
        $this->dbh->commit();
    }

    private function updateOffers($shopId, $offers)
    {
        $stmt = $this->dbh->prepare(
            "INSERT INTO goods (offer_id, category_id,shop_id, is_available, url, price, currency, picture, title, common_data) VALUES (:offer_id, :category_id, :shop_id, :is_available, :url, :price, :currency, :picture, :title, :common_data) ON DUPLICATE KEY UPDATE category_id=:category_id, is_available=:is_available, url=:url, price=:price, currency=:currency, picture=:picture, title=:title, common_data=:common_data"
        );
        $this->dbh->beginTransaction();
        foreach ($offers->children() as $offer) {
            $stmt->execute(
                array(
                    'offer_id' => (string)$offer->attributes()->id,
                    'category_id' => (int)$offer->categoryId,
                    'shop_id' => (int)$shopId,
                    'is_available' => (boolean)$offer->attributes()->available,
                    'url' => (string)$offer->url,
                    'price' => (string)$offer->price,
                    'currency' => (string)$offer->currencyId,
                    'picture' => (string)$offer->picture,
                    'title' => (string)$offer->model,
                    'common_data' => json_encode((array)$offer)
                )
            );
        }
        $this->dbh->commit();
        return true;
    }

}

$db = new initBase();
$db->updateBase();