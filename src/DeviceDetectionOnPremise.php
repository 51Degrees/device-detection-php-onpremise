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

include_once __DIR__ . '/../on-premise/src/php' . explode('.', PHP_VERSION)[0] . '/FiftyOneDegreesHashEngine.php';

use fiftyone\pipeline\core\BasicListEvidenceKeyFilter;
use fiftyone\pipeline\engines\Engine;

class DeviceDetectionOnPremise extends Engine
{
    public $dataKey = 'device';
    public $engine;
    private $evidenceKeys;

    public function __construct()
    {
        // List of pipelines the flowElement has been added to
        $this->pipelines = [];

        $this->engine = \FiftyOneDegreesHashEngine::engine_get();

        $requiredProperties = ini_get('FiftyOneDegreesHashEngine.required_properties');

        if ($requiredProperties) {
            $this->setRestrictedProperties(explode(',', $requiredProperties));
        }

        // Make properties list
        $propertiesInternal = $this->engine->getMetaData()->getProperties();

        $properties = [];
        for ($i = 0; $i < $propertiesInternal->getSize(); ++$i) {
            $property = $propertiesInternal->getByIndex($i);
            $properties[strtolower($property->getName())] = [
                'name' => $property->getName(),
                'type' => $this->getPropertyType($property),
                'dataFiles' => SwigHelpers::vectorToArray($property->getDataFilesWherePresent()),
                'category' => $property->getCategory(),
                'description' => $property->getDescription(),
                'available' => $property->getAvailable(),
                'isList' => $property->getIsList(),
                'component' => $this->getComponentName($property)
            ];
        }

        foreach ($this->getMetricProperties() as $name => $property) {
            $properties[$name] = $property;
        }

        $this->properties = $properties;

        // Make evidence list
        $evidences = $this->engine->getKeys();
        $evidenceKeysList = [];
        for ($i = 0; $i < $evidences->size(); ++$i) {
            $evidence = strtolower($evidences->get($i));
            $evidence = str_replace('http_', '', $evidence);
            $evidence = str_replace('http_x-', '', $evidence);
            $evidenceKeysList[] = $evidence;
        }
        $this->evidenceKeys = $evidenceKeysList;

        parent::__construct(...func_get_args());
    }

    /**
     * Instance of EvidenceKeyFilter based on the evidence keys fetched
     * from the cloud service by the private getEvidenceKeys() method.
     *
     * @return BasicListEvidenceKeyFilter
     */
    public function getEvidenceKeyFilter()
    {
        return new BasicListEvidenceKeyFilter($this->evidenceKeys);
    }

    public function processInternal($flowData)
    {
        // Make evidence collection
        $evidence = $flowData->evidence->getAll();

        $evidenceInternal = new \EvidenceDeviceDetectionSwig();

        foreach ($evidence as $key => $value) {
            if ($this->filterEvidenceKey($key) && is_string($value)) {
                $evidenceInternal->set($key, $value);
            }
        }

        $result = $this->engine->process($evidenceInternal);

        $data = new SwigData($this, $result);

        $flowData->setElementData($data);
    }

    /**
     * Add a cache to an engine.
     *
     * @param \fiftyone\pipeline\engines\DataKeyedCache $cache Cache with get and set methods
     * @throws \Exception
     */
    public function setCache($cache)
    {
        throw new \Exception(Messages::CACHE_ERROR);
    }

    /**
     * Ask the engine to start using the specified data file
     * for detections.
     * This can be used in 3 different scenarios:
     * 1. The data file that was originally used to create the engine has
     * been updated on disk. In this case, no parameters are needed.
     * 2. A new data file is available, but it is in a different location
     * to the original. In this case, the parameter should be the new data
     * file location.
     * 3. A new data file is available in memory. The first parameter will
     * be the variable holding the in-memory data file. The second will
     * be the size of the data file in bytes.
     *
     * @param null|string $fileName_or_data Data file path or the variable holding the in-memory data file
     * @param null|int $length Length of the in-memory data file in bytes
     */
    public function refreshData($fileName_or_data = null, $length = null)
    {
        switch (func_num_args()) {
            case 0: $this->engine->refreshData();
                break;
            case 1: $this->engine->refreshData($fileName_or_data);
                break;
            default: 
                $this->engine->refreshData($fileName_or_data, $length);
        }
    }

    private function getComponentName($property)
    {
        $component = $this->engine->getMetaData()->getComponentForProperty($property);

        return $component->getName();
    }

    private function getPropertyType($property)
    {
        switch ($property->getType()) {
            case 'string': 
                return 'String';
            case 'int': 
                return 'Integer';
            case 'bool': 
                return 'Boolean';
            case 'double': 
                return 'Double';
            case 'javascript': 
                return 'JavaScript';
            case 'string[]': 
                return 'Array';
            default: 
                return 'String';
        }
    }

    private function getMetricProperties()
    {
        $dataFiles = ['Lite', 'Premium', 'Enterprise'];

        return [
            'matchednodes' => [
                'name' => 'MatchedNodes',
                'type' => 'Integer',
                'dataFiles' => $dataFiles,
                'description' => Constants::MATCHED_NODES_DESCRIPTION,
                'category' => 'DeviceMetrics',
                'component' => 'MatchMetrics',
                'isList' => false,
                'available' => true
            ],
            'difference' => [
                'name' => 'Difference',
                'type' => 'Integer',
                'dataFiles' => $dataFiles,
                'description' => Constants::DIFFERENCE_DESCRIPTION,
                'category' => 'DeviceMetrics',
                'component' => 'MatchMetrics',
                'isList' => false,
                'available' => true
            ],
            'drift' => [
                'name' => 'Drift',
                'type' => 'Integer',
                'dataFiles' => $dataFiles,
                'description' => Constants::DRIFT_DESCRIPTION,
                'category' => 'DeviceMetrics',
                'component' => 'MatchMetrics',
                'isList' => false,
                'available' => true
            ],
            'deviceid' => [
                'name' => 'DeviceId',
                'type' => 'String',
                'dataFiles' => $dataFiles,
                'description' => Constants::DEVICE_ID_DESCRIPTION,
                'category' => 'DeviceMetrics',
                'component' => 'MatchMetrics',
                'isList' => false,
                'available' => true
            ],
            'useragents' => [
                'name' => 'UserAgents',
                'type' => 'Array',
                'dataFiles' => $dataFiles,
                'description' => Constants::USER_AGENTS_DESCRIPTION,
                'category' => 'DeviceMetrics',
                'component' => 'MatchMetrics',
                'isList' => true,
                'available' => true
            ],
            'iterations' => [
                'name' => 'Iterations',
                'type' => 'Integer',
                'dataFiles' => $dataFiles,
                'description' => Constants::ITERATIONS_DESCRIPTION,
                'category' => 'DeviceMetrics',
                'component' => 'MatchMetrics',
                'isList' => false,
                'available' => true
            ],
            'method' => [
                'name' => 'Method',
                'type' => 'String',
                'dataFiles' => $dataFiles,
                'description' => Constants::METHOD_DESCRIPTION,
                'category' => 'DeviceMetrics',
                'component' => 'MatchMetrics',
                'isList' => false,
                'available' => true
            ]
        ];
    }
}
