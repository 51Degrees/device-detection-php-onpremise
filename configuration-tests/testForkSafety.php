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

/**
 * Fork-safety reproduction for issue #38.
 *
 * This is the test that proves why MaxPerformance (in memory) is the only safe
 * profile for PHP on premise. PHP is normally run under a process manager
 * (Apache MPM prefork, php-fpm) that serves each request from a worker process
 * forked from the one the engine was created in.
 *
 * A streaming profile (Balanced, LowMemory, HighPerformance, Default) keeps the
 * data file open and reads from it on demand through a pool of file handles.
 * When the worker is forked, those open handles are inherited by every child,
 * so the parent and the children share the same kernel file descriptions and
 * the same file read positions. Concurrent reads then move each other's file
 * position and return corrupt data, which shows up as wrong detection results
 * or a thrown exception.
 *
 * The MaxPerformance profile loads the whole data file into memory and keeps no
 * open handle, so there is nothing to share across a fork and detection stays
 * correct.
 *
 * The test builds each kind of engine directly (bypassing the module which now
 * forces MaxPerformance), forks several workers that all share the inherited
 * handles, and checks the outcome:
 *   - streaming  -> keeps >= 1 open data-file handle AND corrupts under fork
 *   - in memory  -> keeps 0 open data-file handles AND stays correct under fork
 *
 * Exit code 0 means the contrast was demonstrated. Any other code means the
 * expected behaviour was not observed.
 *
 * Inputs (env vars, with argv fallbacks):
 *   FIFTYONE_DATA_FILE  path to a .hash data file   (argv[1], or the module's configured data_file)
 *   FIFTYONE_WRAPPER    path to the generated FiftyOneDegreesHashEngine.php (argv[2])
 *   FIFTYONE_UA_CSV     path to a CSV/text file of User-Agents, one per line  (argv[3], optional)
 */

const CHILDREN = 4;     // forked workers that all share the inherited handles
const ITERATIONS = 40;  // passes over the User-Agent list per worker
const CRASH_WEIGHT = 250; // a worker killed by a signal is the strongest possible corruption signal

function fail(string $message, int $code = 2): void
{
    fwrite(STDERR, "testForkSafety: {$message}\n");
    exit($code);
}

$dataFile = getenv('FIFTYONE_DATA_FILE') ?: ($argv[1] ?? '');
if ($dataFile === '' && function_exists('ini_get')) {
    $dataFile = (string) ini_get('FiftyOneDegreesHashEngine.data_file');
}
if ($dataFile === '' || !is_file($dataFile)) {
    fail("data file not found (set FIFTYONE_DATA_FILE or pass it as the first argument): '{$dataFile}'");
}

$wrapper = getenv('FIFTYONE_WRAPPER') ?: ($argv[2] ?? '');
if ($wrapper !== '' && is_file($wrapper)) {
    require_once $wrapper;
}

if (!function_exists('pcntl_fork')) {
    fail('the pcntl extension is required to reproduce the fork behaviour');
}
if (!class_exists('EngineHashSwig') || !class_exists('ConfigHashSwig')) {
    fail('the FiftyOneDegreesHashEngine extension and its PHP wrapper must be loaded');
}

// Load a set of User-Agents to detect. The exact values do not matter, only
// that there are enough distinct ones to exercise the data file.
$userAgents = [];
$uaCsv = getenv('FIFTYONE_UA_CSV') ?: ($argv[3] ?? '');
if ($uaCsv !== '' && is_file($uaCsv)) {
    $handle = fopen($uaCsv, 'r');
    while (($line = fgets($handle)) !== false && count($userAgents) < 200) {
        $line = trim($line, " \t\r\n\"");
        if ($line !== '' && stripos($line, 'user-agent') !== 0) {
            $userAgents[] = $line;
        }
    }
    fclose($handle);
}
if (count($userAgents) === 0) {
    $userAgents = [
        'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114',
        'Mozilla/5.0 (Linux; Android 10; SM-G960F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0 Mobile Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Safari/605.1.15',
    ];
}

/**
 * Build an engine directly with the requested profile so the test can exercise
 * a streaming configuration even though the module itself now forces
 * MaxPerformance.
 */
function buildEngine(string $dataFile, string $profile): EngineHashSwig
{
    $config = new ConfigHashSwig();
    switch ($profile) {
        case 'MaxPerformance':
            $config->setMaxPerformance();
            break;
        case 'HighPerformance':
            $config->setHighPerformance();
            break;
        case 'LowMemory':
            $config->setLowMemory();
            break;
        case 'Balanced':
        default:
            $config->setBalanced();
            break;
    }
    // A single handle maximises the contention between forked workers, which
    // makes the streaming corruption reliable rather than occasional.
    $config->setConcurrency(1);
    // Return a result for unmatched User-Agents instead of throwing, so that a
    // consistent non-match is not mistaken for a corrupt read.
    $config->setAllowUnmatched(true);

    return new EngineHashSwig($dataFile, $config, new RequiredPropertiesConfigSwig());
}

