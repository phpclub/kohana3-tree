
CREATE TABLE `ns_tree` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `lft` int(11) unsigned NOT NULL,
  `rgt` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lft_index` (`lft`),
  UNIQUE KEY `rgt_index` (`rgt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO 
	`ns_tree`
VALUES
	(1,'ELECTRONICS',1,20),(2,'TELEVISIONS',2,9),(3,'TUBE',3,4),
	(4,'LCD',5,6),(5,'PLASMA',7,8),(6,'PORTABLE ELECTRONICS',10,19),
	(7,'MP3 PLAYERS',11,14),(8,'FLASH',12,13),
	(9,'CD PLAYERS',15,16),(10,'2 WAY RADIOS',17,18);