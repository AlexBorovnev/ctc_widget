<?php
namespace library;

use library\Config;
use library\DbLoadWidget;
use library\Common;

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
            $value = strip_tags(trim($value));
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
        $shopsList = $this->dbh->prepare('SELECT * FROM shops');
        $shopsList->execute();
        $shopsList = $shopsList->fetchAll(\PDO::FETCH_ASSOC);
        return array('list' => $shopsList, 'count' => count($shopsList));
    }

    protected function getShop($data)
    {
        $this->checkNeededParam($data, array('shopId' => array('type' => 'array', 'required' => true)));
        $qMarks = Common::getInstance()->getQueryMark($data['shopId']);
        $shopsList = $this->dbh->prepare("SELECT * FROM shops WHERE id IN ($qMarks)");
        $shopsList->execute($data['shopId']);
        $shopsList = $shopsList->fetchAll(\PDO::FETCH_ASSOC);
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
        $qMarks = 'shop_id = ?';
        $qValue = array($data['shopId']);
        if (!empty($data['parentId'])) {
            $qMarks .= ' AND parent_id IN (' . Common::getInstance()->getQueryMark($data['parentId']) . ')';
            $qValue = array_merge($qValue, $data['parentId']);
        }
        if (!empty($data['categoryId'])) {
            $qMarks .= ' AND category_id IN (' . Common::getInstance()->getQueryMark($data['categoryId']) . ')';
            $qValue = array_merge($qValue, $data['categoryId']);
        }
        $categoryList = $this->dbh->prepare("SELECT * FROM categories WHERE $qMarks");
        $categoryList->execute($qValue);
        $categoryList = $categoryList->fetchAll(\PDO::FETCH_ASSOC);
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
        $qOfferMark = Common::getInstance()->getQueryMark($data['offerId']);
        $offerList = $this->dbh->prepare(
            "SELECT common_data FROM goods WHERE shop_id = ? AND offer_id IN ($qOfferMark)"
        );
        $offerList->execute(array_merge(array($data['shopId']), $data['offerId']));
        $offerList = $offerList->fetchAll(\PDO::FETCH_ASSOC);
        $commonData = array();
        foreach ($offerList as $row) {
            $commonData[] = json_encode(unserialize($row['common_data']));
        }
        return array('list' => $commonData, 'count' => count($offerList));
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
        $qMarks = 'shop_id = ?';
        $qValue = array($data['shopId']);
        $qMarks .= ' AND category_id IN (' . Common::getInstance()->getQueryMark($data['categoryId']) . ')';
        $qValue = array_merge($qValue, $data['categoryId']);
        if (!empty($data['color'])) {
            $qMarks .= ' AND color IN (' . Common::getInstance()->getQueryMark($data['color']) . ')';
            $qValue = array_merge($qValue, $data['color']);
        }
        $offerList = $this->dbh->prepare("SELECT common_data FROM goods WHERE $qMarks ORDER BY RAND() LIMIT 1000");
        $offerList->execute($qValue);
        $offerList = $offerList->fetchAll(\PDO::FETCH_ASSOC);
        $commonData = array();
        foreach ($offerList as $row) {
            $commonData[] = json_encode(unserialize($row['common_data']));
        }
        return array('list' => $commonData, 'count' => count($offerList));
    }

    protected function getWidgetInfo()
    {
        $tableWithWidgetInfo = array('widget_type', 'widget_skin');
        $infoList = array();
        foreach ($tableWithWidgetInfo as $tableName) {
            $query = $this->dbh->prepare("SELECT * FROM {$tableName}");
            $query->execute();
            $infoList[$tableName] = $query->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $infoList;
    }

    protected function setWidget($data)
    {
        $this->checkNeededParam(
            $data,
            array(
                'shopId' => array('type' => 'string', 'required' => true),
                'skinId' => array('type' => 'string', 'required' => true),
                'typeId' => array('type' => 'string', 'required' => true),
                'commonRule' => array('type' => 'array', 'required' => false),
                'positions' => array('type' => 'array', 'required' => true),
                'widgetId' => array('type' => 'string', 'required' => false)
            )
        );
        foreach ($data['positions'] as $rule) {
            $this->checkNeededParam(
                $rule,
                array(
                    'type' => array('type' => 'string', 'required' => true),
                    'params' => array('type' => 'array', 'required' => true)
                )
            );
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
            switch ($rule['type']) {
                case DbLoadWidget::RULE_TYPE_SINGLE:
                    $this->insertSingleItem($shopId, $widgetId, $rule['params'], $key);
                    break;
                case DbLoadWidget::RULE_TYPE_RULE:
                    $this->insertRuleItem($shopId, $widgetId, $rule['params'], $key);
                    break;
                default:
                    throw new \Exception('Rule type undefined');
            }
        }
    }

    protected function getMaxPositions($typeId)
    {
        switch ($typeId) {
            case DbLoadWidget::WIDGET_TYPE_BIG:
                return DbLoadWidget::WIDGET_TYPE_BIG_POSITIONS;
            case
            DbLoadWidget::WIDGET_TYPE_SMALL:
                return DbLoadWidget::WIDGET_TYPE_SMALL_POSITIONS;
            default:
                return DbLoadWidget::WIDGET_MAX_POSITIONS;
        }
    }

    protected function insertSingleItem($shopId, $widgetId, $offerId, $position)
    {
        $singleRuleQuery = $this->dbh->prepare(
            "INSERT INTO rules (shop_id, widget_id, rules_type, source, position) VALUES (:shop_id, :widget_id, :rules_type, :source, :position) ON DUPLICATE KEY UPDATE rules_type = :rules_type, source = :source"
        );
        $offerId = array_shift($offerId);
        $singleRuleQuery->execute(
            array(
                ':shop_id' => $shopId,
                ':widget_id' => $widgetId,
                ':rules_type' => DbLoadWidget::RULE_TYPE_SINGLE,
                ':source' => serialize($offerId),
                ':position' => $position
            )
        );
    }

    protected function insertRuleItem($shopId, $widgetId, $rule, $position)
    {
        $ruleQuery = $this->dbh->prepare(
            "INSERT INTO rules (shop_id, widget_id, rules_type, source, position) VALUES (:shop_id, :widget_id, :rules_type, :source, :position) ON DUPLICATE KEY UPDATE rules_type = :rules_type, source = :source"
        );
        $ruleQuery->execute(
            array(
                ':shop_id' => $shopId,
                ':widget_id' => $widgetId,
                ':rules_type' => DbLoadWidget::RULE_TYPE_RULE,
                ':source' => $this->prepareRule($rule),
                ':position' => $position
            )
        );
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
        $widgetAddQuery = "INSERT INTO widgets (type_id, shop_id, skin_id, position_count, common_rule) VALUES (:type_id, :shop_id, :skin_id, :pos_count, :common_rule)";
        $paramValue = array(
            ':type_id' => $data['typeId'],
            ':shop_id' => $data['shopId'],
            ':skin_id' => $data['skinId'],
            ':pos_count' => count($data['positions']),
            ':common_rule' => $this->prepareCommonRule($data, $data['typeId'])
        );
        if (!empty($data['widgetId'])) {
            $widgetAddQuery = "UPDATE widgets SET type_id=:type_id, shop_id=:shop_id, skin_id=:skin_id, position_count=:pos_count, common_rule=:common_rule WHERE id=:id";
            $paramValue = array_merge($paramValue, array(':id' => $data['widgetId']));
        }
        $widgetAddQuery = $this->dbh->prepare($widgetAddQuery);
        $widgetAddQuery->execute(
            $paramValue
        );
        return $this->dbh->lastInsertId() ? : $data['widgetId'];
    }

    protected function prepareCommonRule($data, $typeId)
    {
        switch ($typeId) {
            case DbLoadWidget::WIDGET_TYPE_SMALL:
            case DbLoadWidget::WIDGET_TYPE_BIG:
                if (empty($data['commonRule'])) {
                    $this->sendResponse(array('message' => $this->config['messages'][5]), true, 5);
                }
                break;
            case DbLoadWidget::WIDGET_TYPE_FREE:
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
        $rulesList = $this->dbh->prepare('SELECT * FROM rules_type');
        $rulesList->execute();
        $rulesList = $rulesList->fetchAll(\PDO::FETCH_ASSOC);
        return array('list' => $rulesList, 'count' => count($rulesList));
    }

    protected function getWidgetSkinList()
    {
        $widgetSkinList = $this->dbh->prepare('SELECT * FROM widget_skin');
        $widgetSkinList->execute();
        $widgetSkinList = $widgetSkinList->fetchAll(\PDO::FETCH_ASSOC);
        return array('list' => $widgetSkinList, 'count' => count($widgetSkinList));
    }

    protected function getWidgetTypeList()
    {
        $widgetTypeList = $this->dbh->prepare('SELECT * FROM widget_type');
        $widgetTypeList->execute();
        $widgetTypeList = $widgetTypeList->fetchAll(\PDO::FETCH_ASSOC);
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
        $responseList = array();
        $widgetsListQuery = $this->dbh->prepare(
            "SELECT r.widget_id, r.rules_type, r.source, r.position, w.common_rule, w.type_id, w.skin_id FROM rules r JOIN widgets w ON w.id=r.widget_id WHERE r.shop_id=:shop_id"
        );
        $widgetsListQuery->execute(array(':shop_id' => $data['shopId']));
        foreach ($widgetsListQuery->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $responseList[$row['widget_id']]['positions'][$row['position']] = array(
                'rule_type' => $row['rules_type'],
                'source' => unserialize($row['source'])
            );
            $responseList[$row['widget_id']] = array_merge(
                $responseList[$row['widget_id']],
                array('skinId' => $row['skin_id'], 'typeId' => $row['type_id'], 'commonRule' => unserialize($row['common_rule']))
            );
        }
        return array('list' => $responseList, 'count' => count($responseList));
    }
}