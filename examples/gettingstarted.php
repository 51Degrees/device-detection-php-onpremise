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
 * @example on-premise/gettingstarted.php
 * 
 * In this example we create an on premise 51Degrees device detection pipeline, 
 * in order to do this you will need a copy of the 51Degrees on-premise library 
 * and need to make the following additions to your php.ini file
 * 
    "FiftyOneDegreesPatternEngine.data_file" = // location of the data file
    "FiftyOneDegreesPatternEngine.required_properties" = // A list of properties
    "FiftyOneDegreesPatternEngine.performance_profile" = // A performance profile if needed
    "FiftyOneDegreesPatternEngine.drift" = // Drift value if needed
    "FiftyOneDegreesPatternEngine.difference" = Difference value if needed
    
 *
**/

require(__dir__ . "../../../deviceDetectionOnPremise.php");

require("../../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\deviceDetectionOnPremise;
use fiftyone\pipeline\core\pipelineBuilder;
use fiftyone\pipeline\javascriptbundler\javaScriptBundlerElement;

$device = new deviceDetectionOnPremise("Pattern");
$javaScriptBundler = new javaScriptBundlerElement();

$pipeline = new pipelineBuilder();

$pipeline = $pipeline->add($device)->add($javaScriptBundler)->build();

$fd = $pipeline->createFlowData();

// A user agent to test

$iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';

$fd->evidence->set("header.user-agent", $iPhoneUA) ;

$fd->process();

if($fd->device->ismobile->hasValue){

    var_dump($fd->device->ismobile->value);
    
} else {
    
    var_dump($fd->device->ismobile->noValueMessage);

}
