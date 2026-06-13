<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2026 51 Degrees Mobile Experts Limited, Davidson House,
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
 * ```
 * User Agent Client Hints Example
 *
 * Hardware Vendor: Unknown
 * Hardware Name: Array
 * Device Type: Desktop
 *
 * ```
 */

require __DIR__ . '/../../vendor/autoload.php';

use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\core\Utils;
use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\devicedetection\examples\onpremise\classes\ExampleUtils;

// The example is run as a router script for the PHP built-in server
// (php -S localhost:3000 userAgentClientHints-Web.php), so every request,
// including the shared CSS and JS assets, is routed here. Serve the
// vendored pattern-library assets from the static directory before
// running detection.
$assets = [
    '/css/examples-main.min.css' => ['static/css/examples-main.min.css', 'text/css'],
    '/js/examples.min.js' => ['static/js/examples.min.js', 'application/javascript']
];

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (isset($assets[$requestPath])) {
    [$relativePath, $contentType] = $assets[$requestPath];
    $assetFile = __DIR__ . '/' . $relativePath;

    if (is_file($assetFile)) {
        header('Content-Type: ' . $contentType);
        echo file_get_contents($assetFile);
    } else {
        http_response_code(404);
    }

    return;
}

$device = new DeviceDetectionOnPremise();

$builder = new PipelineBuilder([]);

$pipeline = $builder->add($device)->build();

// We create the flowData object that is used to add evidence to and read data from
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
// https://51degrees.com/blog/user-agent-client-hints?utm_source=code&utm_medium=example&utm_campaign=device-detection-php-onpremise&utm_content=examples-onpremise-useragentclienthints-web.php&utm_term=top
Utils::setResponseHeader($flowData);

// Determine whether the free 'Lite' data file is in use so that the
// contact-us banner can be shown only on that tier.
$showContactUs = ExampleUtils::getDataFileTier($flowData->pipeline->getElement('device')) === 'Lite';
?>
<head>
    <title>User-Agent Client Hints Example</title>
    <link rel="stylesheet" href="/css/examples-main.min.css" />
</head>

<div class="c-eg-page">
    <h2 class="c-eg-page__title">User-Agent Client Hints example</h2>

    <p class="c-eg-page__lead">
        By default, the user-agent, sec-ch-ua and sec-ch-ua-mobile HTTP headers are sent.
        This means that on the first request, the server can determine the browser from
        sec-ch-ua while other details must be derived from the user-agent.
    </p>
    <p class="c-eg-page__lead">
        If the server determines that the browser supports client hints, then it may request
        additional client hints headers by setting the Accept-CH header in the response.
        Select the <strong>Make second request</strong> button below to send another request
        to the server. This time, any additional client hints headers that have been requested
        will be included.
    </p>

    <div class="c-eg-page__actions">
        <button type="button" class="b-btn" onclick="redirect()">Make second request</button>
    </div>

    <div id="evidence">
        <h3 class="c-eg-page__heading">Evidence values used</h3>
        <p class="c-eg-legend">
            Evidence was
            <span class="c-eg-legend__swatch c-eg-legend__swatch--used">used</span>
            /
            <span class="c-eg-legend__swatch c-eg-legend__swatch--present">present</span>
            for detection
        </p>
        <table class="c-eg-table">
            <thead class="c-eg-table__head">
                <tr class="c-eg-table__row">
                    <th class="c-eg-table__cell">Key</th>
                    <th class="c-eg-table__cell">Value</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($flowData->evidence->getAll() as $key => $value) {
                        if ($flowData->pipeline->getElement('device')->getEvidenceKeyFilter()->filterEvidenceKey($key)) {
                            echo "<tr class='c-eg-table__row c-eg-table__row--used'>";
                        } else {
                            echo "<tr class='c-eg-table__row c-eg-table__row--present'>";
                        }
                        echo '<td class="c-eg-table__cell c-eg-table__cell--key">' . htmlspecialchars(strval($key)) . '</td>';
                        echo '<td class="c-eg-table__cell">' . htmlspecialchars(strval($value)) . '</td>';
                        echo '</tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>

    <h3 class="c-eg-page__heading">Detection results</h3>
    <p id="description" class="c-eg-page__lead"></p>
    <table class="c-eg-table">
        <thead class="c-eg-table__head">
            <tr class="c-eg-table__row">
                <th class="c-eg-table__cell">Key</th>
                <th class="c-eg-table__cell">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Hardware Vendor:</td><td class="c-eg-table__cell"><?php echo htmlspecialchars(ExampleUtils::getHumanReadable($device, 'hardwarevendor')); ?></td></tr>
            <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Hardware Name:</td><td class="c-eg-table__cell"><?php echo htmlspecialchars(ExampleUtils::getHumanReadable($device, 'hardwarename')); ?></td></tr>
            <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Device Type:</td><td class="c-eg-table__cell"><?php echo htmlspecialchars(ExampleUtils::getHumanReadable($device, 'devicetype')); ?></td></tr>
            <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Platform Vendor:</td><td class="c-eg-table__cell"><?php echo htmlspecialchars(ExampleUtils::getHumanReadable($device, 'platformvendor')); ?></td></tr>
            <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Platform Name:</td><td class="c-eg-table__cell"><?php echo htmlspecialchars(ExampleUtils::getHumanReadable($device, 'platformname')); ?></td></tr>
            <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Platform Version:</td><td class="c-eg-table__cell"><?php echo htmlspecialchars(ExampleUtils::getHumanReadable($device, 'platformversion')); ?></td></tr>
            <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Browser Vendor:</td><td class="c-eg-table__cell"><?php echo htmlspecialchars(ExampleUtils::getHumanReadable($device, 'browservendor')); ?></td></tr>
            <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Browser Name:</td><td class="c-eg-table__cell"><?php echo htmlspecialchars(ExampleUtils::getHumanReadable($device, 'browsername')); ?></td></tr>
            <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Browser Version:</td><td class="c-eg-table__cell"><?php echo htmlspecialchars(ExampleUtils::getHumanReadable($device, 'browserversion')); ?></td></tr>
        </tbody>
    </table>

    <?php if ($showContactUs) { ?>
    <div class="c-eg-message">
      <p class="c-eg-message__text">Need more on-premise properties and features? <a href="https://51degrees.com/contact-us">Contact us</a> to explore the options.</p>
      <a class="b-btn c-eg-message__cta" href="https://51degrees.com/contact-us">Contact us</a>
    </div>
    <?php } ?>
</div>

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
    // 3. When "Make second request" button will be clicked, device detection request will again
    // be sent to the server with all additional client hints that was requested in the previous
    // response by the server.
    // 4. Web Server will return the properties based on the new User Agent CLient Hint headers
    // being used as evidence.

    function redirect() {
        sessionStorage.reloadAfterPageLoad = true;
        window.location.reload(true);
    }

    window.onload = function () {
        if (sessionStorage.reloadAfterPageLoad) {
            document.getElementById('description').innerHTML = 'The information shown below is determined using <strong>User-Agent Client Hints</strong> that was sent in the request to obtain additional evidence. If no additional information appears then it may indicate an external problem such as <strong>User-Agent Client Hints</strong> being disabled in your browser.';
            sessionStorage.reloadAfterPageLoad = false;
        } else {
            document.getElementById('description').innerHTML = 'The following values are determined by server-side device detection on the first request.';
        }
    };

</script>
