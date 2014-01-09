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

    const WIDGET_PER_PAGE = 20;

    public function widgetAdd($data)
    {
        $widgetAddQuery = "INSERT INTO widgets (type_id, shop_id, skin_id, position_count, common_rule, title) VALUES (:type_id, :shop_id, :skin_id, :pos_count, :common_rule, :title)";
        $paramValue = array(
            ':type_id' => $data['typeId'],
            ':shop_id' => $data['shopId'],
            ':skin_id' => $data['skinId'],
            ':pos_count' => $data['positions'],
            ':common_rule' => $data['commonRule'],
            ':title' => $data['title']
        );
        if (!empty($data['widgetId'])) {
            $rulesModel = new Rules($this->dbh);
            $rulesModel->deleteRules($data['widgetId']);
            $widgetAddQuery = "UPDATE widgets SET type_id=:type_id, shop_id=:shop_id, skin_id=:skin_id, position_count=:pos_count, common_rule=:common_rule, title=:title WHERE id=:id";
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
        try {
            $widgetsListQuery = $this->dbh->prepare(
                "SELECT w.id, w.title, r.rules_type, r.source, r.position, w.common_rule, w.type_id, w.skin_id FROM widgets w LEFT JOIN rules r ON w.id=r.widget_id WHERE w.shop_id=:shop_id"
            );
            $widgetsListQuery->bindValue(':shop_id', $data['shopId']);
            $widgetsListQuery->execute();
            foreach ($widgetsListQuery->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $responseList[$row['id']]['positions'][$row['position']] = array(
                    'rule_type' => $row['rules_type'],
                    'source' => unserialize($row['source'])
                );
                $responseList[$row['id']] = array_merge(
                    $responseList[$row['id']],
                    array(
                        'skinId' => $row['skin_id'],
                        'typeId' => $row['type_id'],
                        'commonRule' => unserialize($row['common_rule']),
                        'title' => $row['title']
                    )
                );
            }
        } catch (\PDOException $e) {
            return array();
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

    public function clickAdd($widgetId)
    {
        $clickAddQuery = $this->dbh->prepare("UPDATE widgets SET click_cnt=click_cnt+1 WHERE id=?");
        $clickAddQuery->execute(array($widgetId));
        return true;
    }

    public function getWidgetsPage($shopId)
    {
        $countPageQuery = $this->dbh->prepare("SELECT id FROM widgets WHERE shop_id=?");
        $countPageQuery->execute(array($shopId));
        $rowCount = $countPageQuery->rowCount();
        return ((int)($rowCount / self::WIDGET_PER_PAGE)) + (($rowCount % self::WIDGET_PER_PAGE) ? 1 : 0);
    }

    public function deleteWidget($widgetId)
    {
        $deleteWidgetQuery = $this->dbh->prepare("DELETE FROM widgets WHERE id=?");
        $deleteWidgetQuery->execute(array($widgetId));
        return true;
    }

    public function getCommonWidgetInfo ($data, $pageNum)
    {
        $responseList = array();
        $offset = ($pageNum - 1) * self::WIDGET_PER_PAGE;
        try {
            $widgetsListQuery = $this->dbh->prepare(
                "SELECT w.id, w.type_id, w.skin_id, w.title FROM widgets w LEFT JOIN rules r ON w.id=r.widget_id WHERE w.shop_id=:shop_id GROUP BY w.id LIMIT :offset,:limit "
            );
            $widgetsListQuery->bindValue(':shop_id', $data['shopId']);
            $widgetsListQuery->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $widgetsListQuery->bindValue(':limit', self::WIDGET_PER_PAGE, \PDO::PARAM_INT);
            $widgetsListQuery->execute();
            foreach ($widgetsListQuery->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $responseList[$row['id']] =
                    array(
                        'skinId' => $row['skin_id'],
                        'typeId' => $row['type_id'],
                        'title' => $row['title']
                    );
            }
        } catch (\PDOException $e) {
            return array();
        }
        return $responseList;
    }
}