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
?>
<head>
    <title>Web Integration Example</title>
    <style>
        <?php
        use fiftyone\pipeline\devicedetection\examples\onpremise\classes\ExampleUtils;
        require __DIR__ . '/main.css';
        ?>
    </style>
</head>

<div class="main">
    <h2>Web Integration Example</h2>

    <p>
        This example demonstrates the use of the Pipeline API to perform device detection within a
        simple PHP web project. In particular, it highlights:
        <ol>
            <li>
                Automatic handling of the 'Accept-CH' header, which is used to request User-Agent
                Client Hints from the browser
            </li>
            <li>
                Client-side evidence collection in order to identify Apple device models and properties
                such as screen size.
            </li>
        </ol>
    </p>
    <h3>Client Hints</h3>
    <p>
        When the first request is made, browsers that support client hints will typically send a subset
        of client hints values along with the User-Agent header.
        If device detection determines that the browser does support client hints then it will request
        that additional client hints headers are sent with future requests by sending the Accept-CH
        header with the response.
    </p>
    <p>
        Note that if you have visited this page previously, the value of Accept-CH will have been
        cached so all requested client hints headers will be sent on the first request. Using features
        such as 'private browsing' or 'incognito mode' will allow you to see the true first request
        experience as the previous Accept-CH value will not be used.
    </p>

    <noscript>
        <div class="example-alert">
            WARNING: JavaScript is disabled in your browser. This means that the callback discussed
            further down this page will not fire and UACH headers will not be sent.
        </div>
    </noscript>
    <?php if (ExampleUtils::dataFileIsOld($flowData->pipeline->getElement('device'))) { ?>
        <div class="example-alert">
            WARNING: This example is using a data file that is more than 
            <?php echo ExampleUtils::DATA_FILE_AGE_WARNING; ?>
            days old. A more recent data file may be needed to 
            correctly detect the latest devices, browsers, etc. The latest lite data file is available 
            from the 
            <a href="https://github.com/51Degrees/device-detection-data">device-detection-data</a>
            repository on GitHub. Find out about the Enterprise data file, which includes automatic 
            daily updates, on our <a href="https://51degrees.com/pricing">pricing page</a>.
        </div>
    <?php } ?>

    <div id="content">
        <div id="response-headers">
            <h2>Response headers:</h2>
            <p class="smaller">The following response headers were set:</p>
            <table>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
                <?php
                    foreach (headers_list() as $header) {
                        $parts = explode(': ', $header);
                        $output("<tr class='lightyellow'>");
                        $output('<td><b>' . $parts[0] . '</b></td>');
                        $output('<td>' . $parts[1] . '</td>');
                    }
                ?>
            </table>
        </div>

        <?php if (ExampleUtils::containsAcceptCh() == false) { ?>
            <div class="example-alert">
                WARNING: There is no Accept-CH header in the response. This may indicate that your 
                browser does not support User-Agent Client Hints. This is not necessarily a problem,
                but if you are wanting to try out detection using User-Agent Client Hints, then make
                sure that your browser 
                <a href="https://developer.mozilla.org/en-US/docs/Web/API/User-Agent_Client_Hints_API#browser_compatibility">supports them</a>.
            </div>
        <?php } ?>
        <br />

        <div id="evidence">
            <h2>Evidence Used:</h2>
            <p class="smaller">Evidence was <span class="lightgreen">used</span> / <span class="lightyellow">present</span> for detection</p>
            <table>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
                <?php
                    foreach ($flowData->evidence->getAll() as $key => $value) {
                        if ($flowData->pipeline->getElement('device')->getEvidenceKeyFilter()->filterEvidenceKey($key)) {
                            $output("<tr class='lightgreen'>");
                        } else {
                            $output("<tr class='lightyellow'>");
                        }
                        $output('<td><b>' . $key . '</b></td>');
                        $output('<td>' . $value . '</td>');
                        $output('</tr>');
                    }
                ?>
            </table>
        </div>
        <br />

        <h2>Device Data</h2>
        <p class="smaller">
            The following values are determined by sever-side device detection
            on the first request:
        </p>
        <table>
            <tr>
                <th>Key</th>
                <th>Value</th>
            </tr>
            <tr class="lightyellow"><td><b>Hardware Vendor:</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'hardwarevendor')); ?></td></tr>
            <tr class="lightyellow"><td><b>Hardware Name:</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'hardwarename')); ?></td></tr>
            <tr class="lightyellow"><td><b>Device Type:</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'devicetype')); ?></td></tr>
            <tr class="lightyellow"><td><b>Platform Vendor:</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'platformvendor')); ?></td></tr>
            <tr class="lightyellow"><td><b>Platform Name:</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'platformname')); ?></td></tr>
            <tr class="lightyellow"><td><b>Platform Version:</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'platformversion')); ?></td></tr>
            <tr class="lightyellow"><td><b>Browser Vendor:</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'browservendor')); ?></td></tr>
            <tr class="lightyellow"><td><b>Browser Name:</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'browsername')); ?></td></tr>
            <tr class="lightyellow"><td><b>Browser Version:</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'browserversion')); ?></td></tr>
            <tr class="lightyellow"><td><b>Screen width (pixels):</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'screenpixelswidth')); ?></td></tr>
            <tr class="lightyellow"><td><b>Screen height (pixels):</b></td><td> <?php $output(ExampleUtils::getHumanReadable($flowData->device, 'screenpixelsheight')); ?></td></tr>
        </table>
        <br />

        <h3>Client-side Evidence and Apple Models</h3>
        <p>
            The information shown below is determined after a callback is made to the server with
            additional evidence that is gathered by JavaScript running on the client-side.
            The callback will also include any additional client hints headers that have been requested.
        </p>
        <p>
            When an Apple device is used, the results from
            the first request above will show all Apple models because the server cannot tell the
            exact model of the device. In contrast, the results from the callback below will show
            a smaller set of possible models.
            This can be tested to some extent using most emulators, such as those in the
            'developer tools' menu in Google Chrome. However, these are not the identical to real
            devices so this can cause some unusual results. Using real devices will generally be more
            successful.
        </p>
        <p>
            If you want to work with Apple Model or other client-side information, such as screen
            width/height on the server, then you will need to ensure that the 'enableCookies' setting
            is set to 'true' as in the pipeline construction for this example.
            This will cause the additional client-side evidence to be saved as cookies on the client.
            When a future page is requested, these cookies will be included with the request and the
            device detection API will include them when working out the details of the device.
            Refreshing this page can be used to show this in action. Any values that are unique to the 
            client-side values below will appear in the evidence values used and server-side results 
            after the refresh.
        </p>
        <?php if (ExampleUtils::getDataFileTier($flowData->pipeline->getElement('device')) == 'Lite') { ?>
            <div class="example-alert">
                WARNING: You are using the free 'Lite' data file. This does not include the client-side
                evidence capabilities of the paid-for data file, so you will not see any additional
                data below. Find out about the Enterprise data file on our
                <a href="https://51degrees.com/pricing">pricing page</a>.
            </div>
        <?php } ?>
    </div>
