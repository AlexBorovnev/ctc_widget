<?php
namespace library;

use library\Config;

class ApiServer
{
    protected static $dbh;
    protected static $config;

    public function __construct()
    {
        self::$config = Config::getInstance()->getConfig();
        self::$dbh = new \PDO(sprintf(
            "mysql:host=%s;dbname=%s;charset=UTF8",
            self::$config['db']['db_host'],
            self::$config['db']['db_name']
        ), self::$config['db']['login'], self::$config['db']['password']);
        self::$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function run($data)
    {
        $methodName = $this->getMethodName($data);
        $params = $this->getParams($data);
        if (method_exists($this, $methodName)) {
            $this->sendResponse(array('data' => $this->{$methodName}($params)));
        } else {
            $this->sendResponse(array('message' => 'Method doesn’t exists'), true, 2);
        }
    }

    protected function getParams($data)
    {
        if (!empty($data['params']) && is_array($data['params'])) {
            array_walk($data['params'], array($this, 'stripTags'));
            return $data['params'];
        } else {
            $this->sendResponse(array('message' => 'Params is not array'), true, 4);
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
            $this->sendResponse(array('message' => 'Method name is empty'), true, 3);
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
                        $this->sendResponse(array('message' => 'Needed param is out'), true, 5);
                    }
                case false:
                    if (!empty($data[$name]) && !$this->correctType($data[$name], $params['type'])) {
                        $this->sendResponse(array('message' => 'Needed param is out'), true, 5);
                    }
            }
        }
    }
    protected function correctType($value, $type){
        switch ($type){
            case 'array':
                return is_array($value)?:false;
            case 'string':
                return is_string($value)?:false;
            default:
                return false;
        }
    }

    function __destruct() {
        self::$dbh = null;
    }

    protected function getShopList()
    {
        $shopsList = self::$dbh->prepare('SELECT * FROM shops');
        $shopsList->execute();
        $shopsList = $shopsList->fetchAll(\PDO::FETCH_ASSOC);
        return array('list' => $shopsList, 'count' => count($shopsList));
    }

    protected function getShop($data)
    {
        $this->checkNeededParam($data, array('shopId' => array('type' => 'array', 'required' => true)));
        $qMarks = $this->getQueryMark($data['shopId']);
        $shopsList = self::$dbh->prepare("SELECT * FROM shops WHERE id IN ($qMarks)");
        $shopsList->execute($data['shopId']);
        $shopsList = $shopsList->fetchAll(\PDO::FETCH_ASSOC);
        return array('list' => $shopsList, 'count' => count($shopsList));
    }

    protected function getCategoryList($data){
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
        if (!empty($data['parentId'])){
            $qMarks .= ' AND parent_id IN (' . $this->getQueryMark($data['parentId']). ')';
            $qValue = array_merge($qValue, $data['parentId']);
        }
        if (!empty($data['categoryId'])){
            $qMarks .= ' AND category_id IN (' .$this->getQueryMark($data['categoryId'])  .')';
            $qValue = array_merge($qValue, $data['categoryId']);
        }
        $categoryList = self::$dbh->prepare("SELECT * FROM categories WHERE $qMarks");
        $categoryList->execute($qValue);
        $categoryList = $categoryList->fetchAll(\PDO::FETCH_ASSOC);
        return array('list' => $categoryList, 'count' => count($categoryList));
    }

    protected function getOffer($data){
        $this->checkNeededParam(
            $data,
            array(
                'offerId' => array('type' => 'array', 'required' => true),
                'shopId' => array('type' => 'string', 'required' => true)
            )
        );
        $qOfferMark = $this->getQueryMark($data['offerId']);
        $offerList = self::$dbh->prepare("SELECT * FROM goods WHERE shop_id = ? AND offer_id IN ($qOfferMark)");
        $offerList->execute(array_merge(array($data['shopId']), $data['offerId']));
        $offerList =  $offerList->fetchAll(\PDO::FETCH_ASSOC);;
        return array('list' => $offerList, 'count' => count($offerList));
    }

    protected function getOfferList($data){
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
        $qMarks .= ' AND category_id IN (' . $this->getQueryMark($data['categoryId']). ')';
        $qValue = array_merge($qValue, $data['parentId']);
        if (!empty($data['color'])){
            $qMarks .= ' AND color IN (' .$this->getQueryMark($data['color'])  .')';
            $qValue = array_merge($qValue, $data['color']);
        }
        $offerList = self::$dbh->prepare("SELECT * FROM goods WHERE $qMarks ORDER BY RAND() LIMIT 1000");
        $offerList->execute($qValue);
        $offerList = $offerList->fetchAll(\PDO::FETCH_ASSOC);
        return array('list' => $offerList, 'count' => count($offerList));
    }

    protected function getQueryMark($data){
        return str_repeat('?,', count($data) - 1) . '?';
    }

}