<?php
namespace library;

use library\WidgetAbstract;

class XmlLoadWidget extends WidgetAbstract
{
    const BASE_PATH = '../base_db.xml';

    protected function getOffers($widgetsId)
    {
        $offers = array();
        if (file_exists(self::BASE_PATH)) {
            $config = simplexml_load_file(self::BASE_PATH);
            foreach($config->shop->offers->offer as $offer){
                if (in_array((string)$offer->attributes()->id, $widgetsId)) {
                    $offers[] = array_merge(json_decode(json_encode((array)$offer), true), array('offer_id' => (string)$offer->attributes()->id));
                }
            }
        }
        return $offers;
    }
}