<?php
namespace library;
use library\Config;
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

    public static function getUserId($login, $password){
        $authQuery = Config::getInstance()->getDbConnection()->prepare("SELECT id, user_name FROM users WHERE login=:login AND password=MD5(:password)");
        $authQuery->execute(array(':login' => $login, ':password' => $password));
        return $authQuery->fetch(\PDO::FETCH_ASSOC);
    }
}