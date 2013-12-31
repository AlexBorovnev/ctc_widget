<?php
use library\DbLoadWidget;
use library\ApiServer;
use library\Config;
use library\Common;
use model\Shops;
use model\Widgets;
use view\View;

define ('HOST', 'http://' . $_SERVER['HTTP_HOST'] . '/'.  getScripPath());
define ('REV', time());


function __autoload($class_name)
{
    require_once __DIR__ . '/' . str_replace('\\', '/', $class_name) . '.php';
}

function getScripPath(){
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
            require_once('view/widget.php');
        }
        break;
    case 'handler':
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $apiServer = new ApiServer(Config::getInstance()->getConfig(), Config::getInstance()->getDbConnection());
            $apiServer->run($_POST);
        }
        break;
    case 'admin':
        session_start();
        auth();
        if (Config::getInstance()->getBusyStatus() == true) {
            echo "<h1>Database Update Now</h1>";
        } else {
            $pageName = empty($rout[1]) ? '' : $rout[1];
            $pageParam = empty($rout[2]) ? '1' : $rout[2];
            showAdminPage($pageName, $pageParam);
        }
        break;
    default:
        header("HTTP/1.1 404 Not Found");
        exit;
}
function auth(){
    require_once 'view/auth.php';
}
function showAdminPage($page = '', $param = 1){
    switch ($page){
        case '':
            showMainAdminPage();
            break;
        case 'shop':
            showShopPage($param);
            break;
        case 'add':
        	
        	View::getInstance()->render('add_widget.php');
        break;
        default:
            header("HTTP/1.1 404 Not Found");
            exit;
    }
}

function showShopPage($shopId){
//    $shopsModel = new Shops(Config::getInstance()->getDbConnection());
    $widgetsModel = new Widgets(Config::getInstance()->getDbConnection());
//    $shopsList = $shopsModel->getAll();
    $widgetsList = $widgetsModel->getWidgetList(array('shopId' => $shopId));
    
    $typeList = array();
    foreach($widgetsModel->getTypeList() as $elem)
		$typeList[$elem['id']] = $elem['title'];
    
    
    $skinList = array();
    foreach($widgetsModel->getSkinList() as $elem)
		$skinList[$elem['id']] = $elem['title'];
    
    $view = View::getInstance();
    $view->typeList = $typeList;
    $view->skinList = $skinList;
//    $view->shopsList = $shopsList;
    $view->widgetsList = $widgetsList;
    $view->shopId = $shopId;
    View::getInstance()->render('shop.php');
    
}

function showMainAdminPage(){
	$shopsModel = new Shops(Config::getInstance()->getDbConnection());
	$shopsList = $shopsModel->getAll();
	
	$view = View::getInstance();
	$view->shopsList = $shopsList;
	$view->render('admin.php');
}

function makeLink($localPath){
	if($localPath[0] == '/')
		$localPath = substr($localPath, 1);
	return HOST . $localPath;
}