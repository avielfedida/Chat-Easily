CREATE TABLE `messages` (
  `mes_id` int(11) NOT NULL AUTO_INCREMENT,
  `mes_send_by_id` int(11) DEFAULT NULL,
  `mes_content` text,
  `mes_room_id` int(11) DEFAULT NULL,
  `mes_dop` int(11) DEFAULT NULL,
  PRIMARY KEY (`mes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `rooms` (
  `ro_id` int(11) NOT NULL AUTO_INCREMENT,
  `ro_owner_id` int(11) DEFAULT NULL,
  `ro_invited_id` varchar(255) DEFAULT '',
  PRIMARY KEY (`ro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `us_id` int(11) NOT NULL AUTO_INCREMENT,
  `us_nick` varchar(255) DEFAULT NULL,
  `us_ip` varchar(255) DEFAULT NULL,
  `us_rank` tinyint(4) DEFAULT '1',
  `us_rc` tinyint(4) NOT NULL DEFAULT '0',
  `us_ldoa` int(11) DEFAULT NULL,
  PRIMARY KEY (`us_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;