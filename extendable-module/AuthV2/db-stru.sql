CREATE TABLE IF NOT EXISTS `AuthV2User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `textId` varchar(50) NOT NULL,
  `pass` varchar(50) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `SmsgwSession` (
  `id` char(32) NOT NULL DEFAULT '',
  `name` char(32) NOT NULL DEFAULT '',
  `modified` int(11) DEFAULT NULL,
  `lifetime` int(11) DEFAULT NULL,
  `data` longblob,
  PRIMARY KEY (`id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

