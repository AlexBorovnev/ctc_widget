<?php
function __autoload($class_name)
{
    require_once realpath(__DIR__ . '/../' . str_replace('\\', '/', $class_name) . '.php');
}
error_reporting(E_ERROR | E_PARSE);
$titleType = array(
    'div_kid' => \model\Categories::MODEL_VENDOR_TITLE,
    'div_tech' => \model\Categories::NAME_TITLE,
    'div_appliance' => \model\Categories::NAME_TITLE,
    'div_home' => \model\Categories::NAME_TITLE,
    'div_bs' => \model\Categories::MODEL_VENDOR_TITLE,
    'div_beauty' => \model\Categories::NAME_TITLE,
    'div_fashion' => \model\Categories::MODEL_VENDOR_TITLE,
    'div_gifts' => \model\Categories::NAME_TITLE,
    'div_book' => \model\Categories::AUTHOR_TITLE
);
$db = new library\UpdateDB($argv[1], $argv[2]);
$categoryTitle = \model\Categories::MODEL_VENDOR_TITLE;
foreach ($titleType as $catName => $title){
    if (strpos($argv[3], $catName) !== false){
        $categoryTitle = $title;
    }
}
switch ($argv[3]){
    case 'before':
        $db->beforeAddAction();
        break;
    case 'after':
        $db->afterAddAction();
        break;
    default:
        $db->setParser(str_replace('.zip', '.xml', $argv[3]));
        $db->updateBase($categoryTitle);
        break;
}