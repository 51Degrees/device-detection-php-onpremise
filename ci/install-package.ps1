param (
    [Parameter(Mandatory=$true)]
    [string]$RepoName,
    [Parameter(Mandatory=$true)]
    [string]$Version
)

& ./$RepoName/ci/build-project.ps1 -RepoName $RepoName
