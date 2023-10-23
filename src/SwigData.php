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

namespace fiftyone\pipeline\devicedetection;

use fiftyone\pipeline\engines\AspectData;
use fiftyone\pipeline\core\AspectPropertyValue;


class SwigData extends AspectData 
{
    private $result;
    
    public function __construct($engine, $result){

        $this->result = $result;
        
        parent::__construct(...func_get_args());
        
    }

    public function __isset($key) {
        return isset($this->flowElement->properties[strtolower($key)]);
    }

    public function getInternal($key){
        $result;

        $key = strtolower($key);

        if(isset($this->flowElement->properties[$key])){
            $property = $this->flowElement->properties[$key];

            if ($property["category"] == "DeviceMetrics") {
                switch ($property["name"]) {
                    case "MatchedNodes":
                        $result = new AspectPropertyValue(
                                null,
                                $this->result->getMatchedNodes());
                        break;
                    case "Difference":
                        $result = new AspectPropertyValue(
                                null,
                                $this->result->getDifference());
                        break;
                    case "Drift":
                        $result = new AspectPropertyValue(
                                null,
                                $this->result->getDrift());
                        break;
                    case "DeviceId":
                        $result = new AspectPropertyValue(
                                null,
                                $this->result->getDeviceId());
                        break;
                    case "UserAgents":
                        $useragents = array();
                        $count = $this->result->getUserAgents();
                        for ($i = 0; $i < $count; $i++) {
                            $useragents[] = $this->result->getUserAgent($i);
                        }
                        $result = new AspectPropertyValue(
                                null,
                                $useragents);
                        break;
                    case "Method":
                        $result = new AspectPropertyValue(
                                null,
                                $this->result->getDifference());
                        break;
                    case "Iterations":
                        $result = new AspectPropertyValue(
                                null,
                                $this->result->getIterations());
                        break;
                }
            }
            else {
                switch ($property["type"]) {
                    case "Boolean":                
                        $value = $this->result->getValueAsBool($property["name"]);
                        break;
                    case "String":
                        $value = $this->result->getValueAsString($property["name"]);
                        break;
                    case "JavaScript":
                        $value = $this->result->getValueAsString($property["name"]);
                        break;
                    case "Integer":
                        $value = $this->result->getValueAsInteger($property["name"]);
                        break;
                    case "Double":
                        $value = $this->result->getValueAsDouble($property["name"]);
                        break;
                    case "Array":
                        $value = $this->result->getValues($property["name"]);
                        break;
                }

                if ($value->hasValue()) {
                    if ($property["type"] == "Array") {
                        $result = new AspectPropertyValue(null, SwigHelpers::vectorToArray($value->getValue()));
                    }
                    else {
                        $result = new AspectPropertyValue(null, $value->getValue());
                    }
                }
                else {
                    $result = new AspectPropertyValue($value->getNoValueMessage());
                }
            }
            return $result;

        }

    }

}
