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
 * @example onpremise/userAgentClientHints-Web.php
 *
 * @include{doc} example-web-integration-client-hints.txt
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/onpremise/userAgentClientHints-Web.php).

 * In this example we create an on premise 51Degrees device detection pipeline, 
 * in order to do this you will need a copy of the 51Degrees on-premise library 
 * and need to make the following additions to your php.ini file
 * 
 * ```
 * FiftyOneDegreesHashEngine.data_file = // location of the data file
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
 * ```
 * User Agent Client Hints Example
 * 
 * Hardware Vendor: Unknown
 * Hardware Name: Array
 * Device Type: Desktop
 * 
 * ```
 * 
 **/

require(__DIR__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\core\Utils;

$device = new DeviceDetectionOnPremise();

$builder = new PipelineBuilder(array());

$pipeline = $builder->add($device)->build();

// We create the flowData object that is used to add evidence to and read
// data from 
$flowData = $pipeline->createFlowData();

// We set headers, cookies and more information from the web request
$flowData->evidence->setFromWebRequest();

// Now we process the flowData
$flowData->process();

$device = $flowData->device;

// Some browsers require that extra HTTP headers are explicitly
// requested. So set whatever headers are required by the browser in
// order to return the evidence needed by the pipeline.
// More info on this can be found at
// https://51degrees.com/blog/user-agent-client-hints
Utils::setResponseHeader($flowData);

// Generate the HTML
echo "<h2>User Agent Client Hints Example</h2>";

echo "

    <p>
    By default, the user-agent, sec-ch-ua and sec-ch-ua-mobile HTTP headers
    are sent.
    <br />
    This means that on the first request, the server can determine the
    browser from sec-ch-ua while other details must be derived from the
    user-agent.
    <br />
    If the server determines that the browser supports client hints, then
    it may request additional client hints headers by setting the
    Accept-CH header in the response.
    <br />
    Select the <strong>Make second request</strong> button below,
    to use send another request to the server. This time, any
    additional client hints headers that have been requested
    will be included.
    </p>

<button type='button' onclick='redirect()'>Make second request</button>

<script>

    // This script will run when button will be clicked and device detection request will again 
    // be sent to the server with all additional client hints that was requested in the previous
    // response by the server.
    // Following sequence will be followed.
    // 1. User will send the first request to the web server for detection.
    // 2. Web Server will return the properties in response based on the headers sent in the request. Along 
    // with the properties, it will also send a new header field Accept-CH in response indicating the additional
    // evidence it needs. It builds the new response header using SetHeader[Component name]Accept-CH properties 
    // where Component Name is the name of the component for which properties are required.
    // 3. When \"Make second request\" button will be clicked, device detection request will again 
    // be sent to the server with all additional client hints that was requested in the previous
    // response by the server.
    // 4. Web Server will return the properties based on the new User Agent CLient Hint headers 
    // being used as evidence.

    function redirect() {
        sessionStorage.reloadAfterPageLoad = true;
        window.location.reload(true);
        }

    window.onload = function () { 
        if ( sessionStorage.reloadAfterPageLoad ) {
        document.getElementById('description').innerHTML = '<p>The information shown below is determined using <strong>User Agent Client Hints</strong> that was sent in the request to obtain additional evidence. If no additional information appears then it may indicate an external problem such as <strong>User Agent Client Hints</strong> being disabled in your browser.</p>';
        sessionStorage.reloadAfterPageLoad = false;
        }
        else{
        document.getElementById('description').innerHTML = '<p>The following values are determined by sever-side device detection on the first request.</p>';
        }
    }

</script>

<div id=\"evidence\">
    <strong></br>Evidence values used: </strong>
    <table>
        <tr>
            <th>Key</th>
            <th>Value</th>
        </tr>";

$evidences = $pipeline->getElement("device")->filterEvidence($flowData);
foreach( $evidences as $key => $value){
           echo"<tr><td>" . strVal($key) . "</td>";
           echo "<td>" . strVal($value) . "</td></>";
}

echo "</table>";
echo "</div>";

echo "<div id=description></div>";
echo "</br><strong>Detection results:</strong></br>";
echo "<div id=\"content\">";
echo "<p>\n";
echo "    Hardware Vendor: " . (isset($device->hardwarevendor) && $device->hardwarevendor->hasValue ? $device->hardwarevendor->value : "Unknown (" . (isset($device->hardwarevendor) ? $device->hardwarevendor->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Hardware Name: " . (isset($device->hardwarename) && $device->hardwarename->hasValue ? implode(",", $device->hardwarename->value) : "Unknown (" . (isset($device->hardwarename) ? $device->hardwarename->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Device Type: " . (isset($device->devicetype) && $device->devicetype->hasValue ? $device->devicetype->value  : "Unknown (" . (isset($device->devicetype) ? $device->devicetype->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Platform Vendor: " . (isset($device->platformvendor) && $device->platformvendor->hasValue ? $device->platformvendor->value : "Unknown (" . (isset($device->platformvendor) ? $device->platformvendor->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Platform Name: " . (isset($device->platformname) && $device->platformname->hasValue ? $device->platformname->value : "Unknown (" . (isset($device->platformname) ? $device->platformname->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Platform Version: " . (isset($device->platformversion) && $device->platformversion->hasValue ? $device->platformversion->value : "Unknown (" . (isset($device->platformversion) ? $device->platformversion->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Browser Vendor: " . (isset($device->browservendor) && $device->browservendor->hasValue ? $device->browservendor->value : "Unknown (" . (isset($device->browservendor) ? $device->browservendor->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Browser Name: " . (isset($device->browsername) && $device->browsername->hasValue ? $device->browsername->value : "Unknown (" . (isset($device->browsername) ? $device->browsername->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Browser Version: " . (isset($device->browserversion) && $device->browserversion->hasValue ? $device->browserversion->value : "Unknown (" . (isset($device->browserversion) ? $device->browserversion->noValueMessage : "property unavailable" ) .")") . "\n";
