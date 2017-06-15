<?php  

//connect to the database 
//$connect = mysqli_connect("ars-mysql1.c6cjzokfsjyi.us-west-2.rds.amazonaws.com","ars_dbroot","American1","sm_market_quickstart2"); 
$connect = mysqli_connect("localhost","ars_dbroot","American1","bitnami_magento"); 
// http://stackoverflow.com/questions/4565195/mysql-how-to-insert-into-multiple-tables-with-foreign-keys
// 

if ($_FILES['csv']['size'] > 0) { 

    //get the csv file 
    $file = $_FILES['csv']['tmp_name']; 
    $handle = fopen($file,"r"); 
    $row_strings = "";
    //loop through the csv file and insert into database 
    do { 
        $tmp_query = "INSERT INTO url_rewrite (entity_type, entity_id, redirect_type, store_id, request_path, target_path) VALUES ('custom', 0, 301, 2,'";
        if ($data[0]) { 
            // Query 1 to Insert attribute option
			$tmp_query .= addslashes($data[0])."','".addslashes($data[1])."');";
            mysqli_query($connect, $tmp_query);
			$new_option_id = mysqli_insert_id($connect);
            $row_strings .= $new_option_id.", ";
            //$row_strings = $tmp_query;
        } 
    } while ($data = fgetcsv($handle,2000,",","'")); 
    // 

    //redirect 
    header('Location: import_custom_url_rewrite.php?success=1&row_strings='.$row_strings); die; 

} 

?> 

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<title>Import URL Rewrite (no header in the file)</title> 
</head> 

<body> 

<?php if (!empty($_GET[success])) { echo "<b>Your file has been imported.</b><br>".$_GET[row_strings]."<br>"; } //generic success notice ?> 

<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1"> 
  Choose your file: <br /> 
  <input name="csv" type="file" id="csv" /> 
  <input type="submit" name="Submit" value="Submit" /> 
</form> 

</body> 
</html> 