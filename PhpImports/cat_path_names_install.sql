DELIMITER $$

DROP PROCEDURE IF EXISTS spTokenize $$
 
CREATE PROCEDURE spTokenize (str varchar(255), delim char(1))
BEGIN
  DECLARE i INT DEFAULT 0;
  DECLARE ilen INT;
  create temporary table if not exists `tmptokens`(val varchar(50));
  truncate table `tmptokens`;
  
  SET ilen = (length(replace(str, delim, concat(delim, ' ')))  - length(str));
  
  WHILE i <= ilen DO
    insert into `tmptokens`(val) select(substring_index(SUBSTRING_INDEX(str, delim, i+1), delim, -1));
    SET i = i + 1;
  END WHILE;

--  SELECT * FROM tokens;

END $$

DROP PROCEDURE IF EXISTS `spGetPath` $$
 
CREATE DEFINER=`ars_dbroot`@`localhost` PROCEDURE `spGetPath`(INOUT pf varchar(1200))
BEGIN
	DECLARE l_id int;
    DECLARE l_part varchar(255);
    DECLARE done int default FALSE;
    
	DECLARE cursor_i CURSOR FOR 
		SELECT paths.entity_id as id, paths.name as name FROM catalog_category_flat_store_1 as paths join `tmptokens` as tokens on paths.entity_id=tokens.val WHERE paths.entity_id>1;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

	call spTokenize(pf, '/');
    -- add an additional '/' at the end. What does it do? Well, it encloses each entity_id in well defined boundaries and avoids the bug of partially matching the wrong id's during string replacement below.
   SET pf = CONCAT(pf, '/');    
        
	OPEN cursor_i;
	read_loop: LOOP
		FETCH cursor_i INTO l_id, l_part;
		IF done THEN
		  LEAVE read_loop;
		END IF;
		SET pf = replace(pf, CONCAT('/',l_id,'/'), CONCAT('/',l_part,'/'));
	END LOOP;
	CLOSE cursor_i;

	-- remove trailing '/' added above
	SET pf = SUBSTRING(pf, 1, length(pf)-1);
    
	-- remove starting root/, i.e., '1/'
    IF pf LIKE '1/%' THEN 
		SET pf = SUBSTRING(pf, 3);
	END IF;
    
--    select pf;

END $$


DROP PROCEDURE IF EXISTS `spGetCatPathNames` $$

CREATE DEFINER=`ars_dbroot`@`localhost` PROCEDURE `spGetCatPathNames`()
BEGIN
	DECLARE l_id int;
--    DECLARE l_path varchar(255);
    DECLARE l_pathname varchar(1200);
    DECLARE done int default FALSE;
    
	DECLARE cursor_i CURSOR FOR SELECT entity_id, path FROM catalog_category_flat_store_1;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

	create table if not exists `art_category_path_name`(entity_id int, path_name varchar(1200));
	truncate table `art_category_path_name`;

	OPEN cursor_i;
	read_loop: LOOP
		FETCH cursor_i INTO l_id, l_pathname;
		IF done THEN
		  LEAVE read_loop;
		END IF;
        call spGetPath(l_pathname);
		INSERT INTO `art_category_path_name`(entity_id, path_name) VALUES(l_id, l_pathname);
	END LOOP;
	CLOSE cursor_i;

    select * FROM `art_category_path_name`;
END $$

DELIMITER ;