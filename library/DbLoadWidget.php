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
        if (isset($rule) && isset($rule['common_rule'])){
            $offers = $this->getAdditionalOfferIfNeeded($offers, $rule['common_rule'], $rule['type_id'], count($offers), $rule['shop_id']);
        }
        return $offers;
    }

    protected function getAdditionalOfferIfNeeded($offers, $rule, $typeId, $countOffers, $shopId)
    {
        $delta = 0;
        switch ($typeId) {
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
                $offers[] = $this->getRandomItem($shopId, $rule);
            }
        }
        return $offers;
    }

    protected function getOfferByRule($rule)
    {
        switch ($rule['rules_type']) {
            case Rules::RULE_TYPE_SINGLE:
                return $this->getSingleItem($rule['shop_id'], $rule);
            case Rules::RULE_TYPE_RULE:
                return $this->getRandomItem($rule['shop_id'], $rule['source']);
            default:
                return array();
        }
    }

    protected function getRules($widgetId)
    {
        $rulesModel = new Rules($this->dbh);
        return $rulesModel->getWidgetRules($widgetId);
    }

    protected function getSingleItem($shopId, $rule)
    {
        $goodsModel = new Goods($this->dbh);
        $offerData = $goodsModel->getSingleOffer(array('offerId' => $rule['source'], 'shopId' => $shopId));
        if (!$offerData) {
            $offerData = $this->getRandomItem($shopId, $rule['common_rule']);
        }
        return $offerData;
    }

    protected function getRandomItem($shopId, $rule = array())
    {
        $rule = is_string($rule) ? unserialize($rule) : $rule;
        $goodsModel = new Goods($this->dbh);
        $offer = $goodsModel->getRandomItem($shopId, $rule);
        if (!$offer) {
            $offer = $this->getRandomItem($shopId, $this->repeatAttemptWithParentCategory($shopId, $rule));
        }
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