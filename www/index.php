<?php
use library\XmlLoadWidget;
use library\TemporaryWidget;
use library\DbLoadWidget;
use library\ApiServer;
use library\Config;

define ('HOST', 'http://' . $_SERVER['HTTP_HOST'] . '/'.  getScripPath());

function __autoload($class_name)
{
    require_once __DIR__ . '/../' . str_replace('\\', '/', $class_name) . '.php';
}

function getScripPath(){
    $rout = array_diff(explode('/', $_SERVER['SCRIPT_NAME']), array(''));
    array_pop($rout);
    $path = implode('/', $rout);
    return ($path) ? $path . '/' : '';
}
$widget = new DbLoadWidget();
$rout = explode('/', strtok(trim(str_replace(getScripPath(), '', $_SERVER['REQUEST_URI']), '/'), '?'));
switch ($rout[0]) {
    case 'clear_widget_all':
        $widget->deleteAllWidget();
        break;
    case 'clear_widget_id':
        if (!empty($rout[1])) {
            $widget->deleteWidget(strip_tags($rout[1]));
        }
        break;
    case 'widget_id':
        if (!empty($rout[1])) {
            $widgetsId = strip_tags($rout[1]);
            require_once('widget.php');
        }
        break;
    case 'handler':
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $apiServer = new ApiServer();
            $apiServer->run($_POST);
        }
        break;
    case 'admin':
        if (Config::getInstance()->getBusyStatus() == true) {
            echo "<h1>Database Update Now</h1>";
        } else {
            echo "<h1>COOL</h1>";
        }
        break;
    default:
        header("HTTP/1.1 404 Not Found");
        exit;
}