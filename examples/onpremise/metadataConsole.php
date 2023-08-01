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
 * @example onpremise/metadataConsole.php
 * The device detection data file contains meta data that can provide additional information
 * about the various records in the data model.
 * This example shows how to access this data and display the values available.
 * 
 * The device detection data file contains meta data that can provide additional information
 * about the various records in the data model.
 * This example shows how to access this data and display the values available.
 * 
 * A list of the properties will be displayed, along with some additional information about each
 * property.
 * 
 * Finally, the evidence keys that are accepted by device detection are listed. These are the 
 * keys that, when added to the evidence collection in flow data, could have some impact on the
 * result returned by device detection.
 * 
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/onpremise/metadataConsole.php). 
 * 
 * @include{doc} example-require-datafile.txt
 * 
 * Required Composer Dependencies:
 * - 51degrees/fiftyone.devicedetection
 */

require_once(__DIR__ . "/exampleUtils.php");
require_once(__DIR__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\core\Logger;

class MetaDataConsole
{
    /**
     * In this example, we use the DeviceDetectionPipelineBuilder
     * and configure it in code. For more information about
     * pipelines in general see the documentation at
     * http://51degrees.com/documentation/4.3/_concepts__configuration__builders__index.html
     */
    public function run($logger, callable $output)
    {
        // Build a new on-premise Hash engine with the configuration in the PHP ini file.
        // Note that there is no need to construct a complete pipeline in order to access
        // the meta-data.
        // If you already have a pipeline and just want to get a reference to the engine 
        // then you can use `$engine = $pipeline->getElement("device");`
        $engine = new DeviceDetectionOnPremise();

        $this->outputEvidenceKeyDetails($engine, $output);
        $this->outputProperties($engine, $output);
        ExampleUtils::checkDataFile($engine, $logger);
    }

    private function outputEvidenceKeyDetails($engine, callable $output)
    {
        $output("");
        if (is_a($engine->getEvidenceKeyFilter(), "fiftyone\\pipeline\\core\\BasicListEvidenceKeyFilter"))
        {
            // If the evidence key filter extends BasicListEvidenceKeyFilter then we can
            // display a list of accepted keys.
            $filter = $engine->getEvidenceKeyFilter();
            $output("Accepted evidence keys:");
            foreach ($filter->getList() as $key)
            {
                $output("\t$key");
            }
        }
        else
        {
            output("The evidence key filter has type " .
                $engine->getEvidenceKeyFilter().". As this does not extend " .
                "BasicListEvidenceKeyFilter, a list of accepted values cannot be " .
                "displayed. As an alternative, you can pass evidence keys to " .
                "filter->filterEvidenceKey(string) to see if a particular key will be included " .
                "or not.");
            output("For example, header.user-agent is " .
                ($engine->getEvidenceKeyFilter().filterEvidenceKey("header.user-agent") ? "" : "not ") .
                "accepted.");
        }
    }

    private function outputProperties($engine, callable $output)
    {
        foreach ($engine->getProperties() as $property)
        {
            // Output some details about the property.
            $output("Property - ".$property["name"] . " " .
                "[Category: ".$property["category"]."] (".$property["type"].")");
        }
    }
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

        (new MetaDataConsole())->run($logger, ["ExampleUtils", "output"]);
    }

    main(isset($argv) ? array_slice($argv, 1) : null);
}