CREATE TABLE `adminsession` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL DEFAULT 0,
  `identifier` varchar(64) NOT NULL DEFAULT '',
  `date`  datetime NOT NULL,
  `data` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
