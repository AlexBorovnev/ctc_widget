<?php
use library\Config;

require_once __DIR__ . '/library/Config.php';
try{
    $dbh = Config::getInstance()->getDbConnection();
    $dbh->prepare(getQuery())->execute();
} catch (\Exception $e){
    var_dump($e->getMessage());
}

function getQuery (){
    return <<<EOL

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры для таблица ctc.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) unsigned NOT NULL,
  `shop_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `category_id` (`category_id`,`shop_id`),
  KEY `FK_categories_shops` (`shop_id`),
  CONSTRAINT `FK_categories_shops` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы ctc.categories: ~479 rows (приблизительно)
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;


-- Дамп структуры для таблица ctc.currency
CREATE TABLE IF NOT EXISTS `currency` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `currency_id` varchar(50) NOT NULL DEFAULT '0',
  `rate` float NOT NULL DEFAULT '0',
  `shop_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `currency_id` (`currency_id`,`shop_id`),
  KEY `FK__shops` (`shop_id`),
  CONSTRAINT `FK__shops` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы ctc.currency: ~1 rows (приблизительно)
/*!40000 ALTER TABLE `currency` DISABLE KEYS */;
/*!40000 ALTER TABLE `currency` ENABLE KEYS */;


-- Дамп структуры для таблица ctc.goods
CREATE TABLE IF NOT EXISTS `goods` (
  `category_id` int(11) unsigned NOT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `offer_id` varchar(20) DEFAULT NULL,
  `price` varchar(20) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `currency` varchar(20) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `common_data` text,
  `is_available` int(1) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `color` varchar(50) DEFAULT NULL,
  UNIQUE KEY `offer_id` (`offer_id`,`shop_id`),
  KEY `category_id` (`category_id`,`shop_id`),
  KEY `shop_id` (`shop_id`),
  CONSTRAINT `FK_goods_categories_1388127620` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_goods_shops_1388127620` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы ctc.goods: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `goods` DISABLE KEYS */;
/*!40000 ALTER TABLE `goods` ENABLE KEYS */;


-- Дамп структуры для таблица ctc.rules
CREATE TABLE IF NOT EXISTS `rules` (
  `widget_id` int(10) unsigned NOT NULL,
  `shop_id` int(10) NOT NULL,
  `rules_type` int(10) NOT NULL,
  `source` text DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  UNIQUE KEY `widget_id` (`widget_id`,`position`,`shop_id`),
  KEY `FK_rules_widgets` (`widget_id`),
  KEY `FK_rules_rules_type` (`rules_type`),
  KEY `FK_rules_shops` (`shop_id`),
  CONSTRAINT `FK_rules_rules_type` FOREIGN KEY (`rules_type`) REFERENCES `rules_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_rules_shops` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_rules_widgets` FOREIGN KEY (`widget_id`) REFERENCES `widgets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы ctc.rules: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `rules` ENABLE KEYS */;


-- Дамп структуры для таблица ctc.rules_type
CREATE TABLE IF NOT EXISTS `rules_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `title` varchar(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы ctc.rules_type: ~2 rows (приблизительно)
/*!40000 ALTER TABLE `rules_type` DISABLE KEYS */;
INSERT INTO `rules_type` (`id`, `type`, `title`) VALUES
	(1, 'rule', 'Правило'),
	(2, 'single', 'Указанный товар');
/*!40000 ALTER TABLE `rules_type` ENABLE KEYS */;


-- Дамп структуры для таблица ctc.shops
CREATE TABLE IF NOT EXISTS `shops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы ctc.shops: ~1 rows (приблизительно)
/*!40000 ALTER TABLE `shops` DISABLE KEYS */;
/*!40000 ALTER TABLE `shops` ENABLE KEYS */;


-- Дамп структуры для таблица ctc.sites
CREATE TABLE IF NOT EXISTS `sites` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы ctc.sites: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `sites` ENABLE KEYS */;


-- Дамп структуры для таблица ctc.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL DEFAULT '0',
  `password` varchar(32) NOT NULL DEFAULT '0',
  `user_name` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп структуры для таблица ctc.widgets
CREATE TABLE IF NOT EXISTS `widgets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(11) DEFAULT NULL,
  `skin_id` int(11) DEFAULT NULL,
  `shop_id` int(11) NOT NULL,
  `common_rule` text,
  `title` text NULL,
  `position_count` int(11) NOT NULL,
  `click_cnt` int(11) NOT NULL DEFAULT '0',
  `time_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_widgets_widget_type` (`type_id`),
  KEY `FK_widgets_widget_skin` (`skin_id`),
  KEY `FK_widgets_shops` (`shop_id`),
  CONSTRAINT `FK_widgets_shops` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_widgets_widget_skin` FOREIGN KEY (`skin_id`) REFERENCES `widget_skin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_widgets_widget_type` FOREIGN KEY (`type_id`) REFERENCES `widget_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы ctc.widgets: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `widgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `widgets` ENABLE KEYS */;


-- Дамп структуры для таблица ctc.widget_skin
CREATE TABLE IF NOT EXISTS `widget_skin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы ctc.widget_skin: ~3 rows (приблизительно)
/*!40000 ALTER TABLE `widget_skin` DISABLE KEYS */;
INSERT INTO `widget_skin` (`id`, `title`) VALUES
	(1, 'СТС'),
	(2, 'Домашний'),
	(3, 'Видеоморе');
/*!40000 ALTER TABLE `widget_skin` ENABLE KEYS */;


-- Дамп структуры для таблица ctc.widget_type
CREATE TABLE IF NOT EXISTS `widget_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы ctc.widget_type: ~3 rows (приблизительно)
/*!40000 ALTER TABLE `widget_type` DISABLE KEYS */;
INSERT INTO `widget_type` (`id`, `title`) VALUES
	(1, 'Маленький'),
	(2, 'Большой'),
	(3, 'Свободный');
/*!40000 ALTER TABLE `widget_type` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

EOL;

}