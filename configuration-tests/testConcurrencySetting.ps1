param (
    [Parameter(Mandatory=$true)]
    [string]$BaseIniFilePath
)

$config = Get-Content $BaseIniFilePath

$env:PHP_INI_SCAN_DIR = "$([IO.Path]::PathSeparator)$PSScriptRoot"

$testValues = "10", "0", "1", "-3"

Write-Output "Running concurrency configuration tests..."
foreach ($value in $testValues) {
    $config + "`nFiftyOneDegreesHashEngine.concurrency = $value" | Out-File $PSScriptRoot/php.ini

    $php = php -S 127.0.0.1:3002 -t $PSScriptRoot *>$PSScriptRoot/log.txt &
    sleep 1

    $response = Invoke-WebRequest http://127.0.0.1:3002
    if ($response.StatusCode -eq 200) {
        Write-Output "PASS: concurrency = $value"
    } else {
        Write-Output "FAIL: concurrency = $value"
        Write-Output "Server returned code ${response.StatusCode}. Server logs:"
        Get-Content $PSScriptRoot/log.txt
        throw "test failed"
    }

    Remove-Job $php -Force
}
Write-Output "OK"
