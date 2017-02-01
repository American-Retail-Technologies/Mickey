<?php 

#####################################################
#Date:          11/23/16 
#Project:       Mickey
#Description:   Get Order Information for index 4
#####################################################

include "token.php";

$ch = curl_init("http://americanretailsupply.net/index.php/rest/V1/orders/4");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
 
$result = curl_exec($ch);
header('Content-Type: application/json'); 
echo json_encode($result);
