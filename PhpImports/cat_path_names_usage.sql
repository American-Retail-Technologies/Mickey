-- ASSUMPTIONS:
--  Input IS catalog_category_flat_store_1
-- 		entity_id	=> int
-- 		name		=> max-length=255
-- 		path 	 	=> max-length=255
-- Output
-- 		entity_id	=> int (matches with entity_id above)
-- 		path_name 	=> nvarchar(1000)
--
-- (IMPORTANT) "Root" entity_id = 1
-- All path start with Root, i.e., 1/n/nn/nn/nn


-- Call for a specific path name (just an atomic call)
-- SET @a = '1/3/5';
-- call spGetPath(@a);
-- SELECT @a;

-- Usage:
-- ALL Cat Names 
-- call this once per session then pathnames are available in a table `art_category_path_name` (entity_id, path_name)
-- this call also returns the result of the `art_category_path_name` temp table
call spGetCatPathNames();

-- Now, you may join with various criterion

-- ALL
SELECT c.*, p.path_name FROM catalog_category_flat_store_1 c join art_category_path_name p on c.entity_id=p.entity_id; 

--  WHERE c.entity_id between 3 and 5
SELECT c.*, p.path_name FROM catalog_category_flat_store_1 c join art_category_path_name p on c.entity_id=p.entity_id and c.entity_id between 3 and 5; 

--  WHERE c.name like 'Category 2%'
SELECT c.*, p.path_name FROM catalog_category_flat_store_1 c join art_category_path_name p on c.entity_id=p.entity_id and c.name like 'Category 2%'; 
