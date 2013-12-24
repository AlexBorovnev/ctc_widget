<?php
namespace library;

use library\WidgetAbstract;
use library\Config;
use library\Common;

class DbLoadWidget extends WidgetAbstract
{
    /**
     * @var \PDO
     */
    protected $dbh = null;

    public function __construct(array $config, \PDO $connection)
    {
        parent::__construct($config);
        $this->dbh = $connection;
    }

    protected function getOffers($widgetId)
    {
        $offers = array();
        foreach ($this->getRules($widgetId) as $rule) {
            if ($offer = $this->getOfferByRule($rule)) {
                $offers[] = $offer;
            }
        }
        return $offers;
    }

    protected function getOfferByRule($rule)
    {
        switch ($rule['rules_type']) {
            case self::RULE_TYPE_SINGLE:
                return $this->getSingleItem($rule);
            case self::RULE_TYPE_RULE:
                return $this->getRandomItem($rule['shop_id'], $rule['source']);
            default:
                return array();
        }
    }

    protected function getRules($widgetId)
    {
        $rulesQuery = $this->dbh->prepare('SELECT * FROM rules r JOIN widgets w ON w.id=r.widget_id WHERE widget_id=:widget_id');
        $rulesQuery->bindValue(':widget_id', $widgetId);
        $rulesQuery->execute();
        return $rulesQuery->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function getSingleItem($rule)
    {
        $getSingleItem = $this->dbh->prepare(
            'SELECT * FROM goods WHERE offer_id=:offer_id AND shop_id=:shop_id AND is_available=1'
        );
        $getSingleItem->bindValue(':offer_id', unserialize($rule['source']));
        $getSingleItem->bindValue(':shop_id', $rule['shop_id']);
        $getSingleItem->execute();
        $offerData = $getSingleItem->fetch(\PDO::FETCH_ASSOC);
        if (!$offerData){
            $offerData = $this->getRandomItem($rule['shop_id'], $rule['common_rule']);
        }
        return $offerData;
    }

    protected function getRandomItem($shopId, $rule = array())
    {
        $queryString = '';
        $queryValue = array();
        $rule = is_string($rule) ? unserialize($rule) : $rule;
        foreach ($rule as $filter => $value ){
            if (isset($this->convertFilter[$filter])){
                $queryString .= " AND {$this->convertFilter[$filter]} IN (" . Common::getInstance()->getQueryMark($value) .')';
                $queryValue = array_merge($queryValue, $value);
            }
        }
        $offerQuery = $this->dbh->prepare("SELECT * FROM goods WHERE shop_id=?" . $queryString . 'ORDER BY RAND() LIMIT 1');
        $offerQuery->execute(array_merge(array($shopId), $queryValue));
        return $offerQuery->fetch(\PDO::FETCH_ASSOC);
    }
}