/**
 * Count the open operating-system handles that point at the data file. A
 * streaming engine keeps at least one. An in-memory engine keeps none.
 *
 * The open handles are listed under /proc on Linux. Other platforms (for
 * example macOS) do not have it, so the count cannot be determined there and -1
 * is returned to mean "unknown". The corruption check below works everywhere
 * and is the cross-platform proof.
 */
function openDataFileHandles(string $dataFile): int
{
    if (!is_dir('/proc/self/fd')) {
        return -1;
    }
    $target = realpath($dataFile);
    $count = 0;
    foreach (glob('/proc/self/fd/*') ?: [] as $fd) {
        $link = @readlink($fd);
        if ($link !== false && realpath($link) === $target) {
            $count++;
        }
    }
    return $count;
}

function handlesLabel(int $handles): string
{
    return $handles < 0 ? 'unknown (no /proc on this platform)' : (string) $handles;
}

/**
 * Detect every User-Agent ITERATIONS times. A given User-Agent must always
 * resolve to the same device id. Any variation, or any thrown exception, means
 * a read was corrupted. Returns the number of corruption events seen.
 */
function detectionWorker(EngineHashSwig $engine, array $userAgents): int
{
    $corruption = 0;
    $firstResult = [];
    for ($pass = 0; $pass < ITERATIONS; $pass++) {
        foreach ($userAgents as $userAgent) {
            try {
                $deviceId = $engine->process($userAgent)->getDeviceId();
                if (!isset($firstResult[$userAgent])) {
                    $firstResult[$userAgent] = $deviceId;
                } elseif ($firstResult[$userAgent] !== $deviceId) {
                    $corruption++;
                }
            } catch (\Throwable $e) {
                $corruption++;
            }
        }
    }
    return $corruption;
}

/**
 * Fork CHILDREN workers that all share the handles inherited from this process,
 * run them concurrently, and total the corruption they observe. The parent does
 * not detect, so that a worker crash (a signal) is contained and reported
 * rather than taking the whole test down.
 */
function runForkedWorkers(EngineHashSwig $engine, array $userAgents): int
{
    $pids = [];
    for ($i = 0; $i < CHILDREN; $i++) {
        $pid = pcntl_fork();
        if ($pid === -1) {
            fail('pcntl_fork failed');
        }
        if ($pid === 0) {
            $corruption = detectionWorker($engine, $userAgents);
            exit(min($corruption, CRASH_WEIGHT - 1));
        }
        $pids[] = $pid;
    }

    $total = 0;
    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
        if (pcntl_wifexited($status)) {
            $total += pcntl_wexitstatus($status);
        } elseif (pcntl_wifsignaled($status)) {
            // Killed by a signal (for example a segfault from a corrupt read).
            $total += CRASH_WEIGHT;
        }
    }
    return $total;
}

echo "Fork-safety reproduction (issue #38)\n";
echo 'Data file: ' . $dataFile . "\n";
echo 'User-Agents: ' . count($userAgents) . ', workers: ' . CHILDREN . ', iterations: ' . ITERATIONS . "\n\n";

// --- Streaming (non in-memory) profile: expected to be unsafe across a fork ---
$streaming = buildEngine($dataFile, 'Balanced');
$streamingHandles = openDataFileHandles($dataFile);
$streamingCorruption = runForkedWorkers($streaming, $userAgents);
echo "Streaming (Balanced):\n";
echo '  open data-file handles: ' . handlesLabel($streamingHandles) . "\n";
echo "  corruption events across fork: {$streamingCorruption}\n\n";
unset($streaming);
gc_collect_cycles();

// --- In-memory (MaxPerformance) profile: expected to be safe across a fork ---
$inMemory = buildEngine($dataFile, 'MaxPerformance');
$inMemoryHandles = openDataFileHandles($dataFile);
$inMemoryCorruption = runForkedWorkers($inMemory, $userAgents);
echo "In memory (MaxPerformance):\n";
echo '  open data-file handles: ' . handlesLabel($inMemoryHandles) . "\n";
echo "  corruption events across fork: {$inMemoryCorruption}\n\n";
unset($inMemory);
gc_collect_cycles();

$failures = [];
// Handle counts are only asserted where they can be determined (Linux). A
// value of -1 means "unknown" and is skipped.
if ($streamingHandles === 0) {
    $failures[] = 'expected the streaming engine to keep at least one open data-file handle';
}
if ($inMemoryHandles > 0) {
    $failures[] = "expected the in-memory engine to keep no open data-file handle, found {$inMemoryHandles}";
}
// The corruption checks work on every platform and are the core proof.
if ($streamingCorruption < 1) {
    $failures[] = 'expected the streaming profile to corrupt under fork, but no corruption was observed';
}
if ($inMemoryCorruption !== 0) {
    $failures[] = "expected the in-memory profile to be fork-safe, but observed {$inMemoryCorruption} corruption events";
}

if (count($failures) > 0) {
    echo "FAIL\n - " . implode("\n - ", $failures) . "\n";
    exit(1);
}

echo "PASS\n";
echo "A streaming profile keeps open file handles that are shared across a fork and corrupt reads.\n";
echo "The in-memory MaxPerformance profile keeps no file handles and is fork-safe.\n";
echo "This is why MaxPerformance (in memory) is the only safe profile for PHP on premise.\n";
exit(0);
