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
 * @example hash/matchMetrics.php
 * 
 * @include{doc} example-match-metrics-hash.txt
 * 
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/hash/matchMetrics.php).
 * 
 * @include{doc} example-require-datafile.txt
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
 * User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36
 * Matched User-Agent:
 * Id: 15364-38914-97847-0
 * Difference: 0
 * Drift: 0
 * Method: 0
 * 
 * 
 * User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114
 * Matched User-Agent:
 * Id: 12280-81243-82102-0
 * Difference: 0
 * Drift: 0
 * Method: 0
 * ```
 */

require(__dir__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\core\PipelineBuilder;

$device = new DeviceDetectionOnPremise(array(
    // Prefer low memory profile where all data streamed from disk 
    // on-demand. Experiment with other profiles.
    "performanceProfile" => "LowMemory",
    // Only use the predictive graph to better handle variances 
    // between the training data and the target User-Agent string.
    // For a more detailed description of the differences between
    // performance and predictive, see 
    // <a href="https://51degrees.com/documentation/4.1/_device_detection__hash.html#DeviceDetection_Hash_DataSetProduction_Performance">Hash Algorithm</a>
    "usePredictiveGraph" => true,
    "usePerformanceGraph" => false
));

$pipeline = new PipelineBuilder();

$pipeline = $pipeline->add($device)->build();


function check_metrics($userAgent, $pipeline){

    // We create the flowData object that is used to add evidence to and 
    // read data from 
    $flowData = $pipeline->createFlowData();

    // We set the User-Agent
    $flowData->evidence->set("header.user-agent", $userAgent);

    // Now we process the flowData
    $result = $flowData->process();
    $device = $result->device;
    
    echo "User-Agent:         " . $userAgent . "</br>\n";
    // Obtain the matched User-Agent: the matched substrings in the
    // User-Agent separated with underscored.
    echo "Matched User-Agent: " .
        $device->useragents->value[0] . "</br>\n";
    // Obtains the matched Device ID: the IDs of the matched profiles
    // separated with hyphens. Notice how the value changes depending
    // on the properties that are used with the builder. Profile IDs are
    // replaced with zeros when there are no properties associated with
    // the corresponding component available.
    echo "Id: " . $device->deviceId->value . "</br>\n";
    // Obtain difference: The total difference in hash code values
    // between the matched substrings and the actual substrings. The
    // maximum difference to allow when finding a match can be set
    // through the configuration structure.
    echo "Difference: " . $device->difference->value . "</br>\n";
    // Obtain drift: The maximum drift for a matched substring from the
    // character position where it was expected to be found. The maximum
    // drift to allow when finding a match can be set through the
    // configuration structure.
    echo "Drift: " . $device->drift->value . "</br>\n";
    // Output the method that was used to obtain the result. Play with
    // the setUsePredictiveGraph and setUsePerformanceGraph values to
    // see the different results.
    echo "Method: " . $device->method->value . "</br>\n";
    
    echo "</br>\n";
    echo "</br>\n";

}


// Some example User-Agents to test

$desktopUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36';
$iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';

// Run the function multiple times, creating a new flowData from the pipeline 
// each time
check_metrics($desktopUA, $pipeline);
check_metrics($iPhoneUA, $pipeline);
