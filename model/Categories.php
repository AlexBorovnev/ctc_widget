<?php
namespace model;

use library\Common;

class Categories extends AbstractModel
{

    protected $fields = array(
        'categoryId' => 'category_id',
        'shopId' => 'shop_id',
        'parentId' => 'parent_id',
        'title' => 'title',
        'updatedAt' => 'updated_at'
    );

    public function getCategoriesList($data)
    {
        $qMarks = '';
        $qValue = array($data['shopId']);
        if (!empty($data['parentId'])) {
            $qMarks .= ' AND c.parent_id IN (' . $this->getQueryMark($data['parentId']) . ')';
            $qValue = array_merge($qValue, $data['parentId']);
        }
        if (!empty($data['categoryId'])) {
            $qMarks .= ' AND c.category_id IN (' . $this->getQueryMark($data['categoryId']) . ')';
            $qValue = array_merge($qValue, $data['categoryId']);
        }
        $categoryList = $this->dbh->prepare("SELECT c.category_id, c.shop_id, c.title, c.parent_id FROM categories c LEFT JOIN goods g ON g.category_id=c.category_id AND g.shop_id=c.shop_id WHERE  (g.is_available=1 OR c.category_id IN (SELECT parent_id AS category_id FROM categories GROUP BY parent_id)) AND c.shop_id=? $qMarks GROUP BY c.category_id");
        $categoryList->execute($qValue);
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
}