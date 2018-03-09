<?php

//Upload image and convert into any size

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	$to      = 'tenzin@americanretailsupply.com';
	$subject = 'Test PHP Email';
	$message = 'Test PHP Email';
	$headers = 'From: tenzin@americanretailsupply.com' . "\r\n" .
		'Reply-To: tenzin@americanretailsupply.com' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	$status = mail($to, $subject, $message, $headers) ? "mail was successfully accepted for delivery":"mail was not successfully accepted for delivery";
	
	//redirect 
    header('Location: send_mail.php?success=1&status='.$status); die; 
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<title>Send Simple Email</title> 
</head> 

<body> 

<?php if (!empty($_GET[success])) { echo "<b>Your Email has been submitted.</b><br><br>Status of Email:".$_GET[status]."<br>"; } //generic success notice ?> 

<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1"> 
  <input type="submit" name="Submit" value="Submit" /> 
</form> 

</body> 
</html> 