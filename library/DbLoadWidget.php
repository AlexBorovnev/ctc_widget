<?php
namespace library;

use library\WidgetAbstract;
use library\Config;

class DbLoadWidget extends WidgetAbstract
{
    /**
     * @var \PDO
     */
    protected $dbh = null;
    public function __construct()
    {
        parent::__construct();
        $this->dbh = Config::getInstance()->getDbConnection();
    }

    protected function getOffers($widgetId)
    {
        $offers = array();
        foreach ($this->getRules($widgetId[0]) as $rule){
            if ($offer = $this->getOfferByRule($rule)){
                $offers[] = $offer;
            }
        }
        return $offers;
    }

    protected function getOfferByRule($rule){
        switch ($rule['rules_type']){
            case self::RULE_TYPE_SINGLE:
                if($offer = $this->getSingleItem($rule)){
                    return $offer;
                }
                return false;
            case self::RULE_TYPE_RULE:
                return $this->getRandomItem($rule);
            default:
                return array();
        }
    }

    protected function getRules($widgetId){
        if ($widget = $this->widgetExists($widgetId)){
            $rulesQuery = $this->dbh->prepare('SELECT * FROM rules WHERE widget_id=:widget_id');
            $rulesQuery->bindValue(':widget_id', $widgetId);
            $rulesQuery->execute();
            return $rulesQuery->fetchAll(\PDO::FETCH_ASSOC);
        }
        return array();
    }

    protected function getSingleItem($rule){
        $getSingleItem = $this->dbh->prepare('SELECT * FROM goods WHERE offer_id=:offer_id AND shop_id=:shop_id AND is_available=1');
        $getSingleItem->bindValue(':offer_id', $rule['source']);
        $getSingleItem->bindValue(':shop_id', $rule['shop_id']);
        $getSingleItem->execute();
        return $getSingleItem->fetch(\PDO::FETCH_ASSOC);
    }

    protected function getRandomItem(){

    }

    protected function widgetExists($widgetId){
        $widgetQuery = $this->dbh->prepare('SELECT COUNT(*) AS rows_num, type_id, skin_id FROM widgets WHERE id=:widget_id');
        $widgetQuery->bindValue(':widget_id', $widgetId);
        $widgetQuery->execute();
        $widget = $widgetQuery->fetch(\PDO::FETCH_ASSOC);
        if ($widget['rows_num'] == 1){
            return $widget;
        } else {
            return false;
        }
    }
}