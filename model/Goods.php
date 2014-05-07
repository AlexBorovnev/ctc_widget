<?php
namespace model;

class Goods extends AbstractModel
{
    const OFFER_IS_AVAILABLE = 1;
    const PARAM_LIST_NAME = 'param';
    protected $fields = array('categoryId' => 'category_id', 'param' => 'param', 'shopId' => 'shop_id');

    public function getOffer($data)
    {
        $qMarks = 'g.shop_id = ?';
        $qValue = array($data['shopId']);
        if (!empty($data['categoryId'])) {
            $qMarks .= ' AND g.category_id IN (' . $this->getQueryMark($data['categoryId']) . ')';
            $qValue = array_merge($qValue, $data['categoryId']);
        }
        if (!empty($data['offerId'])) {
            $qMarks .= ' AND g.offer_id IN (' . $this->getQueryMark($data['offerId']) . ')';
            $qValue = array_merge($qValue, $data['offerId']);
        }

        if (!empty($data['params'])) {
            $qMarks = ' AND gp.';
            $qValue = array_merge($qValue, $data['color']);
        }
        if (empty($data['allOffer'])) {
            $qMarks .= ' AND g.is_available=1';
        }
        $offerList = $this->dbh->prepare("SELECT g.offer_id, g.category_id, g.common_data, g.is_available, g.title as offer, c.title FROM goods g LEFT JOIN categories c ON c.category_id=g.category_id WHERE $qMarks");
        $offerList->execute($qValue);
        $offerList = $offerList->fetchAll(\PDO::FETCH_ASSOC);
        $paramQuery = $this->dbh->prepare("SELECT gp.value, p.title FROM goods_param gp JOIN params p ON p.id=gp.param_id WHERE gp.offer_id=? AND gp.category_id=? AND gp.shop_id=? ");
        $commonData = array();
        foreach ($offerList as $row) {
            $paramQuery->execute(array($row['offer_id'], $row['category_id'], $data['shopId']));
            $commonData[] = json_encode(
                array_merge(
                    unserialize($row['common_data']),
                    array(
                        'isAvailable' => $row['is_available'],
                        'categoryId' => $row['category_id'],
                        'title' => $row['title'],
                        'offer' => $row['offer'],
                        'param' => $paramQuery->fetchAll(\PDO::FETCH_ASSOC)
                    )
                )
            );
        }
        return $commonData;
    }

    public function getSingleOffer($data, $isAvailable=0)
    {
        $getSingleItemQuery = 'SELECT g.*, c.title as category_title FROM goods g LEFT JOIN categories c ON c.category_id=g.category_id AND c.shop_id=:shop_id WHERE g.offer_id=:offer_id AND g.shop_id=:shop_id';
        $offerInfo = unserialize($data['offerId']);
        if (is_string($offerInfo)){
            $offerInfo = array('offerId' => $offerInfo);
        }
        $paramValue = array(':offer_id' => $offerInfo['offerId'], ':shop_id' => $data['shopId']);
        if (!empty($offerInfo['categoryId'])){
            $paramValue = array_merge($paramValue, array(':category_id' => $offerInfo['categoryId']));
            $getSingleItemQuery .= ' AND g.category_id=:category_id';
        }
        if ($isAvailable){
            $getSingleItemQuery .= ' AND g.is_available=:is_available';
            $paramValue = array_merge($paramValue, array(':is_available' => $isAvailable));
        }
        $getSingleItem = $this->dbh->prepare($getSingleItemQuery);
        $getSingleItem->execute($paramValue);
        return $getSingleItem->fetch(\PDO::FETCH_ASSOC);
    }

    public function getRandomItem($shopId, $rule = array())
    {
        $queryString = '';
        $queryValue = array();
        foreach ($rule as $filter => $value) {
            if ($filter == self::PARAM_LIST_NAME){
                $paramNames = array_keys($value);
                $paramIdsQuery = $this->dbh->prepare("SELECT gp.param_id, p.title FROM goods_param gp JOIN params p ON p.id=gp.param_id WHERE p.title IN (".$this->getQueryMark($paramNames).") GROUP BY gp.param_id");
                $paramIdsQuery->execute($paramNames);
                $paramIds = $paramIdsQuery->fetchAll(\PDO::FETCH_ASSOC);
                $queryStringList = array();
                foreach ($paramIds as $row){
                    $queryStringList[] = " (gp.param_id=? AND gp.value IN (".$this->getQueryMark($value[$row['title']])."))";
                    $queryValue = array_merge($queryValue, array($row['param_id']), $value[$row['title']]);
                }
                if ($queryStringList){
                    $queryString .= ' AND (' . join(' OR', $queryStringList) . ') GROUP BY g.offer_id HAVING COUNT(g.offer_id)>=' . count($queryStringList);
                }
            } else {
                if (isset($this->fields[$filter]) && !empty($value) && is_array($value)) {
                    $queryString .= " AND g.{$this->fields[$filter]} IN (" . $this->getQueryMark(
                            $value
                        ) . ')';
                    $queryValue = array_merge($queryValue, $value);
                }
            }
        }
        $offerQuery = $this->dbh->prepare(
            'SELECT * FROM goods g JOIN goods_param gp ON gp.shop_id=g.shop_id AND gp.category_id=g.category_id AND gp.offer_id=g.offer_id WHERE g.shop_id=? ' . $queryString . ' ORDER BY RAND() LIMIT 1'
        );
        $offerQuery->execute(array_merge(array($shopId), $queryValue));
        return $offerQuery->fetch(\PDO::FETCH_ASSOC);
    }

    public function getColorList()
    {
        return array(
            'Бежевый',
            'Белый',
            'Голубой',
            'Желтый',
            'Зеленый',
            'Золотой',
            'Коричневый',
            'Красный',
            'Мультицвет',
            'Не указан',
            'Оранжевый',
            'Розовый',
            'Серебряный',
            'Серый',
            'Синий',
            'Фиолетовый',
            'Черный'
        );
    }
}