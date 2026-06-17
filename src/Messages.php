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

namespace fiftyone\pipeline\devicedetection;

/**
 * Messages which may be reused by the API.
 */
class Messages
{
    /**
     * Error message returned when a cache is configured for the on-premise
     * engine.
     */
    public const CACHE_ERROR =
        'A results cache cannot be configured in the on-premise Hash ' .
        'engine. The overhead of having to manage native object ' .
        'lifetimes when a cache is enabled outweighs the benefit of the ' .
        'cache.';

    /**
     * Error message returned when a performance profile other than
     * MaxPerformance is configured for the on-premise engine. The placeholder
     * is the profile that was configured.
     */
    public const PERFORMANCE_PROFILE_ERROR =
        "The on-premise Hash engine for PHP can only run with the in-memory " .
        "'MaxPerformance' performance profile, but '%s' was configured. PHP is " .
        "typically run under a process manager (Apache MPM prefork, php-fpm) " .
        "that forks the worker process, and any other profile keeps the data " .
        "file open and shares those file handles across the fork, which " .
        "corrupts detection. Remove the " .
        "'FiftyOneDegreesHashEngine.performance_profile' setting, or set it to " .
        "'MaxPerformance'.";
}
