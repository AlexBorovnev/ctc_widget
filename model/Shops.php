<?php
namespace model;

class Shops extends AbstractModel
{

    public function getAll()
    {
        $shopsList = $this->dbh->prepare('SELECT * FROM shops');
        $shopsList->execute();
        return $shopsList->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getShop($data)
    {
        $qMarks = $this->getQueryMark($data['shopId']);
        $shopsList = $this->dbh->prepare("SELECT * FROM shops WHERE id IN ($qMarks)");
        $shopsList->execute($data['shopId']);
        return $shopsList->fetchAll(\PDO::FETCH_ASSOC);
    }
}