<?php
use library\XmlLoadWidget;
use library\TemporaryWidget;
use library\DbLoadWidget;
use library\ApiServer;

define ('HOST', ($_SERVER['HTTPS'] == 'on') ? 'https' : 'http' . '://' . $_SERVER['HTTP_HOST'] . '/');

function __autoload($class_name)
{
    require_once __DIR__ . '/../' . str_replace('\\', '/', $class_name) . '.php';
}

$widget = new TemporaryWidget();
$rout = explode('/', strtok(trim($_SERVER['REQUEST_URI'], '/'), '?'));
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
    case 'picture':
        header('Location: picture.php?' . $_SERVER['QUERY_STRING']);
        break;
    case 'handler':
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $apiServer = new ApiServer();
            $apiServer->run($_POST);
        }
        break;
    case 'admin':

    default:
        header("HTTP/1.1 404 Not Found");
        exit;
}