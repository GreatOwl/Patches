CREATE TABLE `patches` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(255) NOT NULL DEFAULT '',
  `patch` int(5) NOT NULL,
  `query` text NOT NULL,
  `status` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;