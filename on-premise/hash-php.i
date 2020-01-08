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

%{
    EngineHash *engine;
    ConfigHash *config;
    RequiredPropertiesConfig *properties;

    PHP_INI_BEGIN()
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.data_file", "/usr/lib/php5/51Degrees.trie", PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.required_properties", NULL, PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.performance_profile", NULL, PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.drift", "0", PHP_INI_ALL, NULL)
    PHP_INI_ENTRY("FiftyOneDegreesHashEngine.difference", "", PHP_INI_ALL, NULL)
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