<?php
namespace library;

class Common{

    protected static $instance = null;

    private function __construct(){}

    public static function getInstance(){
        if (is_null(self::$instance)){
            self::$instance = new Common();
        }
        return self::$instance;
    }

    public static function getQueryMark($data)
    {
        return str_repeat('?,', count($data) - 1) . '?';
    }
}