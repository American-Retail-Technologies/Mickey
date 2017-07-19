<?php
//https://stackoverflow.com/questions/5004233/jquery-ajax-post-example-with-php?noredirect=1&lq=1

$connect = mysqli_connect("ars-mysql.crymzjqricqv.us-west-2.rds.amazonaws.com","ars_dbroot","American1","ars_staging_magento1");
//$customers = "SELECT * FROM customer_entity ORDER BY created_at DESC";
if($_POST["fromDate"]){
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	$customers = "SELECT email, firstname, lastname FROM `customer_entity` WHERE `created_at` >= '".$_POST["fromDate"]."' ORDER BY `created_at` DESC";
	//echo $customers;
	
	$queryResult = mysqli_query($connect, $customers);
	//$result = $queryResult->fetch_all();
	while($result = $queryResult->fetch_row()){
		echo json_encode($result);
	}
	
	// Free result set
	mysqli_free_result($result);
	mysqli_close($connect);
	
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

<form action="" enctype="multipart/form-data" name="form2" id="form2">
<input type="checkbox" name="emails" checked="checked">tenzin@americanretailsupply</input>
</form>

<div id="response"></div>
<script>
$("#form1").submit(function(event) {
	/* Stop form from submitting normally */
    event.preventDefault();
	
	var ajaxRequest;
	
	/* Get from elements values */
	var values = $(this).serialize();
	
	ajaxRequest= $.ajax({
		type: "post",
		data: values
    });
	
	ajaxRequest.done(function (response, textStatus, jqXHR){
		// show successfully for submit message
		console.log(response);
		
		//$("#form2").html('<input type="checkbox" name="">' + response + '</li>');
    });
	
	/* On failure of request this function will be called  */
     ajaxRequest.fail(function (){
		// show error
		$("#response").html('There is error while submit');
     });
});
</script>
</body>
</html>
