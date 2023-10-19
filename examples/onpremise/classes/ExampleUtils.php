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
class ExampleUtils
{
    // If data file is older than this number of days then a warning will 
    // be displayed.
    const DATA_FILE_AGE_WARNING = 30;

    public static function output($message)
    {
        if (php_sapi_name() == "cli")
        {
            echo $message."\n";
        }
        else
        {
            echo "<pre>$message\n</pre>";
        }
    }

    public static function getDataFileDate($engine)
    {
        $date = $engine->engine->getPublishedTime();
        return mktime(0, 0, 0, $date->getMonth(), $date->getDay(), $date->getYear());
    }

    public static function dataFileIsOld($engine)
    {
        $dataFileDate = ExampleUtils::getDataFileDate($engine);
        return strtotime("today") >  $dataFileDate + mktime(0, 0, 0, 0, ExampleUtils::DATA_FILE_AGE_WARNING, 0);
    }

    public static function getDataFileTier($engine)
    {
        return $engine->engine->getProduct();
    }

    /**
     * Get the 'engine' element within the pipeline that
     * performs device detection. We can use this to get
     * details about the data file as well as meta-data
     * describing things such as the available properties.
     */
    public static function checkDataFile($engine, $logger)
    {
        if (isset($engine))
        {
            $dataFileDate = ExampleUtils::getDataFileDate($engine);
            $logger->log("info",
                "Using a '".ExampleUtils::getDataFileTier($engine)."' data file created ".
                "'".date("d/m/Y", $dataFileDate)."' from location ".
                "'".$engine->engine->getDataFilePath()."'");

            if (ExampleUtils::dataFileIsOld($engine))
            {
                $logger->log("warn",
                    "This example is using a data file ".
                    "that is more than '".ExampleUtils::DATA_FILE_AGE_WARNING."' days ".
                    "old. A more recent data file may be needed to ".
                    "correctly detect the latest devices, browsers, ".
                    "etc. The latest lite data file is available from ".
                    "the device-detection-data repository on GitHub ".
                    "https://github.com/51Degrees/device-detection-data. ".
                    "Find out about the Enterprise data file, which ".
                    "includes automatic updates, on our pricing page: ".
                    "https://51degrees.com/pricing");
            }

            if (ExampleUtils::getDataFileTier($engine) === "Lite")
            {
                $logger->log("warn",
                    "This example is using the 'Lite' ".
                    "data file. This is used for illustration, and ".
                    "has limited accuracy and capabilities. Find ".
                    "out about the Enterprise data file on our ".
                    "pricing page: https://51degrees.com/pricing");
            }
        }
    }

    private static function getEnvVariable($name)
    {
        $env = getenv();
        if (isset($env[$name]))
        {
            return $env[$name];
        }
        else
        {
            return "";
        }
    }

    public static function getHumanReadable($device, $name)
    {
        try
        {
            $value = $device->$name;
            if ($value->hasValue)
            {
                if (is_array($value->value))
                {
                    return implode(", ", $value->value);
                }
                else
                {
                    return $value->value;
                }
            }
            else
            {
                return "Unknown (".$value->noValueMessage.")";
            }
        }
        catch (\Exception $e)
        {
            return "Property not found in the current data file.";
        }
    }

    public static function containsAcceptCh()
    {
        foreach (headers_list() as $header)
        {
            $parts = explode(": ", $header);
            if (strtolower($parts[0]) === "accept-ch")
            {
                return true;
            }
        }
        return false;
    }
}
