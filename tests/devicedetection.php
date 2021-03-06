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


require(__DIR__ . "/../vendor/autoload.php");

// Fake remote address for web integration

$_SERVER["REMOTE_ADDR"] = "0.0.0.0";

use PHPUnit\Framework\TestCase;
use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\devicedetection\Messages;

class exampleTests extends TestCase
{
    protected $iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';

    public function testPropertyValueBad()
	{
        $deviceDetection = new DeviceDetectionOnPremise();

        $builder1 = new PipelineBuilder();

        $badUA = 'w5higsnrg';

        $pipeline1 = $builder1->add($deviceDetection)->build();

        $flowData1 = $pipeline1->createFlowData();

        $flowData1->evidence->set("header.user-agent", $badUA);

        $result = $flowData1->process();
        
		$this->assertFalse($result->device->ismobile->hasValue);
        $this->assertEquals($result->device->ismobile->noValueMessage, 
            "No matching profiles could be found for the supplied evidence. "
                . "A 'best guess' can be returned by configuring more lenient "
                . "matching rules. See "
                . "https://51degrees.com/documentation/_device_detection__features__false_positive_control.html");
    }

    public function testPropertyValueGood()
	{

        $deviceDetection = new DeviceDetectionOnPremise();

        $builder = new PipelineBuilder();

        $pipeline = $builder->add($deviceDetection)->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set("header.user-agent", $this->iPhoneUA);

        $result = $flowData->process();
        
		$this->assertTrue($result->device->ismobile->value);

    }

    public function testGetProperties()
	{

        $deviceDetection = new DeviceDetectionOnPremise();

        $builder = new PipelineBuilder();

        $pipeline = $builder->add($deviceDetection)->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set("header.user-agent", $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement("device")->getProperties();

		$this->assertEquals($properties["ismobile"]["name"], "IsMobile");
		$this->assertEquals($properties["ismobile"]["type"], "Boolean");
		$this->assertEquals($properties["ismobile"]["category"], "Device");
        
    }

    public function testAvailableProperties()
    {

        $deviceDetection = new DeviceDetectionOnPremise();

        $builder = new PipelineBuilder();

        $pipeline = $builder->add($deviceDetection)->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set("header.user-agent", $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement("device")->getProperties();

        foreach ($properties as &$property)
        {
            $key = strtolower($property["name"]);

            $apv = $result->device->getInternal($key);

            $this->assertNotNull($apv, $key);

            if ($apv->hasValue) {

                $this->assertNotNull($apv->value, $key);

            } else {

                $this->assertNotNull($apv->noValueMessage, $key);

            }
        }
    }

    public function testValueTypes()
    {

        $deviceDetection = new DeviceDetectionOnPremise();

        $builder = new PipelineBuilder();

        $pipeline = $builder->add($deviceDetection)->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set("header.user-agent", $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement("device")->getProperties();

        foreach ($properties as &$property)
        {
            $key = strtolower($property["name"]);
 
            // TODO: Skip 'useragents' and 'method' property. These are returned 
            // as an int() by the SWIG interface but should be an Array and a 
            // String respectively. 
            if ($key == "useragents" || $key == "method") { 
                continue;
            }

            $apv = $result->device->getInternal($key);

            $expectedType = $property["type"];
            
            $this->assertNotNull($apv, $key);

            $value = $apv->value;

            switch ($expectedType) {
                case "Boolean":
                    if (method_exists($this, 'assertIsBool')) {
                        $this->assertIsBool($value, $key);
                    } else {
                        $this->assertInternalType("boolean", $value, $key);
                    }
                    break;
                case 'String':
                    if (method_exists($this, 'assertIsString')) {
                        $this->assertIsString($value, $key);
                    } else {
                        $this->assertInternalType("string", $value, $key);
                    }
                    break;
                case 'JavaScript':
                    if (method_exists($this, 'assertIsString')) {
                        $this->assertIsString($value, $key);
                    } else {
                        $this->assertInternalType("string", $value, $key);
                    }
                    break;
                case 'Integer':
                    if (method_exists($this, 'assertIsInt')) {
                        $this->assertIsInt($value, $key);
                    } else {
                        $this->assertInternalType("integer", $value, $key);
                    }
                    break;
                case 'Double':
                    if (method_exists($this, 'assertIsFloat')) {
                        $this->assertIsFloat($value, $key);
                    } else {
                        $this->assertInternalType("double", $value, $key);
                    }
                    break;
                case 'Array':
                    if (method_exists($this, 'assertIsArray')) {
                        $this->assertIsArray($value, $key);
                    } else {
                        $this->assertInternalType("array", $value, $key);
                    }
                    break;
                default:
                    $this->fail("expected type for " . $key . " was " . $expectedType);
                    break;
            }
        }
    }

    public function testFailureToMatch()
	{

        include __DIR__ . "/../examples/hash/failureToMatch.php";

		$this->assertTrue(true);

    }

	public function testGettingStarted()
	{

        include __DIR__ . "/../examples/hash/gettingstarted.php";
        
        $this->assertTrue(true);

    }
    
    public function testMetaData()
	{

        include __DIR__ . "/../examples/hash/metadata.php";
        
        $this->assertTrue(true);

    }
    
    public function testWebIntegration()
    {

        include __DIR__ . "/../examples/hash/webIntegration.php";

        $this->assertTrue(true);

    }

    public function testUserAgentClientHints()
    {

        include __DIR__ . "/../examples/hash/userAgentClientHints.php";

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

        try {
           $deviceDetection->setCache(null);
           $this->fail();
        }
        catch (\Exception $ex) {
            $exception = $ex;
        }
        $this->assertNotNull($ex);
        $this->assertEquals(
            Messages::CACHE_ERROR,
            $exception->getMessage());
    }
}
