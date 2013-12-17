<?php
if (isset($_GET['picture_id'])){
    $memcache = new Memcache();
    $memcache->addServer('localhost', 11211);
    if (false !== $img = $memcache->get(strip_tags($_GET['picture_id']))){
        header('Content-type: image/png');
        echo $img;
    } elseif (isset($_GET['picture_custom'])) {
        header('Content-type: image/png');
        echo file_get_contents(strip_tags($_GET['picture_custom']));
    }
}
