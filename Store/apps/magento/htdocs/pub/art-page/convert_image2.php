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
	$fileName = $_FILES['csv']['name'];
	$fileSize = $_FILES['csv']['size'];
    $handle = fopen($file,"r"); 
    $row_strings = "$file $fileName $fileSize";
	
	$uploadfile = "/opt/bitnami/apps/magento/htdocs/pub/art-page/resized_image/".$fileName;
	if (move_uploaded_file($file, $uploadfile)) {
		$row_strings .= ". File is valid, and was successfully uploaded to $uploadfile.\n";
	} else {
		$row_strings .=  ". Possible file upload attack! $fileName\n";
	}

	
	fclose($handle);

    //redirect 
    header('Location: convert_image2.php?success=1&row_strings='.$row_strings); die; 

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