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
 * @example hash/webIntegration.php
 * 
 * This example demonstrates the evidence.setFromWebRequest() method 
 * and client side JavaScript overrides by creating a web server, 
 * serving JavaScript created by the device detection engine and bundled together by 
 * a special JavaScript builder engine.
 * This JavaScript is then used on the client side to get a more accurate reading 
 * for properties by fetching the json response using the overrides as evidence.
 * 
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/hash/webintegration.php).
 * 
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
 * 
 * ```
 * Example


 * Hardware Vendor: Unknown
 * Hardware Name: Array
 * Device Type: Desktop
 * Platform Vendor: Ubuntu Foundation
 * Platform Name: Ubuntu
 * Platform Version: Unknown
 * Browser Vendor: Mozilla
 * Browser Name: Firefox
 * Browser Version: 77.0

 * Updated information from client-side evidence:
 * Hardware Name: Desktop,Emulator
 * Screen width (pixels): 1920
 * Screen height (pixels): 1080
 * ```
 */

require(__DIR__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\core\JavascriptBuilderElement;
use fiftyone\pipeline\core\JsonBundlerElement;
use fiftyone\pipeline\core\Utils;

$device = new DeviceDetectionOnPremise();

$builder = new PipelineBuilder(array(
    "javascriptBuilderSettings" => array("_endpoint" => "/?json")
));

$pipeline = $builder->add($device)->build();

// We create the flowData object that is used to add evidence to and read
// data from 
$flowData = $pipeline->createFlowData();

// We set headers, cookies and more information from the web request
$flowData->evidence->setFromWebRequest();

// Now we process the flowData
$result = $flowData->process();

// Check for the json endpoint and return the JSON
// This is used by the client side to get additional property data from the pipeline
// after populating client side evidence

if(isset($_GET["json"])){

    header('Content-Type: application/json');

    echo json_encode($flowData->jsonbundler->json);
    
    return;

}
$device = $flowData->device;

// Some browsers require that extra HTTP headers are explicitly
// requested. So set whatever headers are required by the browser in
// order to return the evidence needed by the pipeline.
// More info on this can be found at
// https://51degrees.com/blog/user-agent-client-hints
Utils::setResponseHeader($flowData);

echo "<h2>Example</h2><br/>\n";

echo "<div id=\"content\">";
echo "<p>\n";
echo "The following values are determined by sever-side device detection on the first request:\n";
echo "</p>";
echo "<p><br/>\n";
echo "    Hardware Vendor: " . (isset($device->hardwarevendor) && $device->hardwarevendor->hasValue ? $device->hardwarevendor->value : "Unknown (" . (isset($device->hardwarevendor) ? $device->hardwarevendor->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Hardware Name: " . (isset($device->hardwarename) && $device->hardwarename->hasValue ? $device->hardwarename->value : "Unknown (" . (isset($device->hardwarename) ? $device->hardwarename->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Device Type: " . (isset($device->devicetype) && $device->devicetype->hasValue ? $device->devicetype->value : "Unknown (" . (isset($device->devicetype) ? $device->devicetype->noValueMessage : "property unavailable" .")") ) . "<br />\n";
echo "    Platform Vendor: " . (isset($device->platformvendor) && $device->platformvendor->hasValue ? $device->platformvendor->value : "Unknown (" . (isset($device->platformvendor) ? $device->platformvendor->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Platform Name: " . (isset($device->platformname) && $device->platformname->hasValue ? $device->platformname->value : "Unknown (" . (isset($device->platformname) ? $device->platformname->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Platform Version: " . (isset($device->platformversion) && $device->platformversion->hasValue ? $device->platformversion->value : "Unknown (" . (isset($device->platformversion) ? $device->platformversion->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Browser Vendor: " . (isset($device->browservendor) && $device->browservendor->hasValue ? $device->browservendor->value : "Unknown (" . (isset($device->browservendor) ? $device->browservendor->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Browser Name: " . (isset($device->browsername) && $device->browsername->hasValue ? $device->browsername->value : "Unknown (" . (isset($device->browsername) ? $device->browsername->noValueMessage : "property unavailable" ) .")") . "<br />\n";
echo "    Browser Version: " . (isset($device->browserversion) && $device->browserversion->hasValue ? $device->browserversion->value : "Unknown (" . (isset($device->browserversion) ? $device->browserversion->noValueMessage : "property unavailable" ) .")") . "\n";
echo "    Screen Width (pixels): " . (isset($device->screenpixelswidth) && $device->screenpixelswidth->hasValue ? $device->screenpixelswidth->value : "Unknown (" . (isset($device->screenpixelswidth) ? $device->screenpixelswidth->noValueMessage : "property unavailable" ) .")") . "\n";
echo "    Screen Height (pixels): " . (isset($device->screenpixelsheight) && $device->screenpixelsheight->hasValue ? $device->screenpixelsheight->value : "Unknown (" . (isset($device->screenpixelsheight) ? $device->screenpixelsheight->noValueMessage : "property unavailable" ) .")") . "\n";
echo "</p>";
echo "<p>\n";
echo "The information shown below is determined from JavaScript running on the client-side that is able to obtain additional evidence. If no additional information appears then it may indicate an external problem such as JavaScript being disabled in your browser.\n";
echo "</p>\n";
echo "<p>\n";
echo "Note that the 'Hardware Name' field is intended to illustrate detection of Apple device models as this cannot be determined server-side. This can be tested to some extent using most emulators such as those in the 'developer tools' menu in Google Chrome. However, using real devices will result in more precise model numbers.\n";
echo "</p>\n";
echo "</div><br>\n";

// We get any JavaScript that should be placed in the page and run it, this 
// will set cookies and other information allowing us to access extra 
// properties such as device->screenpixelwidth.

echo "<script>" . $flowData->javascriptbuilder->javascript . "</script>";

echo "
<script>
    // This function will fire when the JSON data object is updated 
    // with information from the server.
    // The sequence is:
    // 1. Response contains JavaScript properties 'ScreenPixelsHeightJavaScript', 'ScreenPixelWidthJavaScript' and 'JavaScriptHardwareProfile'. These are executed on the client.
    // 2. This triggers another call to the webserver that includes the evidence gathered by these JavaScript properties.
    // 3. The web server responds with new JSON data that contains the updated property values based on the new evidence.
    // 4. The JavaScript integrates the new JSON data and fires the 'complete' callback below.
    window.onload = function () {
        fod.complete(function (data) {
            var para = document.createElement('p');
            var br = document.createElement('br');
            var text = document.createTextNode('Updated information from client-side evidence:');
            para.appendChild(text);
            para.appendChild(br);
            text = document.createTextNode('Hardware Name: ' + (data.device.hardwarename ? data.device.hardwarename.join(',') : 'Unknown (property not available)'));
            br = document.createElement('br');
            para.appendChild(text);
            para.appendChild(br);
            text = document.createTextNode('Screen width (pixels): ' + (data.device.screenpixelswidth ? data.device.screenpixelswidth : 'Unknown (property not available)'));
            br = document.createElement('br');
            para.appendChild(text);
            para.appendChild(br);
            text = document.createTextNode('Screen height (pixels): ' + (data.device.screenpixelsheight ? data.device.screenpixelsheight : 'Unknown (property not available)'));
            br = document.createElement('br');
            para.appendChild(text);
            para.appendChild(br);

            var element = document.getElementById('content');
            element.appendChild(para);
        });
    }
</script>";