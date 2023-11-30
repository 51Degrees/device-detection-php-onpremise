<?php
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
 * @example onpremise/manualDataUpdate.php
 *
 * This example shows how to get the device detection engine to refresh
 * its internal data structures when a new data file is available.
 *
 * This can be done asynchronously, without the need to restart the machine.
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/onpremise/manualDataUpdate.php).
 *
 * In this example we create an on premise 51Degrees device detection pipeline.
 * In order to do this you will need a copy of the 51Degrees on-premise library
 * and need to make the following additions to your php.ini file
 *
 * ```
 * FiftyOneDegreesHashEngine.data_file = // location of the data file
 * ```
 *
 * When running under process manager such as Apache MPM or php-fpm, make sure
 * to set performance_profile to MaxPerformance by making the following addition
 * to php.ini. More details can be found in <a href="https://github.com/51Degrees/device-detection-php-onpremise/blob/master/readme.md">README</a>.
 *
 * ```
 * FiftyOneDegreesHashEngine.performance_profile = MaxPerformance
 * ```
 *
 * Under process managers, refreshing internal data will not work as it is required
 * to be performed on both main process and child processes. There is not a proven
 * solution to do so yet, so we recommend a full server restart to be performed instead.
 *
 * Expected output:
 *
 * ```
 * Is User-Agent 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114' a mobile?
 * true
 * Is User-Agent 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36' a mobile?
 * false
 * Reloading data file...
 * Is User-Agent 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114' a mobile?
 * true
 * Is User-Agent 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36' a mobile?
 * false
 * ```
 */

require __DIR__ . '/../../vendor/autoload.php';

use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;

$deviceEngine = new DeviceDetectionOnPremise();

$pipeline = new PipelineBuilder();

$pipeline = $pipeline->add($deviceEngine)->build();

// Here we create a function that checks if a supplied User-Agent is a mobile device
function manualDataUpdate_checkifmobile($pipeline, $userAgent = '')
{
    // We create the flowData object that is used to add evidence to and read data from
    $flowData = $pipeline->createFlowData();

    // Add the User-Agent as evidence

    $flowData->evidence->set('header.user-agent', $userAgent);

    // Now we process the flowData
    $result = $flowData->process();

    // First we check if the property we're looking for has a meaningful result

    echo "Is User-Agent '<b>" . $userAgent . "</b>' a mobile device?:";
    echo "</br>\n";

    if ($result->device->ismobile->hasValue) {
        if ($result->device->ismobile->value) {
            echo 'Yes';
        } else {
            echo 'No';
        }
    } else {
        // If it doesn't have a meaningful result, we echo out the reason why
        // it wasn't meaningful
        echo $result->device->ismobile->noValueMessage;
    }

    echo "</br>\n";
    echo "</br>\n";
}

// Some example User-Agents to test

$desktopUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36';
$iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';

manualDataUpdate_checkifmobile($pipeline, $desktopUA);
manualDataUpdate_checkifmobile($pipeline, $iPhoneUA);
echo 'Reloading data file...';
// Update the device detection engine with the data file from the
// same location.
// In this example, we haven't actually copied a new data file in,
// so this will have no effect.
// In practice, this function should be called after the data file
// on disk has been overwritten with a newer one.
// There are variations, which allow refreshing from a different
// file path or from an in-memory representation of the data file.
$deviceEngine->refreshData();
manualDataUpdate_checkifmobile($pipeline, $desktopUA);
manualDataUpdate_checkifmobile($pipeline, $iPhoneUA);
