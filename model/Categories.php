<?php
namespace model;

use library\Common;

class Categories extends AbstractModel
{
    const GET_TREE_RESULT = true;
    const NAME_TITLE = 1;
    const MODEL_VENDOR_TITLE = 2;
    const AUTHOR_TITLE = 3;
    const MAX_COUNT_PARAMS_VALUE = 50;

    protected $disabledParam = array(
        'Размеры',
        'Материал',
        'Оригинальное название',
        'Дополнительно',
        'Мощность',
        'Размер упаковки (ДхШхВ), см');
    protected $fields = array(
        'categoryId' => 'category_id',
        'shopId' => 'shop_id',
        'parentId' => 'parent_id',
        'title' => 'title',
        'updatedAt' => 'updated_at'
    );

    private $categoryParamList = array();
    private $categoryList = array();
    /**
     * @var $preparedQuery \PDOStatement
     */
    private $preparedQuery;

    public function setCategoryList($categoryList)
    {
        $this->categoryList = $categoryList;
    }

    public function getCategoriesList($data, $getTree = false)
    {
        $qMarks = '';
        $qValue = array($data['shopId'], $data['shopId']);
        if (isset($data['parentId'])) {
            $qMarks .= ' AND c.parent_id IN (' . $this->getQueryMark($data['parentId']) . ')';
            $qValue = array_merge($qValue, $data['parentId']);
        }
        if (isset($data['categoryId'])) {
            $qMarks .= ' AND c.category_id IN (' . $this->getQueryMark($data['categoryId']) . ')';
            $qValue = array_merge($qValue, $data['categoryId']);
        }
        $categoryList = $this->dbh->prepare("SELECT c.category_id, c.shop_id, c.title, c.parent_id, (SELECT COUNT(*) FROM categories WHERE shop_id=? AND parent_id=c.category_id) AS child_cat FROM categories c WHERE c.shop_id=? $qMarks");
        $categoryList->execute($qValue);
        if ($getTree){
            return $this->getCategoryTree($categoryList->fetchAll(\PDO::FETCH_ASSOC));
        }
        return $categoryList->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getCategoriesWithParentDependency($shopId, $rule)
    {
        $parentListForCategoriesQuery = $this->dbh->prepare(
            'SELECT parent_id FROM categories WHERE shop_id=? AND category_id IN (' . $this->getQueryMark(
                $rule['categoryId']
            ) . ') GROUP BY parent_id'
        );
        $parentListForCategoriesQuery->execute(array_merge(array($shopId), $rule['categoryId']));
        $parentList = array();
        while (false !== $parentId = $parentListForCategoriesQuery->fetchColumn()) {
            $parentList[] = $parentId;
        }
        return $this->getParentCategory($shopId, $parentList);
    }

    protected function getParentCategory($shopId, $parentList)
    {
        $categoryList = $parentList;
        if (!empty($parentList)) {
            $categoryFromParentListQuery = $this->dbh->prepare(
                'SELECT category_id FROM categories WHERE shop_id=? AND parent_id IN (' . $this->getQueryMark(
                    $parentList
                ) . ') GROUP BY category_id'
            );
            $categoryFromParentListQuery->execute(array_merge(array($shopId), $parentList));
            while ($categoryId = $categoryFromParentListQuery->fetchColumn()) {
                $categoryList = array_merge($categoryList, $this->getParentCategory($shopId, array($categoryId)));
            }
        }
        return $categoryList;
    }

    public function getChildCategories($categoryId, $shopId, $fromArray = false)
    {
        $childList = array();
        $categoryIdMark = $this->getQueryMark($categoryId);
        $childCategoryQuery = $this->dbh->prepare(
            "SELECT category_id FROM categories WHERE parent_id IN (" . $categoryIdMark . ") AND shop_id=?"
        );
        $childCategoryQuery->execute(array_merge($categoryId, array($shopId)));
        $childListTmp = $childCategoryQuery->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($childListTmp as $row) {
            $childList[] = $row['category_id'];
        }

        if ($childList) {
            $childList = array_merge($childList, $this->getChildCategories($childList, $shopId));
        }
        return array_unique(array_merge($categoryId, $childList));
    }

    public function getCategoryByOffer($shopId, $offerId){
        $categoryQuery = $this->dbh->prepare("SELECT category_id FROM widgets WHERE offer_id=? AND shop_id=?");
        $categoryQuery->execute(array($offerId, $shopId));
        return $categoryQuery->fetch(\PDO::FETCH_ASSOC);
    }

    protected function getCategoryTree($categoryList, $currentParentId = 0)
    {
        $categoryTree = array();
        foreach ($categoryList as $leaf) {
            if ($leaf['parent_id'] == $currentParentId) {
                $categoryTree[$leaf['category_id']] = array(
                    'cid' => $leaf['category_id'],
                    'pid' => $leaf['parent_id'],
                    'title' => $leaf['title'],
                    'child_count' => $leaf['child_cat'],
                );
                if ($categoryTree[$leaf['category_id']]['child_count']) {
                    $categoryTree[$leaf['category_id']]['childs'] = $this->getCategoryTree(
                        $categoryList,
                        $leaf['category_id']
                    );
                }
            }
        }
        return $categoryTree;
    }

    public function getParamNameByCategoriesId($shopId, $catId, $format = 'string')
    {

        if (isset($this->categoryParamList[$catId])){
            $param = $this->categoryParamList[$catId];
        } else {
            if (isset($this->preparedQuery)){
                $query = $this->preparedQuery;
            } else {
                $query = $this->dbh->prepare("SELECT p.title FROM goods_param gp JOIN params p ON p.id=gp.param_id WHERE gp.category_id=:cat_id AND gp.shop_id=:shop_id GROUP BY p.title");
            }
            $query->execute(array(':cat_id' => $catId, ':shop_id' => $shopId));
            $param = array();
            $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row){
                $param[] = $row['title'];
            }
            $this->categoryParamList[$catId] = $param;
        }
        switch ($format){
            case 'string':
                return join(', ', $param);
            case 'json':
                return json_encode($param);
            case 'array':
                return $param;
        }
    }

