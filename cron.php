<?php
class initBase
{
    const BASE_TMP_NAME = 'base_tmp.xml';
    const BASE_NAME = 'base.xml';
    const BACKUP_FOLDER = 'backup/';
    const PROJECT_DIR = '/home/developer/dev/projects/test.loc';
    const MAX_BACKUP_FILE = '20';
    const DB_HOST = 'localhost';
    const DB_NAME = 'ctc';
    const DB_USER = 'root';
    const DB_PASS = '1111';
    const CONFIG_PATH = 'base_url.ini';

    private $backupCreate = false;
    private $backupName = '';
    private $dbh = null;

    public function __construct()
    {
        chdir(self::PROJECT_DIR);
    }

    public function updateBase()
    {
        foreach ($this->getBaseUrl() as $shopName => $baseUrl){
            //$this->downloadBase($baseUrl, $shopName);
            $this->updateDB($shopName);
        }
    }

    private function getBaseUrl(){
        if (file_exists(self::CONFIG_PATH)){
            $urls = parse_ini_file(self::CONFIG_PATH);
            return $urls['base_url'];
        }
    }

    private function downloadBase($baseUrl, $shopName)
    {
        $this->makeBackup($shopName);
        if (@copy($baseUrl, self::BASE_TMP_NAME)) {
            rename(self::BASE_TMP_NAME, self::BASE_NAME);
        } elseif ($this->backupCreate) {
            copy($this->backupName, self::BASE_NAME);
        }
    }

    private function makeBackup($shopName)
    {
        $this->backupName = self::BACKUP_FOLDER . $shopName . '/' . date('YmdHi') . '.xml';
        mkdir(self::PROJECT_DIR . '/' . self::BACKUP_FOLDER . $shopName, 0777);
        chdir(self::PROJECT_DIR . '/' . self::BACKUP_FOLDER . $shopName, 0777);

        $filesList = glob('*.xml');
        if (count($filesList) > self::MAX_BACKUP_FILE) {
            unlink($filesList[0]);
        }
        chdir(self::PROJECT_DIR);
        if (@copy(self::BASE_NAME, $this->backupName)) {
            $this->backupCreate = true;
            chmod($this->backupName, 0777);
        }
    }

    private function updateDB()
    {
        if (file_exists(self::BASE_NAME)) {

            try {
                $this->dbh = new PDO(sprintf(
                    "mysql:host=%s;dbname=%s",
                    self::DB_HOST,
                    self::DB_NAME
                ), self::DB_USER, self::DB_PASS);
                $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

                $xml = simplexml_load_file(self::BASE_NAME);
                $shopName = (string)$xml->shop->name;

//        $STH = $DBH->prepare('SELECT * from categories WHERE shop_id = :shop_id');
//        $STH->bindValue(':shop_id', $shop_id);
//        $STH->execute();
//        $STH->setFetchMode(PDO::FETCH_OBJ);
//
//        while($row = $STH->fetch()) {
//            echo $row->shop_id . " \n";
//            echo $row->parent_id . " \n";
//            echo $row->title . "\n <br />";
//        }



                $categories_data = array();
//        $categories = $xml->shop->categories->category;
//        foreach ($categories as $category) {
//
//            $categories_data[] = array(
//                    'shop_id' => $shop_id,
//                    'parent_id' => (int)$category['parentId'],
//                    'category_id' => (int)$category['id'],
//                    'title' => (string)$category
//            );
//
//        }
//        pdo_insert($DBH, 'categories', $categories_data);


                $STH = $this->dbh->prepare('SELECT * from goods WHERE shop_id = :shop_id');
                $STH->bindValue(':shop_id', $shopName);
                $STH->execute();
                $STH->setFetchMode(PDO::FETCH_OBJ);

                while($row = $STH->fetch()) {
                    echo $row->shop_id . " \n";
                    echo $row->category_id . " \n";
                    echo $row->title . "\n <br />";
                }



//        $offers_data = array();
//        $offers = $xml->shop->offers->offer;
//        foreach ($offers as $offer) {
//            $offers_data[] = array(
//                    'offer_id' => (string)$offer['id'],
//                    'is_available' => (boolean)$offer['available'],
//                    'category_id' => (int)$offer->categoryId,
//                    'price' => (int)$offer->price,
//                    'title' => (string)$offer->model,
//                    'url' => (string)$offer->url,
//                    'shop_id' => $shop_id,
//                    'common_data' => serialize(array('oldprice'=>(string)$offer->oldprice)),
//                    'currency' => (string)$offer->currencyId,
//                    'picture' => (string)$offer->picture,
//            );
//
//        }
//        pdo_insert2($DBH, 'goods', $offers_data);

            }
            catch(PDOException $e) {
                die("Error: ".$e->getMessage());
            }


        } else {
            exit('base.xml not exists');
        }




    }

    private function pdo_insert($dbh, $table, $arr) {
        if (!is_array($arr) || !count($arr)) return false;

        $sql = "INSERT INTO ".$table." (title, shop_id, category_id, parent_id)
        values (:title, :shop_id, :category_id, :parent_id)";
        $stmt = $this->dbh->prepare($sql);

        $dbh->beginTransaction();
        foreach($arr as &$row) {
            $stmt->execute($row);
        }
        $dbh->commit();

    }

    private function pdo_insert2($dbh, $table, $arr) {
        if (!is_array($arr) || !count($arr)) return false;

        $sql = "INSERT INTO ".$table." (category_id, shop_id, offer_id, price,currency,picture,common_data,url,is_available,title)
        values (:category_id, :shop_id, :offer_id, :price,:currency,:picture,:common_data,:url,:is_available,:title)";
        $stmt = $this->dbh->prepare($sql);

        $dbh->beginTransaction();
        foreach($arr as &$row) {
            $stmt->execute($row);
        }
        $dbh->commit();

    }
}

$db = new initBase();
$db->updateBase();