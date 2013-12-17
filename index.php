<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<?php
$host = 'localhost';
$dbname = 'ctc';
$user = 'root';
$pass = 'sasha';

if (file_exists('stm_domashniy_data.xml')) {

    # подключаемся к базе данных
    try {
        $DBH = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

        $xml = simplexml_load_file('stm_domashniy_data.xml');
        $shop_id = 1;

//        # создаем запрос
//        $STH = $DBH->prepare('SELECT * from categories WHERE shop_id = :shop_id');
//        $STH->bindValue(':shop_id', $shop_id);
//        $STH->execute();
//        # выбираем режим выборки
//        $STH->setFetchMode(PDO::FETCH_OBJ);
//
//        # выводим результат
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


        # создаем запрос
        $STH = $DBH->prepare('SELECT * from goods WHERE shop_id = :shop_id');
        $STH->bindValue(':shop_id', $shop_id);
        $STH->execute();
        # выбираем режим выборки
        $STH->setFetchMode(PDO::FETCH_OBJ);

        # выводим результат
        $offers_in_db = array();
        while($row = $STH->fetch()) {
            $offers_in_db[$row->offer_id] = array(
                    'offer_id'=>$row->offer_id,
                    'category_id'=>$row->category_id,
                    'title'=>$row->title,
                    'is_available'=>$row->is_available,
                    'url'=>$row->url,
                    'picture'=>$row->picture
            );
        }

        $offers_data = array();
        $offers = $xml->shop->offers->offer;
        foreach ($offers as $offer) {
            $offer_id = (string)$offer['id'];

            if(isset($offers_in_db[$offer_id])) {
                $offer_from_db = $offers_in_db[$offer_id];

                $data_from_db = $offer_from_db['offer_id'] . '|'
                        .$offer_from_db['is_available'] . '|'
                        .$offer_from_db['category_id'] . '|'
                        .$offer_from_db['title'] . '|'
                        .$offer_from_db['url'] . '|'
                        .$offer_from_db['picture'];

                $data_from_xml = (string)$offer['id'] . '|'
                        .(int)$offer['available'] . '|'
                        .(int)$offer->categoryId . '|'
                        .(string)$offer->model . '|'
                        .(string)$offer->url . '|'
                        .(string)$offer->picture;

                if($data_from_db != $data_from_xml) {

                    $stmt2 = $DBH->prepare("UPDATE goods SET offer_id = :offer_id, is_available= :is_available,
                        category_id = :category_id, price = :price,
                        title= :title, url = :url, shop_id = :shop_id, common_data = :common_data, currency = :currency,
                        picture = :picture
                        WHERE shop_id = :shop_id and offer_id = :offer_id");

                    $stmt2->bindValue(':offer_id', (string)$offer['id']);
                    $stmt2->bindValue(':is_available', (boolean)$offer['available']);
                    $stmt2->bindValue(':category_id', (int)$offer->categoryId);
                    $stmt2->bindValue(':price', (int)$offer->price);
                    $stmt2->bindValue(':title', (string)$offer->model);
                    $stmt2->bindValue(':url', (string)$offer->url);
                    $stmt2->bindValue(':shop_id', $shop_id);
                    $stmt2->bindValue(':common_data', serialize(array('oldprice'=>(string)$offer->oldprice)));
                    $stmt2->bindValue(':currency', (string)$offer->currencyId);
                    $stmt2->bindValue(':picture', (string)$offer->picture);

                    $stmt2->execute();


                }
                unset($offers_in_db[$offer_id]);

            } else {
                // insert
                $offers_data[] = array(
                        'offer_id' => (string)$offer['id'],
                        'is_available' => (boolean)$offer['available'],
                        'category_id' => (int)$offer->categoryId,
                        'price' => (int)$offer->price,
                        'title' => (string)$offer->model,
                        'url' => (string)$offer->url,
                        'shop_id' => $shop_id,
                        'common_data' => serialize(array('oldprice'=>(string)$offer->oldprice)),
                        'currency' => (string)$offer->currencyId,
                        'picture' => (string)$offer->picture,
                );
            }

        }

        // here we make unavailable all goods which not in xml file
        if(!empty($offers_in_db)) {
            $offer_ids = array_keys($offers_in_db);
            $inQuery = implode(',', array_fill(0, count($offer_ids), '?'));

            $stmt = $DBH->prepare("UPDATE goods SET is_available= ? WHERE shop_id = ? and offer_id IN (" . $inQuery . ")");
            $stmt->bindValue(1, 0);
            $stmt->bindValue(2, $shop_id);
            // bindvalue is 1-indexed, so $k+1
            foreach ($offer_ids as $k => $id) {
                $stmt->bindValue(($k+3), $id);
            }
            $stmt->execute();
        }
        /////////////////////////////////////////////

        // here we adding new goods into database
        if(!empty($offers_data)) {
            pdo_insert2($DBH, 'goods', $offers_data);
        }


    }
    catch(PDOException $e) {
        echo "Хьюстон, у нас проблемы.<br />";
        die("Error: ".$e->getMessage());
    }


} else {
    exit('Не удалось открыть файл stm_domashniy_data.xml.');
}



function pdo_insert($dbh, $table, $arr) {
    if (!is_array($arr) || !count($arr)) return false;

    $sql = "INSERT INTO ".$table." (title, shop_id, category_id, parent_id)
        values (:title, :shop_id, :category_id, :parent_id)";
    $stmt = $dbh->prepare($sql);

    $dbh->beginTransaction();
    foreach($arr as &$row) {
        $stmt->execute($row);
    }
    $dbh->commit();

}

function pdo_insert2($dbh, $table, $arr) {
    if (!is_array($arr) || !count($arr)) return false;

    $sql = "INSERT INTO ".$table." (category_id, shop_id, offer_id, price,currency,picture,common_data,url,is_available,title)
        values (:category_id, :shop_id, :offer_id, :price,:currency,:picture,:common_data,:url,:is_available,:title)";
    $stmt = $dbh->prepare($sql);

    $dbh->beginTransaction();
    foreach($arr as &$row) {
        $stmt->execute($row);
    }
    $dbh->commit();

}



?>
