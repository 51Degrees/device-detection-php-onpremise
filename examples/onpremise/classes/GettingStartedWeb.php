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

use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\core\Utils;

class GettingStartedWeb
{
    public function run($configFile, $logger, $output)
    {
        $pipeline = (new PipelineBuilder())
            ->addLogger($logger)
            ->buildFromConfig($configFile);

        $this->processRequest($pipeline, $output);
    }

    private function processRequest($pipeline, $output)
    {
        // Create the flowdata object.
        $flowData = $pipeline->createFlowData();

        // Add any information from the request (headers, cookies and additional
        // client side provided information)
        $flowData->evidence->setFromWebRequest();

        // Process the flowdata
        $flowData->process();

        // Some browsers require that extra HTTP headers are explicitly
        // requested. So set whatever headers are required by the browser in
        // order to return the evidence needed by the pipeline.
        // More info on this can be found at
        // https://51degrees.com/blog/user-agent-client-hints
        Utils::setResponseHeader($flowData);

        // First we make a JSON route that will be called from the client side
        // and will return a JSON encoded property database using any additional
        // evidence provided by the client.
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/json') {
            header('Content-Type: application/json');
            $output(json_encode($flowData->jsonbundler->json));

            return;
        }

        include_once __DIR__ . '/../static/page.php';
    }
}