    public function addCategoriesParam($shopId, $catId, $childs)
    {
        $params = array();
        foreach ($childs as $childCategory){
            $params = array_merge($params, $this->getParamNameByCategoriesId($shopId, $childCategory, 'array'));
        }
        $params = array_unique($params);
        sort($params);
        $paramsValue = $this->getParamsValue($params, $shopId, $childs);
        $query = $this->dbh->prepare("INSERT INTO categories_param (shop_id, categories_id, param_list, params_value) VALUES(:shop_id, :cat_id, :params, :values) ON DUPLICATE KEY UPDATE param_list=:params, params_value=:values");
        $query->execute(array(':shop_id' => $shopId, ':cat_id' => $catId, ':params' => serialize($params), ':values' => serialize($paramsValue)));
    }

    public function makeCategoryListWithChild($shopId)
    {
        $this->categoryList = $this->getCategoriesList(array('shopId' => $shopId));
    }

    public function getParamsForCategory($shopId, $catIds, $withValue = false)
    {
        $categoryIdsMark = $this->getQueryMark($catIds);
        $getCategoryParamQuery = $this->dbh->prepare("SELECT * FROM categories_param cp WHERE categories_id IN ({$categoryIdsMark}) AND shop_id=?");
        $getCategoryParamQuery->execute(array_merge($catIds, array($shopId)));
        $categoryParams = $getCategoryParamQuery->fetchAll(\PDO::FETCH_ASSOC);
        $returnedList = array();
        foreach($categoryParams as $row){
            $returnedList[$row['categories_id']] = unserialize($row['param_list']);
            if ($paramForDel = array_intersect($returnedList[$row['categories_id']], $this->disabledParam) ){
                foreach ($paramForDel as $key=>$param){
                    unset($returnedList[$row['categories_id']][$key]);
                }
            }
        }
        if ($withValue){
            $paramList = array();
            foreach ($returnedList as $row){
                $paramList = array_unique(array_merge($paramList, $row));
            }
            $paramValue = $this->getParamsValue($paramList, $shopId, $catIds);
            return array('categoryParam' => $returnedList, 'paramValue' => $paramValue);
        }else {
            return array('categoryParam' => $returnedList, 'paramValue' =>array());
        }
    }

    private function getParamsValue($paramList, $shopId, $categoryList)
    {
        $categoryQueryMark = $this->getQueryMark($categoryList);

        $getParamValueQuery = $this->dbh->prepare("SELECT gp.value FROM goods_param gp JOIN params p ON gp.param_id=p.id WHERE p.title LIKE ? AND shop_id=? AND gp.category_id IN ({$categoryQueryMark}) GROUP BY gp.value");
        $paramValue = array();
        foreach($paramList as $param){
            if (in_array($param, $this->disabledParam)){
                continue;
            }
            $getParamValueQuery->execute(array_merge(array($param, $shopId), $categoryList));
            $paramValue[$param] = array();
            foreach ($getParamValueQuery->fetchAll(\PDO::FETCH_ASSOC) as $row){
                $value = trim($row['value']);
                if ($value){
                    $paramValue[$param][] = $value;
                }
            }
            if (count($paramValue[$param]) > static::MAX_COUNT_PARAMS_VALUE){
                $paramValue[$param] = array();
            } else {
                sort($paramValue[$param]);
            }
            }
        return $paramValue;
    }

    public function getCategoryWithChildList()
    {
        $output = array();
        $preparedList = array();
        foreach ($this->categoryList as $row){
            $preparedList[$row['category_id']] = $row['parent_id'];
        }
        $this->categoryList = $preparedList;
        foreach ($preparedList as $key=>$row){
            $output[$key] = $this->getChildsCategory($key);
        }
        return $output;
    }

    private function getChildsCategory($category)
    {
        $childs = array();
        foreach ($this->categoryList as $key => $row){
            if ($row == $category){
                if ($newChilds = $this->getChildsCategory($key)){
                    $childs = array_unique(array_merge($childs, $newChilds));
                }
            }
        }
        if (!$childs){
            $childs = array($category);
        }
        return $childs;
    }

    public function setPrepareQuery(\PDOStatement $query)
    {
        $this->preparedQuery = $query;
    }

    public function getParamsWithValueForCategories($shopId, $categoriesId)
    {
        $returnedList = array();
        $queryMark = $this->getQueryMark($categoriesId);
        $query = $this->dbh->prepare("SELECT params_value FROM categories_param WHERE categories_id IN ({$queryMark}) AND shop_id=?");
        $query->execute(array_merge($categoriesId, array($shopId)));
        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $values = unserialize($row['params_value']);
            $returnedList = array_merge($returnedList, $values);
        }
        return $returnedList;
    }
}