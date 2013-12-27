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
        $qMarks = 'shop_id = ?';
        $qValue = array($data['shopId']);
        if (!empty($data['parentId'])) {
            $qMarks .= ' AND parent_id IN (' . $this->getQueryMark($data['parentId']) . ')';
            $qValue = array_merge($qValue, $data['parentId']);
        }
        if (!empty($data['categoryId'])) {
            $qMarks .= ' AND category_id IN (' . $this->getQueryMark($data['categoryId']) . ')';
            $qValue = array_merge($qValue, $data['categoryId']);
        }
        $categoryList = $this->dbh->prepare("SELECT * FROM categories WHERE $qMarks");
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