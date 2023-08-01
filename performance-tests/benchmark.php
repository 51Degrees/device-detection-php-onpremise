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

require(__DIR__ . "/../vendor/autoload.php");

use fiftyone\pipeline\devicedetection\DeviceDetectionOnPremise;
use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\core\JavascriptBuilderElement;
use fiftyone\pipeline\core\JsonBundlerElement;

function process($pipeline, $server, $cookies = null, $query = null) {
    // We create the flowData object that is used to add evidence to and read
    // data from
    $flowData = $pipeline->createFlowData();

    // We set headers, cookies and more information from the web request
    $flowData->evidence->setFromWebRequest($server, $cookies, $query);

    // Now we process the flowData
    $result = $flowData->process();

    $device = $flowData->device;

    return "IsMobile: " . (isset($device->ismobile) && $device->ismobile->hasValue ? $device->ismobile->value : "Unknown (" . (isset($device->ismobile) ? $device->ismobile->noValueMessage : "property unavailable" ) .")") . "\n";
}

$device = new DeviceDetectionOnPremise();
$builder = new PipelineBuilder(array("addJavaScriptBuilder" => false));
$pipeline = $builder->add($device)->build();

$server = array(
    "HTTP_ACCEPT" => "*/*",
    "HTTP_HOST" => "127.0.0.1:3000",
    "HTTP_USER_AGENT" => "PLACEHOLDER",
    "REMOTE_ADDR" => "127.0.0.1",
    "REQUEST_METHOD" => "GET",
    "REQUEST_URI" => "/process",
    "SERVER_ADDR" => "127.0.0.1",
    "SERVER_NAME" => "127.0.0.1",
    "SERVER_PORT" => "3000",
    "SERVER_PROTOCOL" => "HTTP/1.1",
);


if ($argc < 2) {
    fwrite(STDERR, "Usage: $argv[0] [-o|--output] /path/to/user_agents_file.csv\n");
    exit(1);
}

$userAgentsFile = null;
$output = false;

for ($i = 1; $i < $argc; ++$i) {
    if ($argv[$i] == "-o" || $argv[$i] == "--output") {
        $output = true;
    } else {
        $userAgentsFile = $argv[$i];
    }
}

$userAgents = file($userAgentsFile, FILE_IGNORE_NEW_LINES) or exit(1);

$startTime = hrtime(true);
foreach ($userAgents as $ua) {
    $server["HTTP_USER_AGENT"] = $ua;
    process($pipeline, $server);
}
$timeNS = hrtime(true) - $startTime;
$timeMS = $timeNS / 1e6;
$timeS = $timeMS / 1e3;

if ($output) {
    echo json_encode(array(
        "Detections" => count($userAgents),
        "DetectionsPerSecond" => count($userAgents) / $timeS,
        "RuntimeSeconds" => $timeS,
        "AvgMillisecsPerDetection" => $timeMS / count($userAgents),
    ), JSON_PRETTY_PRINT), "\n";
}
