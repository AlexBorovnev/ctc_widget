<?php
namespace View;

use library\Config;
use model\Categories;

class View
{

    private static $instance = null;
    private $storage = array();
    private $tree = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new View;
        }
        return self::$instance;
    }

    public function __set($key, $value)
    {
        $this->storage[$key] = $value;
    }

    public function __get($key)
    {
        return $this->storage[$key];
    }

    function render($tpl)
    {
        ob_start();
        require_once('header.php');
        require_once($tpl);
        require_once('footer.php');
        echo ob_get_clean();

    }

    function simpleRender($tpl)
    {
        ob_start();
        require_once($tpl);
        echo ob_get_clean();
    }

    public function meta($pageTitle, $url, $shopId = "", $isSubPage = false)
    {
        $this->pageTitle = $pageTitle;
        $this->url = $url;
        $this->shopId = $shopId;
        $this->isSubPage = $isSubPage;
    }

    public function getTreeView($dataTree, $shopId, $treeId = 'myTree')
    {
        if ($this->tree) {
            return $this->tree;
        } else {
            $this->tree =  $this->buildTreeView($dataTree, $shopId, $treeId);
            return $this->tree;
        }
    }

    private function buildTreeView($dataTree, $shopId, $treeId)
    {

        $view = "<div class=\"".$treeId." tree\" onclick=\"tree_toggle(arguments[0], ".$shopId.");\"><ul class=\"Container\" >";
        foreach ($dataTree as $catId=>$node){
            if ($node['pid'] == 0){
                $view .= $this->buildNode($node, array('IsRoot', 'Node'));

            }
        }
        $view .= '</ul></div>';
        return $view;
    }

    private function buildNode($node, $classList)
    {
        $view = '';
        $dataAttr = ' data-download=true data-child-count=' .$node['child_count'] . ' data-cid="' . $node['cid'] . '" data-pid="' . $node['pid'] . '" data-title="' . $node['title'] . '"';
        if (isset($node['childs'])){
            $view .= '<li class="' . implode(' ', array_merge($classList, array('ExpandClosed'))).'"';
            $view .= $dataAttr;
            $view .= '>';
            $view .= '<div class="Expand"></div><div class="Content">'. $node['title'].'</div>';
            $view .= '<ul class="Container">';
            foreach ($node['childs'] as $child){
                $view .= $this->buildNode($child, array('Node'));
            }
            $view .= '</ul></li>';
        } else {
            $view .= '<li class="' . implode(' ', array_merge($classList, array('ExpandLeaf'))) .'"';
            $view .= $dataAttr;
            $view .= '>';
            $view .= '<div class="Expand"></div><div class="Content">'. $node['title'].'</div>';
            $view .= '</li>';
        }
        return $view;
    }

    public function buildParamsBlock($categoryIds, $selectedParams, $shopId)
    {
        $categoriesModel = new Categories(Config::getInstance()->getDbConnection());
        $params = $categoriesModel->getParamsWithValueForCategories($shopId, $categoryIds);
        $outputHTML = '';
        foreach ($params as $paramName => $paramValues){
            $outputHTML .= '<div class="paramContainer clearfix">';
            $outputHTML .= '<div class="paramBlock clearfix"><h4><ul><li onclick="displayParamBlock(arguments[0])">'.$paramName.'</li></ul></h4></div>';
            if (isset($selectedParams[$paramName])){
                $outputHTML .= '<ul class="paramList">';
            } else {
                $outputHTML .= '<ul class="paramList" style="display:none;">';
            }
            foreach ($paramValues as $value){
                $active = '';
                if (isset($selectedParams[$paramName]) && in_array(htmlspecialchars($value), $selectedParams[$paramName])){
                    $active = ' active';
                }
                $outputHTML .= '<li class="param'.$active.'" data-param-value="'.$value.'" data-param-name="'.$paramName.'">'.$value.'</li>';
            }
            $outputHTML .= '</ul>';
            $outputHTML .= '</div>';
        }
        return $outputHTML;
    }

}