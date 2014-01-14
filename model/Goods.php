<?php
namespace model;

class Goods extends AbstractModel
{
    const OFFER_IS_AVAILABLE = 1;
    protected $fields = array('categoryId' => 'category_id', 'color' => 'color', 'shopId' => 'shop_id');

    public function getOffer($data)
    {
        $qMarks = 'shop_id = ?';
        $qValue = array($data['shopId']);
        if (!empty($data['categoryId'])) {
            $qMarks .= ' AND category_id IN (' . $this->getQueryMark($data['categoryId']) . ')';
            $qValue = array_merge($qValue, $data['categoryId']);
        }
        if (!empty($data['offerId'])) {
            $qMarks .= ' AND offer_id IN (' . $this->getQueryMark($data['offerId']) . ')';
            $qValue = array_merge($qValue, $data['offerId']);
        }

        if (!empty($data['color'])) {
            $qMarks .= ' AND color IN (' . $this->getQueryMark($data['color']) . ')';
            $qValue = array_merge($qValue, $data['color']);
        }
        if (empty($data['allOffer'])) {
            $qMarks .= ' AND is_available=1';
        }
        $offerList = $this->dbh->prepare("SELECT common_data, is_available FROM goods WHERE $qMarks LIMIT 1000");
        $offerList->execute($qValue);
        $offerList = $offerList->fetchAll(\PDO::FETCH_ASSOC);
        $commonData = array();
        foreach ($offerList as $row) {
            $commonData[] = json_encode(array_merge(unserialize($row['common_data']), array('isAvailable' => $row['is_available'])));
        }
        return $commonData;
    }

    public function getSingleOffer($data, $isAvailable=1)
    {
        $getSingleItemQuery = 'SELECT * FROM goods WHERE offer_id=:offer_id AND shop_id=:shop_id';
        $paramValue = array(':offer_id' => unserialize($data['offerId']), ':shop_id' => $data['shopId']);
        if ($isAvailable){
            $getSingleItemQuery .= ' AND is_available=:is_available';
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
            if (isset($this->fields[$filter]) && !empty($value) && is_array($value)) {
                $queryString .= " AND {$this->fields[$filter]} IN (" . $this->getQueryMark(
                        $value
                    ) . ')';
                $queryValue = array_merge($queryValue, $value);
            }
        }
        $offerQuery = $this->dbh->prepare(
            'SELECT * FROM goods WHERE shop_id=? AND is_available=1' . $queryString . ' ORDER BY RAND() LIMIT 1'
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