<?php
use library\XmlLoadWidget;
use library\TemporaryWidget;
use library\DbLoadWidget;

function __autoload($class_name)
{
    require_once __DIR__ . '/' . str_replace('\\', '/', $class_name) . '.php';
}

$widget = new TemporaryWidget();
if (!empty($_GET['widget_id'])) {
    $widgetsId = strip_tags(trim($_GET['widget_id']));
    require_once('widget.php');
} elseif (!empty($_GET['clear_widget_id'])) {
    $widget->deleteWidget(strip_tags(trim($_GET['clear_widget_id'])));
}elseif (isset($_GET['clear_widget_all'])) {
    $widget->deleteAllWidget();
}

