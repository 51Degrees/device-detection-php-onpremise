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

/**
 * @example onpremise/matchMetrics.php
 * The example illustrates the various metrics that can be obtained about the device detection
 * process, for example, the degree of certainty about the result. Running the example outputs
 * those properties and values.
 *
 * There is a [discussion](https://51degrees.com/documentation/_device_detection__hash.html#DeviceDetection_Hash_DataSetProduction_Performance)
 * of metrics and controlling performance on our website. See also the (performance options)
 * [https://51degrees.com/documentation/_device_detection__features__performance_options.html]
 * page.
 * # Location
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php-onpremise/blob/master/examples/onpremise/matchMetrics.php).
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use fiftyone\pipeline\core\Logger;
use fiftyone\pipeline\devicedetection\examples\onpremise\classes\ExampleUtils;
use fiftyone\pipeline\devicedetection\examples\onpremise\classes\MatchMetrics;

// Only declare and call the main function if this is being run directly.
// This prevents main from being run where examples are run as part of
// PHPUnit tests.
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    function main($argv)
    {
        // Configure a logger to output to the console.
        $logger = new Logger('info');

        (new MatchMetrics())->run(false, $logger, [ExampleUtils::class, 'output']);
    }

    main(isset($argv) ? array_slice($argv, 1) : null);
}
