<?php  

//connect to the database 
$connect = mysqli_connect("ars-aurora1-cluster-1.cluster-crymzjqricqv.us-west-2.rds.amazonaws.com","ars_dbroot","American1","ars_prod_magento1"); 
//$connect = mysqli_connect("ars-mysql.crymzjqricqv.us-west-2.rds.amazonaws.com","ars_dbroot","American1","ars_staging_magento1"); 
//$connect = mysqli_connect("ars-mysql.crymzjqricqv.us-west-2.rds.amazonaws.com","ars_dbroot","American1","ars_dev_magento1"); 
//$connect = mysqli_connect("localhost","ars_dbroot","American1","ars_prod_magento1"); 
// http://stackoverflow.com/questions/4565195/mysql-how-to-insert-into-multiple-tables-with-foreign-keys
// 

if ($_FILES['csv']['size'] > 0) {
	
    //get the csv file 
    $file = $_FILES['csv']['tmp_name']; 
    $handle = fopen($file,"r"); 
    $row_strings = "";
    //loop through the csv file and insert into database 
    do { 
        if ($data[0]) { 
            $request_path = $data[0];
            $target_path = $data[1];
            $update_query = "UPDATE url_rewrite SET target_path='".$target_path."' WHERE request_path='".$request_path."';";
            $insert_query = "INSERT INTO url_rewrite (entity_type, entity_id, redirect_type, store_id, request_path, target_path) VALUES ('custom', 0, 301, 0,'";
            $insert_query .= $request_path."','".$target_path."');";
            $select_query = "SELECT url_rewrite_id FROM url_rewrite WHERE request_path='".$request_path."';";
            //$overall_query = $update_query . " OR " . $insert_query . ";";
            $new_option_id = 0;
            $result = mysqli_query($connect, $select_query);
            if ($result->num_rows === 0) {
              mysqli_query($connect, $insert_query);
              $new_option_id = mysqli_insert_id($connect);
            } else {
              mysqli_query($connect, $update_query);
              $uw = $result->fetch_assoc();
              $new_option_id = $uw['url_rewrite_id'];
            }
			
            $row_strings .= $new_option_id.", ";
            //$row_strings = $overall_query;
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