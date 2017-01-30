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
    // Grab the sitemap URL from the CLI and verify it looks like a URL
    //--------------------------------------------------------------------------------
    if(filter_var($sSitemapUrl, FILTER_VALIDATE_URL) === false) {
        die("$sSitemapUrl is not a valid URL" . PHP_EOL);
    }
    //--------------------------------------------------------------------------------
    // Try downloading the sitemap file
    //--------------------------------------------------------------------------------
    $sSitemapXml = file_get_contents($sSitemapUrl);
    if(!$sSitemapXml) {
        die('Unable to download the sitemap file at $sSitemapUrl' . PHP_EOL);
    }
    //--------------------------------------------------------------------------------
    // Try to parse the sitemap file via Simple XML
    //--------------------------------------------------------------------------------
    try {
        $oSitemap = new SimpleXMLElement($sSitemapXml);
    } catch(Exception $e) {
        die('Failed to parse the sitemap file' . PHP_EOL . $e->getMessage() . PHP_EOL);
    }
    //--------------------------------------------------------------------------------
    // Extract the list of URLs from the sitemap that we intend to crawl
    //--------------------------------------------------------------------------------
    $aDocNamespaces = $oSitemap->getDocNamespaces();
    $sXmlns         = array_shift($aDocNamespaces);
    $oSitemap->registerXPathNamespace('sitemap', $sXmlns);
    $aMatches = $oSitemap->xpath("//sitemap:loc");
    $iNumUrls = count($aMatches);
    //--------------------------------------------------------------------------------
    // Truncate the list of URLs to 10 if we're in test mode
    //--------------------------------------------------------------------------------
    if($bTestOnly === true && $iNumUrls > 10) {
        $aMatches = array_random($aMatches, 10);
        $iNumUrls = 10;
    }
    //--------------------------------------------------------------------------------
    // Download the URLs, timing each one
    //--------------------------------------------------------------------------------
    if($bTestOnly) {
        echo "Testing with $iNumUrls URLs" . PHP_EOL;
    } else {
        echo 'Found ' . $iNumUrls . ' URLs to crawl' . PHP_EOL;
    }
    $iTotalDownloadTime = 0;
    foreach($aMatches as $i => $sUrl) {
        $iPageStartTime = microtime(true);
        $iCur = $i + 1;
        echo "($iCur/$iNumUrls) - Fetching " . $sUrl . PHP_EOL;
        file_get_contents($sUrl);
    
        $iTotalDownloadTime += microtime(true) - $iPageStartTime;
        // Sleep between requests if we're told to
        sleep($iDelay);
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
    echo 'Average page time (in milliseconds): ' . $iTotalDownloadTime * 1000 / $iNumUrls . PHP_EOL;
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
    echo 'wfpc - Magento full page cache warmer' . PHP_EOL;
    echo '-------------------------------------' . PHP_EOL . PHP_EOL;
    echo 'wfpc <-h|-t|-w> [-d=delay] <sitemap url>' . PHP_EOL . PHP_EOL;
    echo 'Run help to see the usage options                       : wfpc -h' . PHP_EOL;
    echo 'Run wfpc in test mode to get an idea of page performance: wfpc -t <sitemap url>' . PHP_EOL;
    echo 'Warm the Magento cache                                  : wfpc -w [-d=delay] <sitemap url>' . PHP_EOL . PHP_EOL;
    echo 'The last form of the command allows a -d option to place a pause of X number of seconds between request' . PHP_EOL;
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