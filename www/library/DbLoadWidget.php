<?php
namespace library;

use library\WidgetAbstract;

class DbLoadWidget extends WidgetAbstract
{
    const RULE_TYPE_SINGLE = 2;
    const RULE_TYPE_RANDOM = 1;

    /**
     * @var \PDO
     */
    protected static $dbh = null;
    public function __construct()
    {
        parent::__construct();
        self::$dbh = new \PDO(sprintf(
            "mysql:host=%s;dbname=%s;charset=UTF8",
            $this->config['db']['db_host'],
            $this->config['db']['db_name']
        ), $this->config['db']['login'], $this->config['db']['password']);
        self::$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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
            case self::RULE_TYPE_RANDOM:
                return $this->getRandomItem($rule);
            default:
                return array();
        }
    }

    protected function getRules($widgetId){
        if ($widget = $this->widgetExists($widgetId)){
            $rulesQuery = self::$dbh->prepare('SELECT * FROM rules WHERE widget_id=:widget_id');
            $rulesQuery->bindValue(':widget_id', $widgetId);
            $rulesQuery->execute();
            return $rulesQuery->fetchAll(\PDO::FETCH_ASSOC);
        }
        return array();
    }

    protected function getSingleItem($rule){
        $getGingleItem = self::$dbh->prepare('SELECT * FROM goods WHERE offer_id=:offer_id AND shop_id=:shop_id AND is_available=1');
        $getGingleItem->bindValue(':offer_id', $rule['source']);
        $getGingleItem->bindValue(':shop_id', $rule['shop_id']);
        $getGingleItem->execute();
        return $getGingleItem->fetch(\PDO::FETCH_ASSOC);
    }

    protected function getRandomItem(){

    }

    protected function widgetExists($widgetId){
        $widgetQuery = self::$dbh->prepare('SELECT COUNT(*) AS rows_num, type_id, skin_id FROM widgets WHERE id=:widget_id');
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