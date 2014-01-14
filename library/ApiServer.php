<?php
namespace library;

use library\Config;
use library\DbLoadWidget;
use library\Common;
use model\Categories;
use model\Goods;
use model\Rules;
use model\Shops;
use model\Widgets;

class ApiServer
{
    /**
     * @var \PDO Config
     */
    protected $dbh;
    protected $config;

    public function __construct(array $config, \PDO $connection)
    {
        $this->config = $config;
        $this->dbh = $connection;
    }

    public function run($data)
    {
        if (Config::getInstance()->getBusyStatus() == true){
            $this->sendResponse(array('message' => $this->config['messages'][7]), true, 7);
        }
        $methodName = $this->getMethodName($data);
        $params = $this->getParams($data);
        if (method_exists($this, $methodName)) {
            $this->sendResponse(array('data' => $this->{$methodName}($params)));
        } else {
            $this->sendResponse(array('message' => $this->config['messages'][2]), true, 2);
        }
    }

    protected function getParams($data)
    {
        if (empty($data['params'])) {
            return array();
        } elseif (!empty($data['params']) && is_array($data['params'])) {
            array_walk($data['params'], array($this, 'stripTags'));
            return $data['params'];
        } else {
            $this->sendResponse(array('message' => $this->config['messages'][4]), true, 4);
        }
    }

    protected function stripTags(&$value, $key)
    {
        if (is_array($value)) {
            array_walk($value, array($this, 'stripTags'));
        } else {
            $value = htmlspecialchars(trim($value));
        }
    }

    protected function getMethodName($data)
    {
        if (!empty($data['methodName'])) {
            return strip_tags(trim((string)$data['methodName']));
        } else {
            $this->sendResponse(array('message' => $this->config['messages'][3]), true, 3);
        }
    }

    protected function sendResponse($data, $error = false, $code = 1)
    {
        echo json_encode(array('error' => $error, 'code' => $code, 'data' => $data));
        exit;
    }

    protected function checkNeededParam($data, $paramsName)
    {
        foreach ($paramsName as $name => $params) {
            switch ($params['required']) {
                case true:
                    if (empty($data[$name]) || !$this->correctType($data[$name], $params['type'])) {
                        $this->sendResponse(array('message' => $this->config['messages'][5]), true, 5);
                    }
                    break;
                case false:
                    if (!empty($data[$name]) && !$this->correctType($data[$name], $params['type'])) {
                        $this->sendResponse(array('message' => $this->config['messages'][5]), true, 5);
                    }
                    break;
            }
        }
    }

    protected function correctType($value, $type)
    {
        switch ($type) {
            case 'array':
                return is_array($value) ? : false;
            case 'string':
                return is_string($value) ? : false;
            default:
                return false;
        }
    }

    function __destruct()
    {
        $this->dbh = null;
    }

    protected function getShopList()
    {
        $shopsModel = new Shops($this->dbh);
        $shopsList = $shopsModel->getAll();
        return array('list' => $shopsList, 'count' => count($shopsList));
    }

    protected function getShop($data)
    {
        $this->checkNeededParam($data, array('shopId' => array('type' => 'array', 'required' => true)));
        $shopsModel = new Shops($this->dbh);
        $shopsList = $shopsModel->getShop(array('shopId' => $data['shopId']));
        return array('list' => $shopsList, 'count' => count($shopsList));
    }

    protected function getCategoryList($data)
    {
        $this->checkNeededParam(
            $data,
            array(
                'shopId' => array('type' => 'string', 'required' => true),
                'parentId' => array('type' => 'array', 'required' => false),
                'categoryId' => array('type' => 'array', 'required' => false)
            )
        );
        $categoriesModel = new Categories($this->dbh);
        $categoryList = $categoriesModel->getCategoriesList($data);
        return array('list' => $categoryList, 'count' => count($categoryList));
    }

