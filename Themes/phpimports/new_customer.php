<?php
//https://stackoverflow.com/questions/5004233/jquery-ajax-post-example-with-php?noredirect=1&lq=1

$connect = mysqli_connect("ars-mysql.crymzjqricqv.us-west-2.rds.amazonaws.com","ars_dbroot","American1","ars_staging_magento1");
//$customers = "SELECT * FROM customer_entity ORDER BY created_at DESC";
if($_POST["fromDate"]){
	$customers = "SELECT email FROM customer_entity where created_at LIKE '%2017-06%' ORDER BY created_at DESC;";

	$result = mysqli_query($connect, $customers);
	
	echo json_encode($result);
	
	header('Location: import_custom_url_rewrite.php?success=1');
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Display List of Customers</title>
</head>

<body>


<form action="" enctype="multipart/form-data" name="form1" id="form1">
  <label for="fromDate">Pick Date: </label>
  <input name="fromDate" type="date" />
  <input type="submit" name="Submit" value="Submit" />
</form>
<?php if (!empty($_GET[success])) {?>
<h1>Success!</h1>
<?php } ?>

<div id="result"> </div>

<script>
$("#form1").submit(function(event) {
	/* Stop form from submitting normally */
    event.preventDefault();
	
	var ajaxRequest;
	
	/* Get from elements values */
	var values = $(this).serialize();
	
	ajaxRequest= $.ajax({
		url: "https://staging.americanretailsupply.com/pub/phpimports/new_customer.php",
		type: "post",
		data: values
    });
	
	ajaxRequest.done(function (response, textStatus, jqXHR){
		// show successfully for submit message
		$("#result").html('Submitted successfully');
    });
	
	/* On failure of request this function will be called  */
     ajaxRequest.fail(function (){
		// show error
		$("#result").html('There is error while submit');
     });
}
</script>
</body>
</html>
