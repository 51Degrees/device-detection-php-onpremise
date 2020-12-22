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

%include "device-detection-cxx/src/hash/hash.i"

// Add a void constructor. This is not to be used, but a workaround for a bug
// in SWIG's PHP 5 generation (PHP 7 does not have this issue).
// Instead of generating:
//     __construct($a, $b=null,$c=null)
// so that __construct($resource) can be called, the code generated is:
//    __construct($a, $b, $c)
// meaning that the valid call to __construct($resource) fails as there are not
// enough arguments. This workaround allows no arguments to be passed, forcing
// SWIG to generate the __construct method with all the arguments being optional.
// Although this creates a constructor which only throws an exception, this
// shouldn't be an issue as the user never calls the constructor directly.
%extend EngineHash {
public:
	EngineHash() {
        throw runtime_error("This constructor should never be used.");
    }

};

%{
    EngineHash *engine;
    ConfigHash *config;
    RequiredPropertiesConfig *properties;

    PHP_INI_BEGIN()
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.data_file", "/usr/lib/php5/51Degrees.hash", PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.required_properties", NULL, PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.performance_profile", NULL, PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.drift", "0", PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.difference", "", PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.allow_unmatched", NULL, PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.use_predictive_graph", NULL, PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.use_performance_graph", NULL, PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.update_matched_useragent", NULL, PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.max_matched_useragent_length", NULL, PHP_INI_ALL, NULL)

    PHP_INI_END()
%}

%immutable engine;
EngineHash *engine;

%minit {

    REGISTER_INI_ENTRIES();
    char *filePath = INI_STR("FiftyOneDegreesHashEngine.data_file");
    char *propertyList = INI_STR("FiftyOneDegreesHashEngine.required_properties");
    char *performanceProfile = INI_STR("FiftyOneDegreesHashEngine.performance_profile");
    int drift = INI_INT("FiftyOneDegreesHashEngine.drift");
    int difference = INI_INT("FiftyOneDegreesHashEngine.difference");
    char *allowUnmatched = INI_STR("FiftyOneDegreesHashEngine.allow_unmatched");
    char *usePredictiveGraph = INI_STR("FiftyOneDegreesHashEngine.use_predictive_graph");
    char *usePerformanceGraph = INI_STR("FiftyOneDegreesHashEngine.use_performance_graph");
    char *updateMatchedUa = INI_STR("FiftyOneDegreesHashEngine.update_matched_useragent");
    int maxUaLength= INI_INT("FiftyOneDegreesHashEngine.max_matched_useragent_length");

    config = new ConfigHash();
    // Set the performance profile.
    if (performanceProfile != NULL) {
        if (strcmp("HighPerformance", performanceProfile) == 0) {
            config->setHighPerformance();              
        }
        else if (strcmp("HighPerformance", performanceProfile) == 0) {
            config->setHighPerformance();              
        }
        else if (strcmp("Balanced", performanceProfile) == 0) {
            config->setBalanced();              
        }
        else if (strcmp("BalancedTemp", performanceProfile) == 0) {
            config->setBalancedTemp();              
        }
        else if (strcmp("LowMemory", performanceProfile) == 0) {
            config->setLowMemory();              
        }
        else if (strcmp("MaxPerformance", performanceProfile) == 0) {
            config->setMaxPerformance();              
        }
    }
    // Set the drift.
    if (drift != 0) {
        config->setDrift(drift);
    }
    // Set the difference.
    if (difference != 0) {
        config->setDifference(difference);
    }
    // Set allow unmatched.
    if (allowUnmatched != NULL) {
        config->setAllowUnmatched(strcmp(allowUnmatched, "true") == 0);
    }
    // Set use predictive graph.
    if (usePredictiveGraph != NULL) {
        config->setUsePredictiveGraph(strcmp(usePredictiveGraph, "true") == 0);
    }
    // Set use performance graph.
    if (usePerformanceGraph != NULL) {
        config->setUsePerformanceGraph(strcmp(usePerformanceGraph, "true") == 0);
    }
    // Set update matched User-Agent.
	if (updateMatchedUa != NULL) {
		config->setUpdateMatchedUserAgent(strcmp(updateMatchedUa, "true") == 0);
	}
	// Set max matched User-Agent length.
	if (maxUaLength != 0) {
		config->setMaxMatchedUserAgentLength(maxUaLength);
	}
    // Set the required properties.
    if (propertyList != NULL) {
        properties = new RequiredPropertiesConfig(propertyList);
    }
    else {
        properties = new RequiredPropertiesConfig();
    }
    
    engine = new EngineHash(
        filePath,
        config,
        properties);
}

%mshutdown {

    delete engine;
    delete properties;
    delete config;
}