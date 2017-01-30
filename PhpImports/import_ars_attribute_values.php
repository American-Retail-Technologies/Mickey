<?php  

//connect to the database 
//$connect = mysqli_connect("localhost","ars_dbroot","American1","ars_dev_magento1");
$connect = mysqli_connect("ars-mysql1.c6cjzokfsjyi.us-west-2.rds.amazonaws.com","ars_dbroot","American1","sm_market_quickstart2");
 
// http://stackoverflow.com/questions/4565195/mysql-how-to-insert-into-multiple-tables-with-foreign-keys
// 

if ($_FILES['csv']['size'] > 0) { 

  try {
  //get the csv file 
    $file = $_FILES['csv']['tmp_name']; 
    $handle = fopen($file,"r"); 
    $row_strings = "";
    $tmp_query = "";
    $attribute_code = $_POST['attribute_code'];
    $result = mysqli_query($connect, "SELECT attribute_id FROM `eav_attribute` WHERE attribute_code='".$attribute_code."'");
    $attribute_id = $result->fetch_object()->attribute_id;
    if ($attribute_id > 0) {
      //loop through the csv file and insert into database 
      do { 
          if ($data[0]) { 
              // Query 1 to Insert attribute option
              mysqli_query($connect, "INSERT INTO `eav_attribute_option` (attribute_id) VALUES (".$attribute_id.");");
              $new_option_id = mysqli_insert_id($connect);
              // Insert the value read for 2 stores, 0 and 2
              mysqli_query($connect, "INSERT INTO `eav_attribute_option_value` (store_id, option_id, value) VALUES (0, ".$new_option_id.", '".addslashes($data[0])."');");
              //mysqli_query($connect, "INSERT INTO `eav_attribute_option_value` (store_id, option_id, value) VALUES (1, ".$new_option_id.", '".addslashes($data[0])."');");
              //mysqli_query($connect, "INSERT INTO `eav_attribute_option_value` (store_id, option_id, value) VALUES (2, ".$new_option_id.", '".addslashes($data[0])."');");
              $row_strings .= $attribute_id ." : ".$new_option_id ." : ".$data[0]."<BR>";
          } 
      } while ($data = fgetcsv($handle,1000,",","'")); 
      //redirect 
      header('Location: import_ars_attribute_values.php?success=1&row_strings='.substr($row_strings,-1000)); die;
    } else {
      header('Location: import_ars_attribute_values.php?failure=1&row_strings=Attribute Code '.$attribute_code.' not found'); die;      
    }    
  }
  //catch exception
  catch(Exception $e) {
    echo 'Exception Message: ' .$e->getMessage();
  }

} 

?> 

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<title>Import Attribute Option Values</title> 
</head> 

<body> 

<?php 
if (!empty($_GET[success])) { 
echo "<b>Your file has been imported.</b><br>".$_GET[row_strings]."<br>"; 
} //generic success notice 
else if (!empty($_GET[failure])) { 
echo "<b>Your file import FAILED.</b><br>".$_GET[row_strings]."<br>"; 
} //generic failure notice 
?> 

<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1"> 
  Attribute Code: <input type="text" name="attribute_code" value="<?php echo $attribute_code;?>"><br /> 
  Choose your file: <br /> 
  <input name="csv" type="file" id="csv" /> 
  <input type="submit" name="Submit" value="Submit" /> 
</form> 

</body> 
</html> 