<div>
<!--
    This script is constructed by the fiftyone\pipeline\core package.
    It adds a JavaScript include for 51Degrees.core.js.
    The 51Degrees pipeline will dynamically generate JavaScript, which includes a
    JSON representation of the contents of flow data.
    i.e. The results from device detection.

    In addition, this JavaScript will look for properties that have a flag set indicating that
    they contain executable script snippets.
    These snippets will be executed and the values they obtain will be sent back to the server
    in order for it to perform the detection process again with the new information.
    This callback will also include any User-Agent Client Hints headers that have been requested
    with the 'Accept-CH' header. (assuming the browser is willing to send them)

    When the server responds, the JSON representation of the results will be updated with the
    new values and the 'complete' event will fire.
    Below, we subscribe to this complete event and display the values from the updated JSON.
-->
<script>
    <?php
        $output($flowData->javascriptbuilder->javascript);
    ?>
</script>

<script>
    window.onload = function () {
        // Subscribe to the 'complete' event.
        fod.complete(function (data) {
            // When the event fires, use the supplied data to populate a new table.
            let fieldValues = [];

            var hardwareName = typeof data.device.hardwarename == "undefined" ?
                "Unknown" : data.device.hardwarename.join(", ")
            fieldValues.push(["Hardware Name: ", hardwareName]);
            fieldValues.push(["Platform: ",
                data.device.platformname + " " + data.device.platformversion]);
            fieldValues.push(["Browser: ",
                data.device.browsername + " " + data.device.browserversion]);
            fieldValues.push(["Screen width (pixels): ", data.device.screenpixelswidth]);
            fieldValues.push(["Screen height (pixels): ", data.device.screenpixelsheight]);
            displayValues(fieldValues);
        });
    }

    // Helper function to add a table that displays the supplied values.
    function displayValues(fieldValues) {
        var table = document.createElement("table");
        var tr = document.createElement("tr");
        addToRow(tr, "th", "Key", false);
        addToRow(tr, "th", "Value", false);
        table.appendChild(tr);

        fieldValues.forEach(function (entry) {
            var tr = document.createElement("tr");
            tr.classList.add("lightyellow");
            addToRow(tr, "td", entry[0], true);
            addToRow(tr, "td", entry[1], false);
            table.appendChild(tr);
        });

        var element = document.getElementById("content");
        element.appendChild(table);
    }

    // Helper function to add an entry to a table row.
    function addToRow(row, elementName, text, strong) {
        var entry = document.createElement(elementName);
        var textNode = document.createTextNode(text);
        if (strong === true) {
            var strongNode = document.createElement("strong");
            strongNode.appendChild(textNode);
            textNode = strongNode;
        }
        entry.appendChild(textNode);
        row.appendChild(entry);
    }
</script>
