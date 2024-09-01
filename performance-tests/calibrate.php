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

require(__DIR__ . "/../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\core\JavascriptBuilderElement;
use fiftyone\pipeline\core\JsonBundlerElement;

$device = new DeviceDetectionOnPremise();

$builder = new PipelineBuilder(array());

$pipeline = $builder->add($device)->build();

// We create the flowData object that is used to add evidence to and read
// data from 
//$flowData = $pipeline->createFlowData();

// We set headers, cookies and more information from the web request
//$flowData->evidence->setFromWebRequest();

// Now we process the flowData
//$result = $flowData->process();

//$device = $flowData->device;

echo "<h2>Example</h2><br/>\n";

echo "<div id=\"content\">";
echo "<p><br/>\n";
echo "</p>";
echo "</div><br>\n";

