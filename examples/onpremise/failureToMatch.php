/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2023 51 Degrees Mobile Experts Limited, Davidson House,
 * Forbury Square, Reading, Berkshire, United Kingdom RG1 3EU.
 *
 * This Original Work is licensed under the European Union Public Licence
 * (EUPL) v.1.2 and is subject to its terms as set out below.
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
 * @example onpremise/failureToMatch.php
 * 
 * This example shows how the hasValue function can help make sure 
 * that meaningful values are returned when checking properties 
 * returned from the device detection engine.
 * 
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/onpremise/failureToMatch.php).
 * 
 * In this example we create an on premise 51Degrees device detection pipeline, 
 * in order to do this you will need a copy of the 51Degrees on-premise library 
 * and need to make the following additions to your php.ini file
 *
 * ```
 * FiftyOneDegreesHashEngine.data_file = // location of the data file
 * FiftyOneDegreesHashEngine.allow_unmatched = false
 * ```
 * 
 * When running under process manager such as Apache MPM or php-fpm, make sure
 * to set performance_profile to MaxPerformance by making the following addition
 * to php.ini file. More details can be found in <a href="https://github.com/51Degrees/device-detection-php-onpremise/blob/master/readme.md">README</a>.
 * 
 * ```
 * FiftyOneDegreesHashEngine.performance_profile = MaxPerformance
 * ```
 * 
 * Expected output:
 * 
 * ```
 * Does User-Agent 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114' represent a mobile device?:
 * Yes
 *
 * Does User-Agent 'nonsense ua...' represent a mobile device?:
 * We don't know for sure. The reason given is:
 * The results contained a null profile for the component which the required property belongs to.
 * ```
 */

// First we include the deviceDetectionPipelineBuilder


require(__dir__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\core\PipelineBuilder;

$device = new DeviceDetectionOnPremise();

$pipeline = new PipelineBuilder();

$pipeline = $pipeline->add($device)->build();


// Here we create a function that checks if a supplied User-Agent is a 
// mobile device

function failuretomatch_checkifmobile($userAgent = "", $pipeline) {

    // We create the flowData object that is used to add evidence to and read data from 
    $flowData = $pipeline->createFlowData();

    // Add the User-Agent as evidence

    $flowData->evidence->set("header.user-agent", $userAgent);

    // Now we process the flowData
    $result = $flowData->process();

    // First we check if the property we're looking for has a meaningful result

    print("Does User-Agent '<b>" . $userAgent . "</b>' represent a mobile device?:");
    print("</br>\n");

    if($result->device->ismobile->hasValue){

        if($result->device->ismobile->value){
            print("Yes");
        } else {
            print("No");
        }

    } else {

        print("We don't know for sure. The reason given is:");
        print("</br>\n");
        // If it doesn't have a meaningful result, we echo out the reason why 
        // it wasn't meaningful
        print($result->device->ismobile->noValueMessage);
        print("</br>\n");

    }

    print("</br>\n");
    print("</br>\n");

}


// Some example User-Agents to test

$iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';
failuretomatch_checkifmobile($iPhoneUA, $pipeline);

$badUserAgent = 'nonsense ua...';
failuretomatch_checkifmobile($badUserAgent, $pipeline);
