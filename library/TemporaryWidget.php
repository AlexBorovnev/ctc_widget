<?php
namespace library;

use library\DbLoadWidget;

class TemporaryWidget extends DbLoadWidget{

    protected function getOfferByRule($widgetId){
        $offersQuery = self::$dbh->prepare("SELECT picture, offer_id, url, price FROM goods WHERE offer_id=:widget_id");
        $offersQuery->bindValue(':widget_id', $widgetId);
        $offersQuery->execute();
        return  $offersQuery->fetch(\PDO::FETCH_ASSOC);
    }

    protected function getRules($widgetsId){
        return $widgetsId;
    }

    protected function getOffers($widgetId)
    {
        $offers = array();
        foreach ($this->getRules($widgetId) as $rule){
            if ($offer = $this->getOfferByRule($rule)){
                $offers[] = $offer;
            }
        }
        return $offers;
    }
}