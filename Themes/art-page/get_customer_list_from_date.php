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