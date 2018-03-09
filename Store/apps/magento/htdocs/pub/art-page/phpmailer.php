<?php

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
//Load composer's autoloader
require '../../vendor/autoload.php';

	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions

	$status = "";
	try {
		//Server settings
		$mail->SMTPDebug = 2;                                 // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.office365.com';                   // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'tenzin@americanretailsupply.com';  // SMTP username
		$mail->Password = base64_decode("RHJhZ29uMTIzNAo=");  // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 25;                                     // TCP port to connect to

		//Recipients
		$mail->setFrom('tenzin@americanretailsupply.com', 'Mailer');
		$mail->addAddress('tkhando@pioneer-inc.com', 'Tenzin Gmail');     // Add a recipient
		//$mail->addAddress('ellen@example.com');               // Name is optional
		$mail->addReplyTo('tenzin@americanretailsupply.com', 'Tenzin ARS');
		//$mail->addCC('cc@example.com');
		//$mail->addBCC('bcc@example.com');

		//Attachments
		//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'Here is the subject';
		$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		$mail->send();
		$status = 'Message has been sent';
	} catch (Exception $e) {
		echo 'Message could not be sent.';
		$status = 'Mailer Error: ' . $mail->ErrorInfo;
	}

	header('Location: phpmailer.php?success=1&status='.$status); die; 
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<title>Send Simple Email using PHPMailer</title> 
</head> 

<body> 

<?php if (!empty($_GET[success])) { echo "<b>Your Email has been submitted.</b><br><br>Status of Email:".$_GET[status]."<br>"; } //generic success notice ?> 

<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1"> 
  <input type="submit" name="Submit" value="Submit" /> 
</form> 

</body> 
</html> 