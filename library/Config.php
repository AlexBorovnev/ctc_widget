<?php
namespace library;

class Config
{
    const CONFIG_PATH = '/../config.ini';
    const CONFIG_LOCAL_PATH = '/../config_local.ini';
    protected static $instance = null;
    protected static $busy = false;

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

    public static function getConfig()
    {
        return array_merge(
            parse_ini_file(__DIR__ . self::CONFIG_PATH, true),
            parse_ini_file(__DIR__ . self::CONFIG_LOCAL_PATH, true)
        );
    }

    public function setBusyStatus($status)
    {
        if ($status) {
            return @file_put_contents(__DIR__ . '/../busy', time());
        } else {
            return @unlink(__DIR__ . '/../busy');
        }
    }

    public function getBusyStatus()
    {
        return (file_exists(__DIR__ . '/../busy')) ? true : false;
    }
}