    protected function getOffer($data)
    {
        $this->checkNeededParam(
            $data,
            array(
                'offerId' => array('type' => 'array', 'required' => true),
                'shopId' => array('type' => 'string', 'required' => true)
            )
        );
        $goodsModel = new Goods($this->dbh);
        $commonData = $goodsModel->getOffer(
            array(
                'shopId' => $data['shopId'],
                'offerId' => $data['offerId'],
                'allOffer' => empty($data['allOffer']) ? false : true
            )
        );
        return array('list' => $commonData, 'count' => count($commonData));
    }

    protected function getOfferList($data)
    {
        $this->checkNeededParam(
            $data,
            array(
                'categoryId' => array('type' => 'array', 'required' => true),
                'shopId' => array('type' => 'string', 'required' => true),
                'color' => array('type' => 'array', 'required' => false),
            )
        );
        $goodsModel = new Goods($this->dbh);
        $commonData = $goodsModel->getOffer(
            array(
                'shopId' => $data['shopId'],
                'categoryId' => $data['categoryId'],
                'color' => empty($data['color']) ? null : $data['color'],
                'allOffer' => true
            )
        );
        return array('list' => $commonData, 'count' => count($commonData));
    }

    protected function getWidgetInfo()
    {
        $widgetsModel = new Widgets($this->dbh);
        return $widgetsModel->getInfo();
    }

    protected function setWidget($data)
    {
        $this->checkNeededParam(
            $data,
            array(
                'shopId' => array('type' => 'string', 'required' => true),
                'title' => array('type' => 'string', 'required' => false),
                'skinId' => array('type' => 'string', 'required' => true),
                'typeId' => array('type' => 'string', 'required' => true),
                'commonRule' => array('type' => 'array', 'required' => false),
                'positions' => array('type' => 'array', 'required' => false),
                'widgetId' => array('type' => 'string', 'required' => false)
            )
        );
        if (empty($data['positions'])) {
            $data['positions'] = array();
        }
        foreach ($data['positions'] as $rule) {
            array_walk($rule, array($this, 'checkNeededParam'));
        }
        try {
            $this->dbh->beginTransaction();
            $data['positions'] = array_slice($data['positions'], 0, $this->getMaxPositions($data['typeId']));
            $widgetId = $this->widgetAdd($data);
            $this->rulesAdd($data['shopId'], $widgetId, $data['positions']);
            $this->dbh->commit();
            return array('widgetId' => $widgetId);
        } catch (\Exception $e) {
            $this->dbh->rollback();
            $this->sendResponse(array('message' => $this->config['messages'][6]), true, 6);
        }
    }

    protected function rulesAdd($shopId, $widgetId, $rules)
    {
        foreach ($rules as $key => $rule) {
            foreach ($rule as $position){
                switch ($position['type']) {
                    case Rules::RULE_TYPE_SINGLE:
                        $this->insertSingleItem($shopId, $widgetId, $position['params'], $key);
                        break;
                    case Rules::RULE_TYPE_RULE:
                        $this->insertRuleItem($shopId, $widgetId, $position['params'], $key);
                        break;
                    default:
                        throw new \Exception('Rule type undefined');
                }
            }
        }
    }

    protected function getMaxPositions($typeId)
    {
        switch ($typeId) {
            case Widgets::WIDGET_TYPE_BIG:
                return Widgets::WIDGET_TYPE_BIG_POSITIONS;
            case
            Widgets::WIDGET_TYPE_SMALL:
                return Widgets::WIDGET_TYPE_SMALL_POSITIONS;
            default:
                return Widgets::WIDGET_MAX_POSITIONS;
        }
    }

    protected function insertSingleItem($shopId, $widgetId, $offerId, $position)
    {
        $offerId = serialize(array_shift($offerId));
        $rulesModel = new Rules($this->dbh);
        $rulesModel->insertRule($shopId, $widgetId, $offerId, $position, $rulesModel::RULE_TYPE_SINGLE);
    }

