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

class Constants {

	const ALL_PROPERTIES = null;
	const BASE_PROPERTIES = "HardwareVendor,HardwareName,DeviceType,PlatformVendor,PlatformName,PlatformVersion,BrowserVendor,BrowserName,BrowserVersion";
    const PLATFORM_PROPERTIES = self::BASE_PROPERTIES . ",SetHeaderPlatformAccept-CH";
	const HARDWARE_PROPERTIES = self::BASE_PROPERTIES . ",SetHeaderHardwareAccept-CH";
	const BROWSER_PROPERTIES = self::BASE_PROPERTIES . ",SetHeaderBrowserAccept-CH";
	
	const CHROME_UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
			. "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36";
	const EDGE_UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) "
	                . "Chrome/95.0.4638.69 Safari/537.36 Edg/95.0.1020.44";
	const FIREFOX_UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64;"
			. " rv:94.0) Gecko/20100101 Firefox/94.0";
	const SAFARI_UA = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_1 "
			. "like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 "
			. "Mobile/15E148 Safari/604.1";
	const CURL_UA = "curl/7.80.0";

	const SUPER_ACCEPT_CH = array("Sec-CH-UA", "Sec-CH-UA-Full-Version", "Sec-CH-UA-Model", 
                            "Sec-CH-UA-Mobile", "Sec-CH-UA-Arch", "Sec-CH-UA-Platform",  
                            "Sec-CH-UA-Platform-Version");
	const PLATFORM_ACCEPT_CH = array("SEC-CH-UA-Platform", "SEC-CH-UA-Platform-Version");
	const HARDWARE_ACCEPT_CH = array("SEC-CH-UA-Model", "SEC-CH-UA-Mobile", "SEC-CH-UA-Arch");
	const BROWSER_ACCEPT_CH = array("SEC-CH-UA", "SEC-CH-UA-Full-Version");
	const EMPTY_ACCEPT_CH = array();
	
	const URL = "http://localhost:3000/";
    const UA_HEADER = "User-Agent: ";			
}
