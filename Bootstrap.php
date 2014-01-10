<?php
use library\DbLoadWidget;
use library\ApiServer;
use library\Config;
use library\Common;
use model\Shops;
use model\Widgets;
use view\View;
use model\Rules;
use model\Categories;
use model\Goods;

define ('HOST', 'http://' . $_SERVER['HTTP_HOST'] . '/' . getScripPath());
define ('REV', time());

session_start();

function __autoload($class_name)
{
    require_once __DIR__ . '/' . str_replace('\\', '/', $class_name) . '.php';
}

function getScripPath()
{
    $rout = array_diff(explode('/', $_SERVER['SCRIPT_NAME']), array(''));
    array_pop($rout);
    $path = implode('/', $rout);
    return ($path) ? $path . '/' : '';
}

$widget = new DbLoadWidget(Config::getInstance()->getConfig(), Config::getInstance()->getDbConnection());
$rout = explode('/', strtok(trim(str_replace(getScripPath(), '', $_SERVER['REQUEST_URI']), '/'), '?'));
switch ($rout[0]) {
    case 'widget_id':
        if (!empty($rout[1])) {
            $widgetsId = strip_tags($rout[1]);
            $widgets = $widget->getWidget($widgetsId);
            $view = View::getInstance();
            $view->widgets = $widgets;
            $view->widgetId = $widgetsId;
            $view->simpleRender('widget.php');
        }
        break;
    case 'handler':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $apiServer = new ApiServer(Config::getInstance()->getConfig(), Config::getInstance()->getDbConnection());
            $apiServer->run($_POST);
        }
        break;
    case '':
    case 'admin':
        
        auth();
        if (Config::getInstance()->getBusyStatus() == true) {
            echo "<h1>Database Update Now</h1>";
        } else {
            $pageName = empty($rout[1]) ? '' : $rout[1];
            $pageParam = empty($rout[2]) ? '1' : $rout[2];
            $pageNum = empty($rout[3]) ? '1' : $rout[3];
            showAdminPage($pageName, $pageParam, $pageNum);
        }
        
//        var_dump();
        
        break;
    case 'logout':
        session_destroy();
        $loc = makeLink('/');
        header('location: ' . $loc);
    break;
    default:
        View::getInstance()->render('404.php');
}
function auth()
{
    require_once 'view/auth.php';
}
function showAdminPage($page = '', $param = 1, $pageNum = 1){
    $view = View::getInstance();
    switch ($page){
        case '':
            
            showMainAdminPage();
            break;
        case 'shop':
            showShopPage($param, $pageNum);
            break;
        case 'add':
            
            $view->shopId = $param;
            $shopModel = new Shops(Config::getInstance()->getDbConnection());

            $shop = $shopModel->getShop(array('shopId' => array($param)));
            $view->shop = json_encode($shop[0]);
            
            $view->meta('Создание виджета', '/admin/add/' . $shopId, $param, true);
            
            $view->render('add_widget.php');
            break;
        case 'edit':
           
            $rulesModel = new Rules(Config::getInstance()->getDbConnection());
          
            
            if ($view->widget = $rulesModel->prepareRuleToResponse($param)){
                $categoriesModel = new Categories(Config::getInstance()->getDbConnection());
                $categoriesList = $categoriesModel->getCategoriesList(array('shopId' => $view->widget['shopId']));
                $view->categories = array('list' => $categoriesList, 'count' => count($categoriesList));

                $view->shopId = $view->widget['shopId'];
                $goodsModel = new Goods(Config::getInstance()->getDbConnection());
                $colorList = $goodsModel->getColorList();;
                $view->colors =  $colorList;

                $shopModel = new Shops(Config::getInstance()->getDbConnection());
                $shop = $shopModel->getShop(array('shopId' => array($view->shopId)));
                $view->shop = json_encode($shop[0]);
				$view->meta('Редактирование виджета', '/admin/edit/' . $param, $view->shopId, true);

                $view->render('edit_widget.php');
            } else {
                $view->render('404.php');
            }
            break;
        default:
            View::getInstance()->render('404.php');
    }
}

