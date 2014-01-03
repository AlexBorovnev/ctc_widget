<?php
namespace model;

class Widgets extends AbstractModel
{

    const WIDGET_SKIN_STS = 1;
    const WIDGET_SKIN_HOME = 2;
    const WIDGET_SKIN_VIDEOMORE = 3;

    const WIDGET_TYPE_SMALL = 1;
    const WIDGET_TYPE_BIG = 2;
    const WIDGET_TYPE_FREE = 3;

    const WIDGET_TYPE_SMALL_POSITIONS = 1;
    const WIDGET_TYPE_BIG_POSITIONS = 2;
    const WIDGET_MAX_POSITIONS = 7;

    public function widgetAdd($data)
    {

        $widgetAddQuery = "INSERT INTO widgets (type_id, shop_id, skin_id, position_count, common_rule) VALUES (:type_id, :shop_id, :skin_id, :pos_count, :common_rule)";
        $paramValue = array(
            ':type_id' => $data['typeId'],
            ':shop_id' => $data['shopId'],
            ':skin_id' => $data['skinId'],
            ':pos_count' => $data['positions'],
            ':common_rule' => $data['commonRule']
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

    public function getSkinList()
    {
        $widgetSkinList = $this->dbh->prepare('SELECT * FROM widget_skin');
        $widgetSkinList->execute();
        return $widgetSkinList->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTypeList()
    {
        $widgetTypeList = $this->dbh->prepare('SELECT * FROM widget_type');
        $widgetTypeList->execute();
        return $widgetTypeList->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getWidgetList($data)
    {
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
                array(
                    'skinId' => $row['skin_id'],
                    'typeId' => $row['type_id'],
                    'commonRule' => unserialize($row['common_rule'])
                )
            );
        }
        return $responseList;
    }

    public function getInfo()
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

    public function getCommonRule($widgetId)
    {
        $commonRuleQuery = $this->dbh->prepare("SELECT common_rule, type_id, shop_id FROM widgets WHERE id=?");
        $commonRuleQuery->execute(array($widgetId));
        return $commonRuleQuery->fetch(\PDO::FETCH_ASSOC);
    }

    public function getWidgetListTmp($data){
        $widgetsListQuery = $this->dbh->prepare("SELECT w.id, r.rules_type, r.source, r.position, w.common_rule, w.type_id, w.skin_id FROM widgets w LEFT JOIN rules r ON w.id=r.widget_id WHERE w.shop_id=?");
        $responseList = array();
        $widgetsListQuery->execute(array($data['shopId']));
        foreach ($widgetsListQuery->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $responseList[$row['widget_id']]['positions'][$row['position']] = array(
                'rule_type' => $row['rules_type'],
                'source' => unserialize($row['source'])
            );
            $responseList[$row['widget_id']] = array_merge(
                $responseList[$row['widget_id']],
                array(
                    'skinId' => $row['skin_id'],
                    'typeId' => $row['type_id'],
                    'commonRule' => unserialize($row['common_rule'])
                )
            );
        }
        return $responseList;
    }

}