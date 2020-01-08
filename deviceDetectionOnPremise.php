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

namespace fiftyone\pipeline\devicedetection;

include(__DIR__ . "/swigHelpers.php");
include(__DIR__ . "/swigData.php");

use fiftyone\pipeline\engines\aspectDataDictionary;
use fiftyone\pipeline\engines\engine;
use fiftyone\pipeline\devicedetection\swigHelpers;
use fiftyone\pipeline\devicedetection\swigData;

class deviceDetectionOnPremise extends engine {

    public $dataKey = "device";

    public function __construct($FiftyOneProvider){

        // List of pipelines the flowElement has been added to
        $this->pipelines = [];

        if ($FiftyOneProvider === "Pattern") {
            include(__dir__ . "/on-premise/DeviceDetectionPatternEngineModule.php");
            $this->engine = \DeviceDetectionPatternEngineModule::engine_get();
        } else if ($settings["FiftyOneProvider"] === "Hash") {
            $this->engine = \DeviceDetectionHashEngineModule::engine_get();
            include(__dir__ . "/on-premise/DeviceDetectionHashEngineModule.php");
        } else {
            throw "Must pass in 'Pattern' or 'Hash' to deviceDetectionOnPremise";
        }

        $requiredProperties = ini_get("FiftyOneDegreesHashEngine.required_properties");

        if($requiredProperties){

            $this->setRestrictedProperties(explode(",", $requiredProperties));

        }

        // Make properties list

        $propertiesInternal = $this->engine->getMetaData()->getProperties();

        $properties = [];
       
        for ($i = 0; $i < $propertiesInternal->getSize(); $i++) {
            $property = $propertiesInternal->getByIndex($i);
            if ($property->getAvailable()) {

                $properties[strtolower($property->getName())] = [
                    "meta" => [
                        "name" => $property->getName(),
                        "type" => $property->getType(),
                        "dataFiles" => swigHelpers::vectorToArray($property->getDataFilesWherePresent()),
                        "category" => $property->getCategory()
                    ]
                ];
            }
        }
       
        $this->properties = $properties;

        parent::__construct(...func_get_args());

    }

    public function processInternal($flowData) {

        // Make evidence collection

        $evidence = $flowData->evidence->getAll();  

        $evidenceInternal = new \EvidenceDeviceDetectionSwig();

        foreach($evidence as $key => $value){

            $evidenceInternal->set($key, $value);

        }

        $result = $this->engine->processDeviceDetection($evidenceInternal);

        $data = new swigData($this, $result);
            
        $flowData->setElementData($data);

    }

}

