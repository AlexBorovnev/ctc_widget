<?php
use library\Config;

require_once __DIR__ . '/library/Config.php';
try{
    $config = Config::getInstance()->getConfig();
    $dbh = Config::getInstance()->getDbConnection();
    $dbh->prepare(removeUsersQuery())->execute();
    $dbh->prepare(getCreateQuery())->execute();
    insertUsers($dbh, $config['users']);

} catch (\Exception $e){
    var_dump($e->getMessage());
}
function removeUsersQuery(){
    return "DROP TABLE `users`;";
}
function getCreateQuery(){
    return <<<EOL
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL DEFAULT '0',
  `password` varchar(32) NOT NULL DEFAULT '0',
  `user_name` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

EOL;
}
function insertUsers(\PDO $dbh, $data){
    $queryString = "INSERT INTO users (login, password) VALUES (?, MD5(?))";
    foreach ($data as $login => $password){
        $dbh->prepare($queryString)->execute(array($login, $password));
    }
}
