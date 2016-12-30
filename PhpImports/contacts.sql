SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL auto_increment,
  `contact_first` varchar(255) character set latin1 default NULL,
  `contact_last` varchar(255) character set latin1 default NULL,
  `contact_email` varchar(255) character set latin1 default NULL,
  PRIMARY KEY  (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;




SELECT MAX(eao.option_id) 
FROM `eav_attribute_option` eao, `eav_attribute` ea 
WHERE eao.attribute_id = ea.attribute_id 
AND ea.attribute_code='ars_color'


INSERT INTO `eav_attribute_option` (attribute_id) VALUES (161);
INSERT INTO `eav_attribute_option_value` (option_id, store_id, value) VALUES (LAST_INSERT_ID(), 2, 'darkish blue');
     