param (
    [Parameter(Mandatory=$true)]
    [string]$BaseIniFilePath
)

$config = Get-Content $BaseIniFilePath

$env:PHP_INI_SCAN_DIR = "$([IO.Path]::PathSeparator)$PSScriptRoot"

trap {
    Write-Output "Server logs:"
    Get-Content $PSScriptRoot/log.txt
}

$testValues = "10", "0", "1", "-3"

Write-Output "Running concurrency configuration tests..."
foreach ($value in $testValues) {
    $config + "`nFiftyOneDegreesHashEngine.concurrency = $value" | Out-File $PSScriptRoot/php.ini

    $php = php -S 127.0.0.1:3002 -t $PSScriptRoot *>$PSScriptRoot/log.txt &

    # Wait for the server to respond. The engine now loads the whole data file
    # into memory (see issue #38), so startup can take longer than a single
    # second, and a crash shows up as the request never succeeding.
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

    if ($started) {
        Write-Output "PASS: concurrency = $value"
    } else {
        Write-Output "FAIL: concurrency = $value"
        throw "Test failed: server did not return HTTP 200"
    }
}
Write-Output "OK"
