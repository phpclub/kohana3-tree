
--DROP TABLE IF EXISTS `ns_tree`;

CREATE TABLE `ns_tree` (
  `id`    bigint AUTO_INCREMENT NOT NULL,
  `name`  varchar(50) NOT NULL,
  `lft`   bigint NOT NULL,
  `rgt`   bigint NOT NULL,
  /* Keys */
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE INDEX `nslrl_idx`
  ON `ns_tree`
  (`lft`, `rgt`);


INSERT INTO 
	ns_tree
VALUES
	(1,'ELECTRONICS',1,20),(2,'TELEVISIONS',2,9),(3,'TUBE',3,4),
	(4,'LCD',5,6),(5,'PLASMA',7,8),(6,'PORTABLE ELECTRONICS',10,19),
	(7,'MP3 PLAYERS',11,14),(8,'FLASH',12,13),
	(9,'CD PLAYERS',15,16),(10,'2 WAY RADIOS',17,18);