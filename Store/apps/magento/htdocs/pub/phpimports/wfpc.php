<?php
// Based on: https://github.com/quickshiftin/wfpc
/**
 * Warm a Magento Full Page Cache.
 * This assumes you have an FPC installed on the target site.
 * If you don't all it will do is create undue load on your server.
 * All the script does is download your sitemap.xml file and hit every url in the list.
 * 
 * In reality this script will work on any site that provides a sitemap.xml file.
 * Please don't use the script to attack websites! Frankly, it won't even work well as
 * a DOS attack, because it executes each request synchronously.
 *
 * Copyright Nathan Nobbe 2015.
 */
if($argc < 2) {
    usage();
}
$sMode = $argv[1];
switch($argv[1]) {
    case '-h' : usage(); break;
    case '-t' :
        if($argc > 3) {
            usage();
        }
        run($argv[2], 0, true);
        break;
    case '-w':
        if($argc < 3 || $argc > 4) {
            usage();
        }
        if($argc == 3) {
            run($argv[2]);
        }
        elseif($argc == 4) {
            list($option, $seconds) = explode('=', $argv[2]);
            $iDelay = 0;
            if($option != '-d' || (int)$seconds < 1) {
                echo 'Warning: Unrecognized option ' . $argv[3] . PHP_EOL;
            } else {
                $iDelay = $seconds;
            }
            run($argv[3], $iDelay);
        }
        break;
    default:
        usage();
}
/**
 * Run the tool across the given set of URLs.
 */
function run($sSitemapUrl, $iDelay=0, $bTestOnly=false)
{
    //--------------------------------------------------------------------------------
    // Try to parse the sitemap file via Simple XML
    //--------------------------------------------------------------------------------
	$parts = parse_url($sSitemapUrl);
	$strHostPath = $parts['scheme'].'://'.$parts['host']."/";
	$file = ".".$parts['path'];	
    try {
		$handle = fopen($file,"r");
    } catch(Exception $e) {
        die('Failed to open the URL file' . PHP_EOL . $e->getMessage() . PHP_EOL);
    }
    //--------------------------------------------------------------------------------
    // Download the URLs, timing each one
    //--------------------------------------------------------------------------------
    $iTotalDownloadTime = 0;
	$iCur = 0;
	while ($data = fgetcsv($handle,1000,",","'")) {
		if (isset($data[0])) {
			$sUrl = $strHostPath . $data[0];
			$iPageStartTime = microtime(true);
			$iCur++;
			echo "$iCur - Downloading: " . $sUrl ;
			file_get_contents($sUrl);
			$currentDownloadTime =  microtime(true) - $iPageStartTime;     
			echo " ($currentDownloadTime)" . PHP_EOL;
			$iTotalDownloadTime += $currentDownloadTime;
			// Sleep between requests if we're told to
			sleep($iDelay);
		}
		if ($bTestOnly === true && $iCur > 10) break;
    }
    //--------------------------------------------------------------------------------
    // Report the results
    //--------------------------------------------------------------------------------
    $sFinishType = 'warming';
    if($bTestOnly)
        $sFinishType = 'testing';
    echo "Finished $sFinishType your Magento site performance" . PHP_EOL;
    echo 'Total download time (in seconds)   : ' . $iTotalDownloadTime . PHP_EOL;
    echo 'Total download time (formatted)    : ' . format_milli($iTotalDownloadTime * 1000) . PHP_EOL;
    echo 'Average page time (in milliseconds): ' . $iTotalDownloadTime * 1000 / $iCur . PHP_EOL;
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
/**
 * Print the script usage and exit.
 */
function usage()
{
    echo '-------------------------------------' . PHP_EOL;
    echo 'wfpc - Magento full page cache warmer. e.g. php wfpc.php -w http://dev.americanretailsupply.com/a1.txt' . PHP_EOL;
    echo '-------------------------------------' . PHP_EOL . PHP_EOL;
    echo 'wfpc <-h|-t|-w> [-d=delay] <sitemap url>' . PHP_EOL . PHP_EOL;
    echo 'Run help to see the usage options                       : wfpc -h' . PHP_EOL;
    echo 'Run wfpc in test mode to get an idea of page performance: wfpc -t <"hosturl"/"csv in current dir">' . PHP_EOL;
    echo 'Warm the Magento cache                                  : wfpc -w [-d=delay] <path to csv file>' . PHP_EOL . PHP_EOL;
    echo 'The last form of the command allows a -d option to place a pause of X number of seconds between request' . PHP_EOL;
    echo 'Hint: To break up input file use: "sed -n -e \'51,100p\' cat-file.csv > cat-051-100.csv"' . PHP_EOL;
    exit();
}
function array_random($arr, $num = 1)
{
    shuffle($arr);
    $r = array();
    for ($i = 0; $i < $num; $i++) {
        $r[] = $arr[$i];
    }
    return $num == 1 ? $r[0] : $r;
}
?>
