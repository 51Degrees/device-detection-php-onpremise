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

namespace fiftyone\pipeline\devicedetection\examples\onpremise\classes;

use fiftyone\pipeline\core\BasicListEvidenceKeyFilter;
use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;

class MetaDataConsole
{
    /**
     * In this example, we use the DeviceDetectionPipelineBuilder
     * and configure it in code. For more information about
     * pipelines in general see the documentation at
     * http://51degrees.com/documentation/4.3/_concepts__configuration__builders__index.html.
     *
     * @param \fiftyone\pipeline\core\Logger $logger
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
        $output('');
        if ($engine->getEvidenceKeyFilter() instanceof BasicListEvidenceKeyFilter) {
            // If the evidence key filter extends BasicListEvidenceKeyFilter then we can
            // display a list of accepted keys.
            $filter = $engine->getEvidenceKeyFilter();
            $output('Accepted evidence keys:');
            foreach ($filter->getList() as $key) {
                $output("\t{$key}");
            }
        } else {
            $output('The evidence key filter has type ' .
                $engine->getEvidenceKeyFilter() . '. As this does not extend ' .
                'BasicListEvidenceKeyFilter, a list of accepted values cannot be ' .
                'displayed. As an alternative, you can pass evidence keys to ' .
                'filter->filterEvidenceKey(string) to see if a particular key will be included ' .
                'or not.');
            $output('For example, header.user-agent is ' .
                ($engine->getEvidenceKeyFilter()->filterEvidenceKey('header.user-agent') ? '' : 'not ') .
                'accepted.');
        }
    }

    private function outputProperties($engine, callable $output)
    {
        foreach ($engine->getProperties() as $property) {
            // Output some details about the property.
            $output('Property - ' . $property['name'] . ' ' .
                '[Category: ' . $property['category'] . '] (' . $property['type'] . ')');
        }
    }
}
