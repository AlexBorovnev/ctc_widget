<?php
namespace model;

class Rules extends AbstractModel
{

    const RULE_TYPE_SINGLE = 2;
    const RULE_TYPE_RULE = 1;

    public function insertRule($shopId, $widgetId, $rule, $position, $ruleType)
    {
        $singleRuleQuery = $this->dbh->prepare(
            "INSERT INTO rules (shop_id, widget_id, rules_type, source, position) VALUES (:shop_id, :widget_id, :rules_type, :source, :position) ON DUPLICATE KEY UPDATE rules_type = :rules_type, source = :source"
        );

        $singleRuleQuery->execute(
            array(
                ':shop_id' => $shopId,
                ':widget_id' => $widgetId,
                ':rules_type' => $ruleType,
                ':source' => $rule,
                ':position' => $position
            )
        );
    }

    public function getRulesList()
    {
        $rulesList = $this->dbh->prepare('SELECT * FROM rules_type');
        $rulesList->execute();
        return $rulesList->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getWidgetRules($widgetId)
    {
        $rulesQuery = $this->dbh->prepare(
            'SELECT * FROM rules r JOIN widgets w ON w.id=r.widget_id WHERE widget_id=:widget_id'
        );
        $rulesQuery->bindValue(':widget_id', $widgetId);
        $rulesQuery->execute();
        return $rulesQuery->fetchAll(\PDO::FETCH_ASSOC);
    }
}