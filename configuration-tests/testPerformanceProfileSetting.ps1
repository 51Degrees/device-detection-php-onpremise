param (
    [Parameter(Mandatory=$true)]
    [string]$BaseIniFilePath
)

$config = Get-Content $BaseIniFilePath

$env:PHP_INI_SCAN_DIR = "$([IO.Path]::PathSeparator)$PSScriptRoot"

trap {
    Write-Output "Server logs:"
    Get-Content $PSScriptRoot/log.txt -ErrorAction SilentlyContinue
}

# A high concurrency combined with a streaming performance profile used to
# crash the server (see issue #38). The engine now always uses the in-memory
# MaxPerformance profile, so the server must start for every profile, a warning
# must be issued when a profile other than MaxPerformance is requested, and no
# warning must be issued for MaxPerformance.
$testProfiles = "Default", "Balanced", "BalancedTemp", "LowMemory", "HighPerformance", "MaxPerformance"

Write-Output "Running performance_profile configuration tests..."
foreach ($profile in $testProfiles) {
    $config + @(
        "FiftyOneDegreesHashEngine.concurrency = 23"
        "FiftyOneDegreesHashEngine.performance_profile = $profile"
        "error_reporting = E_ALL"
        "display_errors = stderr"
        "display_startup_errors = On"
    ) | Out-File $PSScriptRoot/php.ini

    # 1) The built-in server must start and serve under the streaming profile
    #    plus high concurrency that used to crash it, because the engine now
    #    forces the in-memory MaxPerformance profile.
    $php = php -S 127.0.0.1:3002 -t $PSScriptRoot *>$PSScriptRoot/log.txt &

    # Wait for the server to respond. A crash shows up as the request never
    # succeeding within the timeout.
    $started = $false
    foreach ($attempt in 1..30) {
        Start-Sleep -Milliseconds 500
        try {
            if ((Invoke-WebRequest http://127.0.0.1:3002 -TimeoutSec 10).StatusCode -eq 200) {
                $started = $true
                break
            }
        } catch {
            # Not ready yet, or the server crashed. Retry until the timeout.
        }
    }

    Remove-Job $php -Force

    if (-not $started) {
        Write-Output "FAIL: performance_profile = $profile (server did not start)"
        throw "Test failed: server did not return HTTP 200 for profile $profile"
    }
    Write-Output "PASS: performance_profile = $profile (server started)"

    # 2) The module issues a startup warning when a profile other than
    #    MaxPerformance is configured. Module-init warnings surface on stderr in
    #    CLI mode but not under the built-in server, so check via a short CLI run
    #    that loads the same php.ini.
    $cliOutput = (php -r "exit(0);" 2>&1) | Out-String
    $warned = $cliOutput -match "performance_profile.*is ignored"

    if ($profile -eq "MaxPerformance") {
        if ($warned) {
            Write-Output $cliOutput
            throw "Did not expect a warning for performance_profile = $profile"
        }
        Write-Output "PASS: performance_profile = $profile (no warning, as expected)"
    } else {
        if (-not $warned) {
            Write-Output $cliOutput
            throw "Expected a warning for performance_profile = $profile"
        }
        Write-Output "PASS: performance_profile = $profile (warning issued)"
    }
}
Write-Output "OK"
