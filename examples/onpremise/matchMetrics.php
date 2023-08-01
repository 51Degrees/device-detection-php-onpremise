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

/**
 * @example onpremise/matchMetrics.php
 * The example illustrates the various metrics that can be obtained about the device detection
 * process, for example, the degree of certainty about the result. Running the example outputs
 * those properties and values..
 *
 * There is a (discussion)[https://51degrees.com/documentation/_device_detection__hash.html#DeviceDetection_Hash_DataSetProduction_Performance]
 * of metrics and controlling performance on our web site. See also the (performance options)
 * [https://51degrees.com/documentation/_device_detection__features__performance_options.html]
 * page.
 * # Location
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/onpremise/matchMetrics.php). 
 *
 */

require_once(__DIR__ . "/exampleUtils.php");
require_once(__DIR__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\core\Logger;

class MatchMetrics
{
    /**
     * Run the example
     * @param showDescs show descriptions of properties
     * @param logger for pipeline logging
     * @param out an output stream
     */
    public function run($showDescs, $logger, callable $output)
    {
        $engine = new DeviceDetectionOnPremise(array(
            // You can improve matching performance by specifying only those
            // properties you wish to use. If you don't specify any properties
            // you will get all those available in the data file tier that
            // you have used. The free "Lite" tier contains fewer than 20.
            // Since we are specifying properties here, we will only see
            // those properties, along with the match metric properties
            // in the output.
            // Set the values required in the php.ini file using the
            // FiftyOneDegreesHashEngine.required_properties option.
            // If using the full on-premise data file more properties will be
            // present in the data file. See https://51degrees.com/pricing
        ));
        $pipeline = (new PipelineBuilder())
            ->add($engine)
            ->addLogger($logger)
            ->build();

        ExampleUtils::checkDataFile($pipeline->getElement("device"), $logger);
    
        // FlowData is a data structure that is used to convey
        // information required for detection and the results of the
        // detection through the pipeline.
        // Information required for detection is called "evidence"
        // and usually consists of a number of HTTP Header field
        // values, in this case represented by a dictionary of header
        // name/value entries.
        $data = $pipeline->createFlowData();

        // Process a single evidence to retrieve the values
        // associated with the user-agent and other evidence such as sec-ch-* for the
        // selected properties.
        $data->evidence->setArray($this->evidenceValues);
        $data->process();

        // Now that it's been processed, the flow data will have
        // been populated with the result. In this case, we want
        // information about the device, which we can get by
        // asking for a result matching named "device"
        $device = $data->device;

        $output("--- Compare evidence with what was matched ---");
        $output("");
        $output("Evidence");
        // output the evidence in reverse value length order
        uasort($this->evidenceValues, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        foreach ($this->evidenceValues as $key => $value)
        {
            $output("    $key: $value");
        }

        // Obtain the matched User-Agents: the matched substrings in the
        // User-Agents are separated with underscores - output in forward length order.
        $output("Matches");
        $useragents = $device->useragents->value;
        usort($useragents, function($a, $b) {
            return strlen($a) - strlen($b);
        });
        foreach($useragents as $useragent)
        {
            $output("    Matched User-Agent: $useragent");
        }

        $output("");

        $output("--- Listing all available properties, by component, by property " .
            "name ---");
        $output("For a discussion of what the match properties mean, see: " .
            "https://51degrees.com/documentation/_device_detection__hash" .
            ".html#DeviceDetection_Hash_DataSetProduction_Performance");

        // retrieve the available properties from the hash engine. The properties
        // available depends on
        // a) the use of FiftyOneDegreesHashEngine.required_properties option in the
        // php.ini file.
        // which controls which properties will be extracted, and also affects
        // the performance of extraction
        // b) the tier of data file being used. The Lite data file contains fewer
        // than 20 of the >200 available properties
        $availableProperties =
            $pipeline->getElement("device")->getProperties();


        // create a Map keyed on the component name of the properties available
        // components being hardware, browser, OS and Crawler.
        $categoryMap = array();
        foreach ($availableProperties as $property)
        {
            $component = $property["component"];
            if (array_key_exists($component, $categoryMap) === false)
            {
                $categoryMap[$component] = array();
            }
            $categoryMap[$component][] = $property;
        }

        // iterate the map created above
        foreach ($categoryMap as $component => $componentProperties)
        {
            $output($component);
            foreach ($componentProperties as $property)
            {
                $name = $property["name"];
                $description = $property["description"];

                // while we get the available properties and their metadata from the
                // pipeline we get the values for the last detection from flowData
                $value = $device->$name;

                // output property names, values and descriptions
                // some property values are lists. $property["isList"] will be true
                if ($value->hasValue && is_array($value->value)) {
                    $output("    $name: ".count($value->value)." Values");
                    foreach ($value->value as $x)
                    {
                        $output("        : $x");
                    }
                }
                else
                {
                    $output("    $name: $value->value");
                }
                if ($showDescs === true) {
                    $output("        $description");
                }
            }
        }
    }

    // Evidence values from a windows 11 device using a browser
    // that supports User-Agent Client Hints.
    private $evidenceValues = array(
        "header.user-agent" =>
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) ".
            "AppleWebKit/537.36 (KHTML, like Gecko) ".
            "Chrome/98.0.4758.102 Safari/537.36",
        "header.sec-ch-ua-mobile" => "?0",
        "header.sec-ch-ua" =>
            "\" Not A; Brand\";v=\"99\", \"Chromium\";v=\"98\", ".
            "\"Google Chrome\";v=\"98\"",
        "header.sec-ch-ua-platform" => "\"Windows\"",
        "header.sec-ch-ua-platform-version" => "\"14.0.0\"");
};

// Only declare and call the main function if this is being run directly.
// This prevents main from being run where examples are run as part of
// PHPUnit tests.
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
{
    function main($argv)
    {
        // Configure a logger to output to the console.
        $logger = new Logger("info");

        (new MatchMetrics())->run(false, $logger, ["ExampleUtils", "output"]);
    }

    main(isset($argv) ? array_slice($argv, 1) : null);
}