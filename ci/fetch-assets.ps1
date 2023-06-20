param (
    [Parameter(Mandatory=$true)]
    [string]$RepoName,
    [Parameter(Mandatory=$true)]
    [string]$DeviceDetection,
    [string]$DeviceDetectionUrl
)

if ($env:GITHUB_JOB -eq "PreBuild") {
    Write-Output "Skipping assets fetching"
    exit 0
}

$assets = New-Item -ItemType Directory -Path assets -Force
$file = "TAC-HashV41.hash"

if (!(Test-Path $assets/$file)) {
    Write-Output "Downloading $file"
    ./steps/fetch-hash-assets.ps1 -RepoName $RepoName -LicenseKey $DeviceDetection -Url $DeviceDetectionUrl
    Move-Item -Path $RepoName/$file -Destination $assets
} else {
    Write-Output "'$file' exists, skipping download"
}