function showShopPage($shopId, $page){
    //    $shopsModel = new Shops(Config::getInstance()->getDbConnection());
    $widgetsModel = new Widgets(Config::getInstance()->getDbConnection());
    //    $shopsList = $shopsModel->getAll();
    $pageCount = $widgetsModel->getWidgetsPage($shopId);
    $widgetsList = $widgetsModel->getCommonWidgetInfo(array('shopId' => $shopId), $page);

    $typeList = array();
    foreach ($widgetsModel->getTypeList() as $elem) {
        $typeList[$elem['id']] = $elem['title'];
    }
    $skinList = array();
    foreach ($widgetsModel->getSkinList() as $elem) {
        $skinList[$elem['id']] = $elem['title'];
    }
    $shopModel = new Shops(Config::getInstance()->getDbConnection());
    $shop = $shopModel->getShop(array('shopId' => array($shopId)));
    
    
    $view = View::getInstance();
    $view->pageCount = $pageCount;
    $view->typeList = $typeList;
    $view->skinList = $skinList;
    $view->currentPage = $page;
    //    $view->shopsList = $shopsList;
    $view->widgetsList = $widgetsList;
    $view->shopId = $shopId;
    
    $view->meta($shop[0]['title'], '/admin/shop/'.$shopId.'/', $shopId);
    
    $view->render('shop.php');

}

function showMainAdminPage(){
    $shopsModel = new Shops(Config::getInstance()->getDbConnection());
    $shopsList = $shopsModel->getAll();

    $view = View::getInstance();
    $view->shopsList = $shopsList;
    
    $view->meta('Главная', '/admin/');
    
    $view->render('admin.php');
}

function makeLink($localPath){
    if ($localPath[0] == '/') {
        $localPath = substr($localPath, 1);
    }
    return HOST . $localPath;
}

function showPagination($cur, $total, $link){
    $res = array();
    if($cur < 1)
        $cur = 1;
    $res[] = '<div class="pagenation clearfix">';
    $res[] = '<ul>';
    $cnt = $total;

    $begin = $cur - 1;
    $end = $cnt - $cur;

    $prev = $cur - 1;
    $next = $cur + 1;

    if($prev < 1)
        $prev = 1;
    if($next > $cnt )
        $next = $cnt;

    if($cur != 1){
        $res[] = "<li><a href=" . makeLink($link . 1) . ">&lt;&lt;</a></li>";
        $res[] = "<li><a href=" . makeLink($link . $prev) . ">&lt;</a></li>";
    }
    
    if($cur > 2)
        $res[] = "<li><a href=" . makeLink($link . ($cur-2)) . ">".($cur-2)."</a></li>";
    if($cur > 1)
        $res[] = "<li><a href=" . makeLink($link . ($cur-1)) . ">".($cur-1)."</a></li>";

    $res[] = "<li class=\"active\">$cur</li>";

    if($cur < $cnt)
        $res[] = "<li><a href=" . makeLink($link . ($cur+1)) . ">".($cur+1)."</a></li>";
    if($cur+1 < $cnt)
        $res[] = "<li><a href=" . makeLink($link . ($cur+2)) . ">".($cur+2)."</a></li>";

    if($cur != $cnt){
        $res[] = "<li><a href=" . makeLink($link . $next) . ">&gt;</a></li>";
        $res[] = "<li><a href=" . makeLink($link . $cnt) . ">&gt;&gt;</a></li>";
    }



    $res[] = "</ul>";
    $res[] = "</div>";

    return implode("\n", $res);
}

function breadcrumbs(View $view){
    
    $curPage = $view->pageTitle;
    $url = $view->url;
    $shopId = $view->shopId;
    $subPage = $view->isSubPage;
    
    $separ = '&gt;';
    
    $res[] = "<div class='breadcrumbs'><ul>";
    $res[] = "<li><a href='".makeLink('/')."'>Главная</a></li>";
    $res[] = "<li>$separ</li>";
    
    $curPage = "<li><a href='".makeLink($url)."'>{$curPage}</a></li>";
    
    
    
    if($subPage){
         $shopModel = new Shops(Config::getInstance()->getDbConnection());
         $shop = $shopModel->getShop(array('shopId' => array($shopId)));
         
        $shopPage = "<li><a href='".makeLink('/admin/shop/' . $shopId)."'>".$shop[0]['title']."</a></li>";
        
        $res[] = $shopPage;
        $res[] = "<li>$separ</li>";
    }
    if($shopId != ''){
        $res[] = $curPage;        
    }
    if(!$subPage && $shopId == ''){
        array_pop($res);
    }
    
    //if(strlen($shop) != 0 && $curPage != $shop){
//        
//        $toSubPage = "<li>{$shop}</li>";
//    }
    //if($subPage){
//        $res[] = "<li><a href='"makeLink"'</li>"; 
//    }
    $res[] = "</ul>";
    $res[] = "</div>";

    return implode("\n", $res);
    
}