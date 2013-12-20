<?php
namespace library;

class Config
{
    const CONFIG_PATH = '/../config.ini';
    const CONFIG_LOCAL_PATH = '/../config_local.ini';
    protected static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    public static function getConfig(){
        return array_merge(parse_ini_file(__DIR__ . self::CONFIG_PATH, true), parse_ini_file(__DIR__ . self::CONFIG_LOCAL_PATH, true));
    }
}
