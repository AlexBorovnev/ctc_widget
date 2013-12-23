<?php
namespace library;

class Config
{
    const CONFIG_PATH = '/../config.ini';
    const CONFIG_LOCAL_PATH = '/../config_local.ini';
    protected static $instance = null;
    protected static $busy = false;
    protected static $config = array();
    protected static $dbh = null;

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
        if (!self::$config) {
            self::$config = array_merge(
                parse_ini_file(__DIR__ . self::CONFIG_PATH, true),
                parse_ini_file(__DIR__ . self::CONFIG_LOCAL_PATH, true)
            );
        }
        return self::$config;
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

    public function getDbConnection()
    {
        if (!self::$dbh) {
            $config = self::getConfig();
            self::$dbh = new \PDO(sprintf(
                "mysql:host=%s;dbname=%s;charset=UTF8",
                $config['db']['db_host'],
                $config['db']['db_name']
            ), $config['db']['login'], $config['db']['password']);
            self::$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$dbh;
    }
}
