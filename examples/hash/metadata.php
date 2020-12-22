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
 * @example hash/metadata.php
 * This example shows how to get properties from a pipeline's processed flowData based on * their metadata and the getProperties() method.
 * 
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/hash/metadata.php).
 * 
 * In this example we create an on premise 51Degrees device detection pipeline, 
 * in order to do this you will need a copy of the 51Degrees on-premise library 
 * and need to make the following additions to your php.ini file
 *
 * ```
 * FiftyOneDegreesHashEngine.data_file = // location of the data file
 * ```
 * 
 * Expected output
 * 
 * ```
 * [list of properties]
 * 
 * Checking support for User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36
 * svg : No
 * video : No
 * supportstls/ssl : No
 * supportswebgl : No
 * 
 * Checking support for User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114
 * svg : Yes
 * video : Yes
 * supportstls/ssl : Yes
 * supportswebgl : Yes
 * ```
 */

require(__dir__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\core\PipelineBuilder;

$device = new DeviceDetectionOnPremise();

$pipeline = new PipelineBuilder();

$pipeline = $pipeline->add($device)->build();


// Show all the properties in the device element

foreach($pipeline->getElement("device")->properties as $property){

    echo("<b>" . $property["name"] . "</b> (" . $property["type"] . ") - " . $property["category"] . " - " . $property["description"]);
    echo "</br>\n";
    echo "</br>\n";

};

function check_browser_support($userAgent, $pipeline){

    // We create the flowData object that is used to add evidence to and 
    // read data from 
    $flowData = $pipeline->createFlowData();

    // We set the User-Agent
    $flowData->evidence->set("header.user-agent", $userAgent);

    // Now we process the flowData
    $result = $flowData->process();

    // We use getWhere to find all the properties of a certain category 
    // and fetch their values
    $supported = $flowData->getWhere("category", "Supported Media");

    echo "Checking support for User-Agent: '<b>" . $userAgent . "</b>'"; 

    echo "</br>\n";

    foreach ($supported as $key => $value){

        echo $key . " : ";

        // First we check if the property we're looking for has a meaningful 
        // result (see the failureToMatch example for more information)

        if($result->device->ismobile->hasValue){

            if($result->device->ismobile->value){

                print("Yes");

            } else {

                print("No");

            }

        } else {

            // If it doesn't have a meaningful result, we echo out the 
            // reason why it wasn't meaningful
            print($result->device->ismobile->noValueMessage);

        }

        echo "</br>\n";

    }
    
    echo "</br>\n";
    echo "</br>\n";

}


// Some example User-Agents to test

$desktopUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36';
$iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';

// Run the function multiple times, creating a new flowData from the pipeline 
// each time
check_browser_support($desktopUA, $pipeline);
check_browser_support($iPhoneUA, $pipeline);
