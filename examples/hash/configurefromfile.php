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
 * @example hash/configureFromFile.php
 * This example shows how to configure a pipeline from a configuration file
 * using the pipelinebuilder's buildFromConfig method.
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/hash/configurefromfile.php).
 * 
 * In order to do this you will need a copy of the 51Degrees on-premise library 
 * and need to make the following additions to your php.ini file
 * 
 * ```
 * FiftyOneDegreesHashEngine.data_file = // location of the data file
 * ```
 *
 * ```
 * {
 *  "PipelineOptions": {
 *    "Elements": [
 *      {
 *        "BuilderName": "fiftyone\\pipeline\\devicedetection\\DeviceDetectionOnPremise"
 *      }
 *    ]
 *  }
 *}
 *
 * ```
 *
 * ```
 * Expected output:
 * Is User-Agent 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36' a mobile device?:
 * No
 * 
 * Is User-Agent 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114' a mobile device?:
 * Yes
 * ```
 */ 

require(__DIR__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\core\PipelineBuilder;

// We create a pipeline to access the engine via a JSON configuration file
$builder = new PipelineBuilder();

$configFile = __DIR__ . "/pipeline.json";

if (!file_exists($configFile)) {
    echo "Config file not found at " . $configFile;
    return;
}

// Check if config file resource key has been filled in

$configFileJSON = json_decode(file_get_contents($configFile), true);

if ($configFileJSON["PipelineOptions"]["Elements"][0]["BuildParameters"]["resourceKey"] === "!!YOUR_RESOURCE_KEY!!") {
    echo "You need to create a resource key at " .
    "https://configure.51degrees.com and paste it into your config file, " .
    "replacing !!YOUR_RESOURCE_KEY!!.";
    echo "\n<br/>";
    echo "Make sure to include the ismobile property " .
    "as it is used by this example.\n<br />";
    return;
}

$pipeline = $builder->buildFromConfig($configFile);

// Next we build the pipeline. We could additionally add extra engines and/or
// flowElements here before building.

// To stop having to construct the pipeline
// and re-make cloud API requests used during construction on every page load,
// we recommend caching the serialized pipeline to a database or disk.
// Below we are using PHP's serialize and writing to a file if it doesn't exist

$serializedPipelineFile = __DIR__ . "configure_from_file_pipeline.pipeline";
if(!file_exists($serializedPipelineFile)){
    $pipeline = $builder->build();
    file_put_contents($serializedPipelineFile, serialize($pipeline));
} else {
    $pipeline = unserialize(file_get_contents($serializedPipelineFile));
}

// We make a function for checking if a User-Agent is a mobile device

function configurefromfile_checkifmobile($pipeline, $userAgent = "")
{

    // We create the flowData object that is used to add evidence to and read data from
    $flowData = $pipeline->createFlowData();

    // Add the User-Agent as evidence
    $flowData->evidence->set("header.user-agent", $userAgent);

    // Now we process the flowData
    $result = $flowData->process();

    // First we check if the property we're looking for has a meaningful result
    print("Is User-Agent '<b>" . $userAgent . "</b>' a mobile device?:");
    print("</br>\n");

    if ($result->device->ismobile->hasValue) {
        if ($result->device->ismobile->value) {
            print("Yes");
        } else {
            print("No");
        }
    } else {
        // If it doesn't have a meaningful result, we echo out the reason why
        // it wasn't meaningful
        print($result->device->ismobile->noValueMessage);
    }

    print("</br>\n");
    print("</br>\n");
}

$desktopUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36';
$iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';

configurefromfile_checkifmobile($pipeline, $desktopUA);
configurefromfile_checkifmobile($pipeline, $iPhoneUA);
