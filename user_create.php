<?php
use library\Config;

require_once __DIR__ . '/library/Config.php';
try{
    $dbh = Config::getInstance()->getDbConnection();
    $dbh->prepare(getQuery())->execute();
} catch (\Exception $e){
    var_dump($e->getMessage());
}
function getQuery(){
    return <<<EOL
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL DEFAULT '0',
  `password` varchar(32) NOT NULL DEFAULT '0',
  `user_name` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `login`, `password`, `user_name`) VALUES
	(1, 'admin', MD5('admin'), 'admin');
EOL;

}