param (
    [Parameter(Mandatory=$true)]
    [string]$RepoName
)

# This reproduction forks the worker process the way a PHP process manager does
# (Apache MPM prefork, php-fpm), which needs the POSIX pcntl extension. It runs
# on Linux and macOS only.
if ($IsWindows) {
    Write-Output "Skipping fork-safety test: pcntl/fork is not available on Windows."
    return
}

# Load the extension the same way the unit tests do.
$env:PHP_INI_SCAN_DIR = "$([IO.Path]::PathSeparator)$PWD/$RepoName"
$env:FIFTYONE_DATA_FILE = "$PWD/assets/TAC-HashV41.hash"
$env:FIFTYONE_WRAPPER = "$PWD/$RepoName/on-premise/FiftyOneDegreesHashEngine.php"
$env:FIFTYONE_UA_CSV = "$PWD/assets/20000 User Agents.csv"

Write-Output "Running fork-safety reproduction (issue #38)..."
php "$RepoName/configuration-tests/testForkSafety.php"
$code = $LASTEXITCODE

if ($code -eq 0) {
    Write-Output "PASS: streaming profiles fail across a fork, in-memory MaxPerformance is safe."
} elseif ($code -eq 2) {
    # Missing pcntl, extension, or data file. Skip rather than fail, since this
    # is an environment limitation rather than a defect.
    Write-Output "::warning::Skipping fork-safety test (pcntl, extension, or data file unavailable)."
} else {
    throw "fork-safety test failed (exit $code)"
}
