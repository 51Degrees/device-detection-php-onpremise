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

include_once(__DIR__ . "/SwigHelpers.php");
include_once(__DIR__ . "/SwigData.php");

include_once(__DIR__ . "/on-premise/src/php" . explode('.', PHP_VERSION)[0] . "/FiftyOneDegreesHashEngine.php");

use fiftyone\pipeline\engines\AspectDataDictionary;
use fiftyone\pipeline\engines\Engine;
use fiftyone\pipeline\devicedetection\SwigHelpers;
use fiftyone\pipeline\devicedetection\SwigData;

class DeviceDetectionOnPremise extends Engine {

    public $dataKey = "device";

    public function __construct(){

        // List of pipelines the flowElement has been added to
        $this->pipelines = [];

        $this->engine = \FiftyOneDegreesHashEngine::engine_get();

        $requiredProperties = ini_get("FiftyOneDegreesHashEngine.required_properties");

        if($requiredProperties){

            $this->setRestrictedProperties(explode(",", $requiredProperties));

        }

        // Make properties list

        $propertiesInternal = $this->engine->getMetaData()->getProperties();

        $properties = [];
       
        for ($i = 0; $i < $propertiesInternal->getSize(); $i++) {
            $property = $propertiesInternal->getByIndex($i);
            $properties[strtolower($property->getName())] = [
                "name" => $property->getName(),
                "type" => $this->getPropertyType($property),
                "dataFiles" => SwigHelpers::vectorToArray($property->getDataFilesWherePresent()),
                "category" => $property->getCategory(),
                "description" => $property->getDescription(),
                "available" => $property->getAvailable()
            ];
        }
       
        $this->properties = $properties;

        parent::__construct(...func_get_args());

    }

    private function getPropertyType($property) {
        switch ($property->getType()) {
            case "string": return "String";
            case "int": return "Integer";
            case "bool": return "Boolean";
            case "double": return "Double";
            case "javascript": return "JavaScript";
            case "string[]": return "Array";
            default: return "String";
        }
    }

    public function processInternal($flowData) {

        // Make evidence collection

        $evidence = $flowData->evidence->getAll();  

        $evidenceInternal = new \EvidenceDeviceDetectionSwig();

        foreach($evidence as $key => $value){

            $evidenceInternal->set($key, $value);

        }

        $result = $this->engine->processDeviceDetection($evidenceInternal);

        $data = new SwigData($this, $result);
            
        $flowData->setElementData($data);

    }

}

