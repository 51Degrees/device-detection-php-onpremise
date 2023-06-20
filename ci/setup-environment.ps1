param (
    [Parameter(Mandatory=$true)]
    [string]$LanguageVersion
)

./php/setup-environment.ps1 -LanguageVersion $LanguageVersion

exit $LASTEXITCODE
