<?php  

//connect to the database 
$connect = mysqli_connect("localhost","ars_dbroot","American1","sm_market_temp"); 
//mysql_select_db("sm_market_temp",$connect); //select the table 
// 

if ($_FILES['csv']['size'] > 0) { 

    //get the csv file 
    $file = $_FILES['csv']['tmp_name']; 
    $handle = fopen($file,"r"); 
    $row_strings = "";
    $tmp_query = "";
     
    //loop through the csv file and insert into database 
    do { 
        if ($data[0]) { 
            //$row_strings .= "<BR>".$data[0];
            $tmp_query = "'".addslashes($data[0])."'";
            $tmp_query .= ",'".addslashes($data[1])."'";
            $tmp_query .= ",'".addslashes($data[2])."'";
            mysqli_query($connect, "INSERT INTO contacts (contact_first, contact_last, contact_email) VALUES(".$tmp_query.")"); 
            $row_strings .= "<BR>".$tmp_query;
        } 
    } while ($data = fgetcsv($handle,1000,",","'")); 
    // 

    //redirect 
    header('Location: import_example.php?success=1&row_strings='.$row_strings); die; 

} 

?> 

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<title>Import a CSV File with PHP & MySQL</title> 
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