param (
    [Parameter(Mandatory=$true)]
    [string]$RepoName
)

Push-Location $RepoName/on-premise
try {
    phpize || $(throw "phpize failed")
    ./configure || $(throw "configure failed")
    make "-j$([Environment]::ProcessorCount)" || $(throw "make failed")
} finally {
    Pop-Location
}

@"
extension = "$PWD/$RepoName/on-premise/modules/FiftyOneDegreesHashEngine.so"
FiftyOneDegreesHashEngine.data_file = "$PWD/assets/TAC-HashV41.hash"
FiftyOneDegreesHashEngine.allow_unmatched = false
"@ | Out-File $RepoName/php.ini

# This script only runs in nightly-pr-to-main workdflow, where we should use
# development versions of dependencies
$env:COMPOSER = "composer.json"

./php/build-project.ps1 -RepoName $RepoName

exit $LASTEXITCODE
