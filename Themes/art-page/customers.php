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
		url: "https://staging.americanretailsupply.com/pub/art-page/get_customer_list_from_date.php",
		type: "post",
		data: values
    });
	
	ajaxRequest.done(function (response, textStatus, jqXHR){
		// show successfully for submit message
		console.log(response);
		response.split(']');
		$.each(response, function(index, value) { 
			console.log(index + ': ' + value);
		});
		/*
		$.each(response, function(index, value){
			$("#form2").html('<input type="checkbox" >' + value + '</input>');
		});
		*/
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