    protected function insertRuleItem($shopId, $widgetId, $rule, $position)
    {
        $rule = $this->prepareRule($rule);
        $rulesModel = new Rules($this->dbh);
        $rulesModel->insertRule($shopId, $widgetId, $rule, $position, $rulesModel::RULE_TYPE_RULE);
    }

    protected function prepareRule($rule)
    {
        $preparedRule = array();
        foreach ($rule as $filterName => $filter) {
            if (!empty($filter) && is_array($filter)) {
                $preparedRule[$filterName] = array_diff($filter, array(''));
            }
        }
        return serialize($preparedRule);
    }

    protected function widgetAdd($data)
    {
        $data['commonRule'] = $this->prepareCommonRule($data, $data['typeId']);
        $widgetsModel = new Widgets($this->dbh);
        return $widgetsModel->widgetAdd(
            array(
                'typeId' => $data['typeId'],
                'shopId' => $data['shopId'],
                'skinId' => $data['skinId'],
                'positions' => count($data['positions']),
                'commonRule' => $data['commonRule'],
                'title' => empty($data['title']) ? null : $data['title'],
                'widgetId' => empty($data['widgetId']) ? null : $data['widgetId']
            )
        );
    }

    protected function prepareCommonRule($data, $typeId)
    {
        switch ($typeId) {
            case Widgets::WIDGET_TYPE_SMALL:
            case Widgets::WIDGET_TYPE_BIG:
                if (empty($data['commonRule'])) {
                    $this->sendResponse(array('message' => $this->config['messages'][5]), true, 5);
                }
                break;
            case Widgets::WIDGET_TYPE_FREE:
                if (empty($data['positions'])) {
                    $this->sendResponse(array('message' => $this->config['messages'][5]), true, 5);
                }
                return null;
            default:
                $this->sendResponse(array('message' => $this->config['messages'][5]), true, 5);
        }
        return serialize($data['commonRule']);
    }

    protected function checkCommonRule($rule)
    {
        foreach ($rule as $filter => $value) {
            $this->checkNeededParam(
                $rule,
                array(
                    $filter => array('type' => 'array', 'required' => true),
                )
            );
        }
    }

    protected function getRulesTypeList()
    {
        $rulesModel = new Rules($this->dbh);
        $rulesList = $rulesModel->getRulesList();
        return array('list' => $rulesList, 'count' => count($rulesList));
    }

    protected function getWidgetSkinList()
    {
        $widgetModel = new Widgets($this->dbh);
        $widgetSkinList = $widgetModel->getSkinList();
        return array('list' => $widgetSkinList, 'count' => count($widgetSkinList));
    }

    protected function getWidgetTypeList()
    {
        $widgetModel = new Widgets($this->dbh);
        $widgetTypeList = $widgetModel->getTypeList();
        return array('list' => $widgetTypeList, 'count' => count($widgetTypeList));
    }

    protected function getWidgetList($data)
    {
        $this->checkNeededParam(
            $data,
            array(
                'shopId' => array('type' => 'string', 'required' => true)
            )
        );
        $widgetModel = new Widgets($this->dbh);
        $responseList = $widgetModel->getWidgetList(array('shopId' => $data['shopId']));
        return array('list' => $responseList, 'count' => count($responseList));
    }

    protected function getColorList()
    {
        $goodsModel = new Goods($this->dbh);
        $colorList = $goodsModel->getColorList();
        return array('list' => $colorList, 'count' => count($colorList));
    }

    protected function referrerAdd($data)
    {
        $this->checkNeededParam(
            $data,
            array(
                'widgetId' => array('type' => 'string', 'required' => true)
            )
        );
        $widgetModel = new Widgets($this->dbh);
        $widgetModel->clickAdd($data['widgetId']);
        return true;
    }

    protected function deleteWidget($data){
        $this->checkNeededParam(
            $data,
            array(
                'widgetId' => array('type' => 'string', 'required' => true)
            )
        );
        $widgetModel = new Widgets($this->dbh);
        $widgetModel->deleteWidget($data['widgetId']);
        return true;
    }
}