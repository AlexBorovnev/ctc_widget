<?php
namespace model;

class Goods extends AbstractModel
{
    protected $fields = array('categoryId' => 'category_id', 'color' => 'color', 'shopId' => 'shop_id');

    public function getOffer($data)
    {
        $qMarks = 'shop_id = ? AND is_available=1';
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
        $offerList = $this->dbh->prepare("SELECT common_data FROM goods WHERE $qMarks LIMIT 1000");
        $offerList->execute($qValue);
        $offerList = $offerList->fetchAll(\PDO::FETCH_ASSOC);
        $commonData = array();
        foreach ($offerList as $row) {
            $commonData[] = json_encode(unserialize($row['common_data']));
        }
        return $commonData;
    }

    public function getSingleOffer($data)
    {
        $getSingleItem = $this->dbh->prepare(
            'SELECT * FROM goods WHERE offer_id=:offer_id AND shop_id=:shop_id AND is_available=1'
        );
        $getSingleItem->bindValue(':offer_id', unserialize($data['offerId']));
        $getSingleItem->bindValue(':shop_id', $data['shopId']);
        $getSingleItem->execute();
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
            'SELECT * FROM goods WHERE shop_id=? AND is_available=1' . $queryString . 'ORDER BY RAND() LIMIT 1'
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