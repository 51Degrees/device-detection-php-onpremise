<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2019 51 Degrees Mobile Experts Limited, 5 Charlotte Close,
 * Caversham, Reading, Berkshire, United Kingdom RG4 7BY.
 *
 * This Original Work is licensed under the European Union Public Licence (EUPL) 
 * v.1.2 and is subject to its terms as set out below.
 *
 * If a copy of the EUPL was not distributed with this file, You can obtain
 * one at https://opensource.org/licenses/EUPL-1.2.
 *
 * The 'Compatible Licences' set out in the Appendix to the EUPL (as may be
 * amended by the European Commission) shall be deemed incompatible for
 * the purposes of the Work and the provisions of the compatibility
 * clause in Article 5 of the EUPL shall not apply.
 * 
 * If using the Work as, or as part of, a network application, by 
 * including the attribution notice(s) required under Article 5 of the EUPL
 * in the end user terms of the application under an appropriate heading, 
 * such notice(s) shall fulfill the requirements of that article.
 * ********************************************************************* */

 /**
 * @example hash/userAgentClientHints.php
 * 
 * This example shows how a simple device detection pipeline that checks
 * if a User-Agent is a mobile device
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/hash/userAgentClientHints.php).

 * In this example we create an on premise 51Degrees device detection pipeline, 
 * in order to do this you will need a copy of the 51Degrees on-premise library 
 * and need to make the following additions to your php.ini file
 * 
 * ```
 * FiftyOneDegreesHashEngine.data_file = // location of the data file
 * ```
 * 
 * Expected output:
 * 
 * ```
 * ---------------------------------------
 * This example demonstrates detection using user-agent client hints.
 * The sec-ch-ua value can be used to determine the browser of the connecting device, but not other components such as the hardware.
 * We show this by first performing detection with sec-ch-ua only.
 * We then repeat with the user-agent header set as well. Note that the client hint takes priority over the user-agent.
 * Finally, we use both sec-ch-ua and user-agent.Note that sec-ch-ua takes priority over the user-agent for detection of the browser.
 * ---------------------------------------
 * Evidence Used:
 * Sec-CH-UA = '"Google Chrome";v="89", "Chromium";v="89", ";Not A Brand";v="99"'
 * User-Agent = 'NOT_SET'
 * Detection Results:
 *         Browser = Chrome 89
 *         IsMobile = No matching profiles could be found for the supplied evidence.A 'best guess' can be returned by configuring more lenient matching rules.See https://51degrees.com/documentation/_device_detection__features__false_positive_control.html
 *
 * Evidence Used:
 * Sec-CH-UA = 'NOT_SET'
 * User-Agent = 'Mozilla/5.0 (Linux; Android 9; SAMSUNG SM-G960U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/10.1 Chrome/71.0.3578.99 Mobile Safari/537.36'
 * Detection Results:
 *         Browser = Samsung Browser 10.1
 *         IsMobile = Yes
 *
 * Evidence Used:
 * Sec-CH-UA = '"Google Chrome";v="89", "Chromium";v="89", ";Not A Brand";v="99"'
 * User-Agent = 'Mozilla/5.0 (Linux; Android 9; SAMSUNG SM-G960U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/10.1 Chrome/71.0.3578.99 Mobile Safari/537.36'
 * Detection Results:
 *         Browser = Chrome 89
 *         IsMobile = Yes
 * ```
 *
 **/

require(__dir__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\core\PipelineBuilder;

echo "---------------------------------------</br>\n";
echo "This example demonstrates detection " .
                "using user-agent client hints.</br>\n";
echo "The sec-ch-ua value can be used to " .
                "determine the browser of the connecting device, " .
                "but not other components such as the hardware.</br>\n";
echo "We show this by first performing " .
                "detection with sec-ch-ua only.</br>\n";
echo "We then repeat with the user-agent " .
                "header set as well. Note that the client hint takes " .
                "priority over the user-agent.</br>\n";
echo "Finally, we use both sec-ch-ua and " .
                "user-agent. Note that sec-ch-ua takes priority " .
                "over the user-agent for detection of the browser.</br>\n";
echo "---------------------------------------</br></br>\n";

// First create the device detection pipeline with the desired settings.

$device = new DeviceDetectionOnPremise();

$pipeline = new PipelineBuilder();

$pipeline = $pipeline->add($device)->build();


// Define function to analyze user-agent/client hints

function analyzeClientHints($pipeline, $setUserAgent, $setSecChUa){

    $mobileUserAgent = "Mozilla/5.0 (Linux; Android 9; SAMSUNG SM-G960U) " . 
        "AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/10.1 " .
            "Chrome/71.0.3578.99 Mobile Safari/537.36";
    $secchuaValue = "\"Google Chrome\";v=\"89\", \"Chromium\";v=\"89\", \";Not A Brand\";v=\"99\"";

    // We create a FlowData object from the pipeline
    // this is used to add evidence to and then process  
    $flowData = $pipeline->createFlowData();

    // Add a value for the user-agent client hints header
    // sec-ch-ua as evidence 
    if ($setSecChUa){
        $flowData->evidence->set("query.sec-ch-ua", $secchuaValue);
    }
    // Also add a standard user-agent if requested 
   if ($setUserAgent){
        $flowData->evidence->set("query.user-agent", $mobileUserAgent);
    }

    // Now we process the flowData
    $result = $flowData->process();

    $device = $result->device;

    $browserName = $device->browsername;
    $browserVersion = $device->browserversion;
    $ismobile = $device->ismobile;

    // Output evidence
    echo "<strong>Evidence Used: </strong></br></n>";
    $secchua = "NOT_SET";
    if ($setSecChUa){
        $secchua = $secchuaValue;
    }
    echo sprintf("Sec-CH-UA = '%s'</br>\n", $secchua);

    $ua = "NOT_SET";
    if ($setUserAgent){
       $ua = $mobileUserAgent;
    }
    echo sprintf("User-Agent = '%s'</br>\n", $ua);


    // Output the Browser
    echo "<strong>Detection Results: </strong></br></n>";
    if ($browserName->hasValue && $browserVersion->hasValue){
        echo sprintf("Browser = %s %s</br>\n", $browserName->value, $browserVersion->value);
    }
    else if ($browserName->hasValue){
        echo sprintf("Browser = %s (version unknown)</br>\n", $browserName->value);
    }
    else{
        echo sprintf("Browser = %s</br>\n", $browserName->noValueMessage);
    }

    // Output the value of the 'IsMobile' property.
    if ($ismobile->hasValue){
        if($ismobile->value){
            $value = "Yes";
        } else {
            $value = "No";
        }
        echo sprintf("IsMobile = %s</br></br>\n", $value);
    }
    else{
        echo sprintf("IsMobile = %s</br></br>\n", $ismobile->noValueMessage);
    }
}

// first try with just sec-ch-ua.
analyzeClientHints($pipeline, false, true);

// Now with just user-agent.
analyzeClientHints($pipeline, true, false);

// Finally, perform detection with both.
analyzeClientHints($pipeline, true, true);

