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

namespace fiftyone\pipeline\devicedetection\tests;

// Fake remote address for web integration

$_SERVER['REMOTE_ADDR'] = '0.0.0.0';

use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\devicedetection\Constants;
use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\devicedetection\Messages;
use PHPUnit\Framework\TestCase;

class DeviceDetectionTests extends TestCase
{
    protected $iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';

    // TODO: fix the test
    public function __SKIP__testAvailableProperties()
    {
        $deviceDetection = new DeviceDetectionOnPremise();

        $builder = new PipelineBuilder();

        $pipeline = $builder->add($deviceDetection)->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set('header.user-agent', $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement('device')->getProperties();

        foreach ($properties as &$property) {
            $key = strtolower($property['name']);

            $apv = $result->device->getInternal($key);

            $this->assertNotNull($apv, $key);

            if ($apv->hasValue) {
                $this->assertNotNull($apv->value, $key);
            } else {
                $this->assertNotNull($apv->noValueMessage, $key);
            }
        }
    }

    // TODO: fix the test
    public function __SKIP__testValueTypes()
    {
        $deviceDetection = new DeviceDetectionOnPremise();

        $builder = new PipelineBuilder();

        $pipeline = $builder->add($deviceDetection)->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set('header.user-agent', $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement('device')->getProperties();

        foreach ($properties as &$property) {
            $key = strtolower($property['name']);

            // TODO: Skip 'useragents' and 'method' property. These are returned
            // as an int() by the SWIG interface but should be an Array and a
            // String respectively.
            if ($key == 'useragents' || $key == 'method') {
                continue;
            }

            $apv = $result->device->getInternal($key);

            $expectedType = $property['type'];

            $this->assertNotNull($apv, $key);

            $value = $apv->value;

            switch ($expectedType) {
                case 'Boolean':
                    if (method_exists($this, 'assertIsBool')) {
                        $this->assertIsBool($value, $key);
                    } else {
                        $this->assertInternalType('boolean', $value, $key);
                    }
                    break;
                case 'String':
                    if (method_exists($this, 'assertIsString')) {
                        $this->assertIsString($value, $key);
                    } else {
                        $this->assertInternalType('string', $value, $key);
                    }
                    break;
                case 'JavaScript':
                    if (method_exists($this, 'assertIsString')) {
                        $this->assertIsString($value, $key);
                    } else {
                        $this->assertInternalType('string', $value, $key);
                    }
                    break;
                case 'Integer':
                    if (method_exists($this, 'assertIsInt')) {
                        $this->assertIsInt($value, $key);
                    } else {
                        $this->assertInternalType('integer', $value, $key);
                    }
                    break;
                case 'Double':
                    if (method_exists($this, 'assertIsFloat')) {
                        $this->assertIsFloat($value, $key);
                    } else {
                        $this->assertInternalType('double', $value, $key);
                    }
                    break;
                case 'Array':
                    if (method_exists($this, 'assertIsArray')) {
                        $this->assertIsArray($value, $key);
                    } else {
                        $this->assertInternalType('array', $value, $key);
                    }
                    break;
                default:
                    $this->fail('expected type for ' . $key . ' was ' . $expectedType);
                    break;
            }
        }
    }

    public function testPropertyValueBad()
    {
        $deviceDetection = new DeviceDetectionOnPremise();

        $builder1 = new PipelineBuilder();

        $badUA = '~';

        $pipeline1 = $builder1->add($deviceDetection)->build();

        $flowData1 = $pipeline1->createFlowData();

        $flowData1->evidence->set('header.user-agent', $badUA);

        $result = $flowData1->process();

        $this->assertFalse($result->device->ismobile->hasValue);
        $this->assertSame(
            'No matching profiles could be found for the supplied evidence. '
                . "A 'best guess' can be returned by configuring more lenient "
                . 'matching rules. See '
                . 'https://51degrees.com/documentation/_device_detection__features__false_positive_control.html',
            $result->device->ismobile->noValueMessage
        );
    }

    public function testPropertyValueGood()
    {
        $deviceDetection = new DeviceDetectionOnPremise();

        $builder = new PipelineBuilder();

        $pipeline = $builder->add($deviceDetection)->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set('header.user-agent', $this->iPhoneUA);

        $result = $flowData->process();

        $this->assertTrue($result->device->ismobile->value);
    }

    public function testGetProperties()
    {
        $deviceDetection = new DeviceDetectionOnPremise();

        $builder = new PipelineBuilder();

        $pipeline = $builder->add($deviceDetection)->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set('header.user-agent', $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement('device')->getProperties();

        $this->assertSame('IsMobile', $properties['ismobile']['name']);
        $this->assertSame('Boolean', $properties['ismobile']['type']);
        $this->assertSame('Device', $properties['ismobile']['category']);
    }

    public function testMatchMetricsDescription()
    {
        $deviceDetection = new DeviceDetectionOnPremise();

        $builder = new PipelineBuilder();

        $pipeline = $builder->add($deviceDetection)->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set('header.user-agent', $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement('device')->getProperties();

        $this->assertSame(Constants::MATCHED_NODES_DESCRIPTION, $properties['matchednodes']['description']);
        $this->assertSame(Constants::DIFFERENCE_DESCRIPTION, $properties['difference']['description']);
        $this->assertSame(Constants::DRIFT_DESCRIPTION, $properties['drift']['description']);
        $this->assertSame(Constants::DEVICE_ID_DESCRIPTION, $properties['deviceid']['description']);
        $this->assertSame(Constants::USER_AGENTS_DESCRIPTION, $properties['useragents']['description']);
        $this->assertSame(Constants::ITERATIONS_DESCRIPTION, $properties['iterations']['description']);
        $this->assertSame(Constants::METHOD_DESCRIPTION, $properties['method']['description']);
    }

    public function testFailureToMatch()
    {
        include __DIR__ . '/../examples/onpremise/failureToMatch.php';

        $this->assertTrue(true);
    }

    public function testManualDataUpdate()
    {
        include __DIR__ . '/../examples/onpremise/manualDataUpdate.php';

        $this->assertTrue(true);
    }

    /**
     * Check that when a cache is configured for the engine, an exception is
     * throw with an appropriate message.
     */
    public function testSetCache()
    {
        $exception = null;
        $deviceDetection = new DeviceDetectionOnPremise();

        $this->expectExceptionMessage(Messages::CACHE_ERROR);
        $deviceDetection->setCache(null);
    }
}
