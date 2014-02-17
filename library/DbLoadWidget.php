<?php
namespace library;

use library\WidgetAbstract;
use library\Config;
use library\Common;
use model\Categories;
use model\Goods;
use model\Rules;
use model\Widgets;

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
        $widgetModel = new Widgets($this->dbh);
        $offers = $this->getAdditionalOfferIfNeeded($offers, $widgetModel->getCommonRule($widgetId), count($offers));
        return $offers;
    }

    protected function getAdditionalOfferIfNeeded($offers, $rule, $countOffers)
    {
        $delta = 0;
        switch ($rule['type_id']) {
            case Widgets::WIDGET_TYPE_SMALL:
                $delta = Widgets::WIDGET_TYPE_SMALL_POSITIONS - $countOffers;
                break;
            case Widgets::WIDGET_TYPE_BIG:
                $delta = Widgets::WIDGET_TYPE_BIG_POSITIONS - $countOffers;
                break;
        }
        if ($delta < 0) {
            array_splice($offers, $delta);
        } elseif ($delta > 0) {
            for ($i = 0; $i < $delta; $i++) {
                if ($offer = $this->getRandomItem($rule['shop_id'], $rule['common_rule'])){
                    $offers[] = $this->getRandomItem($rule['shop_id'], $rule['common_rule']);
                }
            }
        }
        return $offers;
    }

    protected function getOfferByRule($rule)
    {
        switch ($rule['rules_type']) {
            case Rules::RULE_TYPE_SINGLE:
                if ($offer = $this->getSingleItem($rule['shop_id'], $rule)){
                    return $offer;
                } elseif (!empty($rule['freeWidgetRule'])) {
                    return $this->getRandomItem($rule['shop_id'], $rule['freeWidgetRule']);
                }
                return array();
            case Rules::RULE_TYPE_RULE:
                return $this->getRandomItem($rule['shop_id'], $rule['source']);
            default:
                return array();
        }
    }

    protected function getRules($widgetId)
    {
        $preparedRule = array();
        $rulesModel = new Rules($this->dbh);
        $rules = $rulesModel->getWidgetRules($widgetId);
        //prepare rule for free widget, if position has rule, we use it after search offer by id, id it exists
        foreach ($rules as $rule){
            if ($rule['rules_type'] == Rules::RULE_TYPE_RULE){
                $preparedRule[$rule['position']]['freeWidgetRule'] = $rule['source'];
                unset($rule['source']);
                $rule['rules_type'] = Rules::RULE_TYPE_SINGLE;
            }
            if (empty($preparedRule[$rule['position']])){
                $preparedRule[$rule['position']] = array();
            }
            $preparedRule[$rule['position']] = array_merge($preparedRule[$rule['position']], $rule);
        }
        return $preparedRule;
    }

    protected function getSingleItem($shopId, $rule)
    {
        $goodsModel = new Goods($this->dbh);
        if (!isset($rule['source'])) {
            return array();
        }
        $offerData = $goodsModel->getSingleOffer(array('offerId' => $rule['source'], 'shopId' => $shopId));
        //if offer not found in db, than implement common rule for position in widget
        if (!$offerData && !empty($rule['common_rule'])) {
            $offerData = $this->getRandomItem($shopId, $rule['common_rule']);
        }
        return $offerData;
    }

    protected function getRandomItem($shopId, $rule = array())
    {
        $rule = is_string($rule) ? unserialize($rule) : $rule;
        $offer = array();
        $goodsModel = new Goods($this->dbh);
        if ($rule){
            $offer = $goodsModel->getRandomItem($shopId, $rule);
        }
        //uncoment this lines if needed search in parent category
//        if (!$offer) {
//            $offer = $this->getRandomItem($shopId, $this->repeatAttemptWithParentCategory($shopId, $rule));
//        }
        return $offer;
    }

    /*TODO implement this method for use other filter and remove const name
    */
    protected function repeatAttemptWithParentCategory($shopId, $rule)
    {
        if (!empty($rule['categoryId'])) {
            $categoryModel = new Categories($this->dbh);
            $categoryList = $categoryModel->getCategoriesWithParentDependency($shopId, $rule);
            if (array_diff($categoryList, $rule['categoryId'])) {
                $rule['categoryId'] = $categoryList;
                return $rule;
            }
        }
        return $rule;
    }
}