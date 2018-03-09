<?php

//Upload image and convert into any size

if ($_FILES['csv']['size'] > 0) {
	
	//file on server
	$tempFilePath = $_FILES['csv']['tmp_name'];
    $fileName = $_FILES['csv']['name'];
	
	$size = "100x100";
	
	$convertedPath = "/opt/bitnami/apps/magento/htdocs/pub/art-page/resized_image/";
	$convertedFilePath = $convertedPath . $fileName;
	
	$cmd = "convert $tempFilePath -resize $size $convertedFilePath";
	exec($cmd);
	//redirect 
    header('Location: convert_image.php?success=1&command='.$cmd); die; 
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<title>Import URL Rewrite (no header in the file)</title> 
</head> 

<body> 

<?php if (!empty($_GET[success])) { echo "<b>Your file has been imported.</b><br>".$_GET[command]."<br>"; } //generic success notice ?> 

<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1"> 
  Choose your file: <br /> 
  <input name="csv" type="file" id="csv" /> 
  <input type="submit" name="Submit" value="Submit" /> 
</form> 

</body> 
</html> 