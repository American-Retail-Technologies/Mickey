<?php 
$result_string = "";
if (isset($_FILES['csv']) && $_FILES['csv']['size'] > 0) { 
  try {
  //get the csv file 
    $file = $_FILES['csv']['tmp_name']; 
    $handle = fopen($file,"r"); 
    $iCur = 0;
    $pageCount = 0;
    $iTotalDownloadTime = 0;
    $strHostPath = $_POST['host_path'];
    $iDelay = $_POST['page_delay'];
    $iFrom = $_POST['from_index'];
    $iTo = $_POST['to_index'];
    $sUrl = "";
    do {
      if (isset($data[0])) {
        $iCur++;
        if ($iCur < $iFrom) continue;
        $iPageStartTime = microtime(true);
        $sUrl = $strHostPath . $data[0];
        $result_string .= "$iCur - Fetching " . $sUrl . "<br/>";
        file_get_contents($sUrl);
        $iTotalDownloadTime += microtime(true) - $iPageStartTime;
        $pageCount++;
        // Sleep between requests if we're told to
        sleep($iDelay);
      }
    } while (($data = fgetcsv($handle,1000,",","'")) && ($iCur < $iTo)); 
    //--------------------------------------------------------------------------------
    // Report the results
    //--------------------------------------------------------------------------------
    $strStatus = "Finished warming your Magento cache with $pageCount pages from $iFrom to $iTo<br/>";
    $strStatus .= 'Total download time (in seconds)   : ' . $iTotalDownloadTime . '<br/>';
    $strStatus .= 'Total download time (formatted)    : ' . format_milli($iTotalDownloadTime * 1000) . '<br/>';
    $strStatus .= 'Average page time (in milliseconds): ' . $iTotalDownloadTime * 1000 / $pageCount . '<br/>';
    //$strStatus .= "iFrom=$iFrom, iTo=$iTo, host=$strHostPath, File=$file<br/>$result_string<br/>";    
    header('Location: cache_warmer.php?success=1&status_message='.$strStatus); die;
  }
  //catch exception
  catch(Exception $e) {
    echo 'Exception Message: ' .$e->getMessage();
  }
} 

/**
 * Format milliseconds nicely.
 */
function format_milli($ms)
{
    $ms = (int)$ms;
    return
        floor($ms/3600000) . ':' .                        // hours
        floor($ms/60000) . ':' .                          // minutes
        floor(($ms % 60000) / 1000) . '.' .               // seconds
        str_pad(floor($ms % 1000), 3, '0', STR_PAD_LEFT); // milliseconds
}

?> 

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<title>ARS Cache Warmer</title> 
</head> 

<body> 

<?php 
if (!empty($_GET['success'])) { 
echo "<b>Your files were downloaded</b><br>".$_GET['status_message']."<br>"; 
} //generic success notice 
else if (!empty($_GET['failure'])) { 
echo "<b>Your file download FAILED.</b><br>".$_GET['status_message']."<br>"; 
} //generic failure notice 
?> 

<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1"> 
  Website Base Path: <input type="text" name="host_path" value="http://test2.yoosh.mobi/"><br /> 
  Inter-page Delay (ms): <input type="text" name="page_delay" value="0"><br /> 
  From Index: <input type="text" name="from_index" value="1"><br /> 
  To Index: <input type="text" name="to_index" value="1"><br />
  Choose the file with URLs: <br /> 
  <input name="csv" type="file" id="csv" /> 
  <input type="submit" name="Submit" value="Submit" /> 
</form> 
<h2>Result:</h2><br />
<?php echo $result_string; ?>

</body> 
</html> 