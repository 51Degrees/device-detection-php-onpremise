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

use fiftyone\pipeline\devicedetection\tests\classes\Constants;
use fiftyone\pipeline\devicedetection\tests\classes\Process;
use PHPUnit\Framework\TestCase;

/**
 * @requires OS Linux
 */
class UACHHashTests extends TestCase
{
    public static $process;

    public function tearDown(): void
    {
        // stop server
        if (self::$process->stop()) {
            echo "\nProcess stopped for User Agent Client Hints On-Premise Web example. \n";
        }
    }

    public static function setUpTest($properties)
    {
        // escape properties before passing them to the shell
        $propertiesShell = escapeshellarg((string) $properties);

        // start server
        self::$process = new Process("php -dFiftyOneDegreesHashEngine.required_properties={$propertiesShell} -S localhost:3000 examples/onpremise/userAgentClientHints-Web.php");
        self::$process->start();
        if (self::$process->status()) {
            shell_exec('lsof -i tcp:3000 1>/dev/null 2>&1');
            sleep(1);
            echo "User Agent Client Hints On-Premise Web example has started running.\n";
        } else {
            throw new \Exception("Could not start the User Agent Client Hints On-Premise Web example.\n");
        }
    }

    // Data Provider for testAcceptCH
    public static function provider_testAcceptCH()
    {
        $properties = [
            Constants::ALL_PROPERTIES, 
            Constants::PLATFORM_PROPERTIES, 
            Constants::HARDWARE_PROPERTIES, 
            Constants::BROWSER_PROPERTIES, 
            Constants::BASE_PROPERTIES
        ];

        // TODO - Edge removed from test until cloud has been updated
        // with new data file.
        $userAgents = [
            Constants::CHROME_UA, 
            /* Constants::EDGE_UA, */ 
            Constants::FIREFOX_UA, 
            Constants::SAFARI_UA, 
            Constants::CURL_UA
        ];

        // Get all combinations of keys and uas and determine
        // which values we are expecting to see in Accept-CH.

        $testParameters = [];
        foreach ($properties as $property) {
            foreach ($userAgents as $ua) {
                if ($ua == Constants::CHROME_UA || $ua == Constants::EDGE_UA) {
                    if ($property == Constants::BROWSER_PROPERTIES) {
                        $testParameters[] = [$ua, $property, Constants::BROWSER_ACCEPT_CH];
                    } elseif ($property == Constants::HARDWARE_PROPERTIES) {
                        $testParameters[] = [$ua, $property, Constants::HARDWARE_ACCEPT_CH];
                    } elseif ($property == Constants::PLATFORM_PROPERTIES) {
                        $testParameters[] = [$ua, $property, Constants::PLATFORM_ACCEPT_CH];
                    } elseif ($property == Constants::ALL_PROPERTIES) {
                        $testParameters[] = [$ua, $property, Constants::SUPER_ACCEPT_CH];
                    } else {
                        $testParameters[] = [$ua, $property, Constants::EMPTY_ACCEPT_CH];
                    }
                } else {
                    $testParameters[] = [$ua, $property, Constants::EMPTY_ACCEPT_CH];
                }
            }
        }

        return $testParameters;
    }

    /**
     * Tests response header value to set in Accept-CH response header.
     *
     * @dataProvider provider_testAcceptCH
     * @param mixed $userAgent
     * @param mixed $properties
     * @param mixed $expectedValue
     */
    public function testAcceptCH($userAgent, $properties, $expectedValue)
    {
        // setup test
        self::setUpTest($properties);

        $requestHeaders = Constants::UA_HEADER . $userAgent . '\r\n';

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $requestHeaders
            ]
        ]);

        $data = @file_get_contents(Constants::URL, false, $context);
        $responseHeaders = self::parseHeaders($http_response_header);

        $this->assertSame(200, $responseHeaders['response_code']);

        if (is_null($expectedValue) || count($expectedValue) == 0) {
            $this->assertArrayNotHasKey('Accept-CH', $responseHeaders);
        } else {
            $this->assertArrayHasKey('Accept-CH', $responseHeaders);

            $actualValue = explode(',', $responseHeaders['Accept-CH']);

            // We don't require the expected list of values to match exactly, as the headers
            // used by detection change over time. However, we do make sure that the most
            // critical ones are present in Accept-CH.
            foreach ($expectedValue as $value) {
                $lowerCasedActualArray = array_map('strtolower', array_map('trim', $actualValue));
                $this->assertContains(strtolower($value), $lowerCasedActualArray);
            }
        }
    }

    /**
     * Converts response headers string to an indexed array.
     *
     * @param array $headers
     * @return array
     */
    private static function parseHeaders($headers)
    {
        $head = [];
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1])) {
                $head[trim($t[0])] = trim($t[1]);
            } else {
                $head[] = $v;
                if (preg_match('#HTTP/[0-9\\.]+\\s+([0-9]+)#', $v, $out)) {
                    $head['response_code'] = intval($out[1]);
                }
            }
        }

        return $head;
    }
}
