<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2026 51 Degrees Mobile Experts Limited, Davidson House,
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
        // The example is run as a router script for the PHP built-in server
        // (php -S localhost:3000 gettingStartedWeb.php), so every request,
        // including the shared CSS and JS assets, is routed here. Serve the
        // vendored pattern-library assets from the static directory before
        // running detection.
        if ($this->serveStaticAsset($output)) {
            return;
        }

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
        // https://51degrees.com/blog/user-agent-client-hints?utm_source=code&utm_medium=example&utm_campaign=device-detection-php-onpremise&utm_content=examples-onpremise-classes-gettingstartedweb.php&utm_term=processrequest
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

    /**
     * Serve a vendored static asset (the shared examples CSS or JS) when the
     * request targets one. Returns true when the request was handled.
     *
     * @param callable $output
     * @return bool
     */
    private function serveStaticAsset($output)
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $assets = [
            '/css/examples-main.min.css' => ['static/css/examples-main.min.css', 'text/css'],
            '/js/examples.min.js' => ['static/js/examples.min.js', 'application/javascript']
        ];

        if (!isset($assets[$path])) {
            return false;
        }

        [$relativePath, $contentType] = $assets[$path];
        $file = __DIR__ . '/../' . $relativePath;

        if (!is_file($file)) {
            http_response_code(404);

            return true;
        }

        header('Content-Type: ' . $contentType);
        $output(file_get_contents($file));

        return true;
    }
}
