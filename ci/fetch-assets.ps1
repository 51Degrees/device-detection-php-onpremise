param (
    [Parameter(Mandatory=$true)]
    [string]$RepoName,
    [Parameter(Mandatory=$true)]
    [string]$DeviceDetection,
    [string]$DeviceDetectionUrl
)

# PreBuild is a job from the nightly-publish-main workflow
if ($env:GITHUB_JOB -eq "PreBuild") {
    Write-Output "Skipping assets fetching"
    exit 0
}

$ErrorActionPreference = 'Stop'

$assets = New-Item -ItemType Directory -Path assets -Force

$downloads = @{
    "TAC-HashV41.hash" = {
        ./steps/fetch-hash-assets.ps1 -RepoName $RepoName -LicenseKey $DeviceDetection -Url $DeviceDetectionUrl
        Move-Item -Path $RepoName/$file -Destination $assets
    }
    "20000 User Agents.csv" = {Invoke-WebRequest -Uri "https://storage.googleapis.com/51degrees-assets/$DeviceDetection/20000%20User%20Agents.csv" -OutFile $assets/$file}
}

foreach ($file in $downloads.Keys) {
    if (!(Test-Path $assets/$file)) {
        Write-Output "Downloading $file"
        Invoke-Command -ScriptBlock $downloads[$file]
    } else {
        Write-Output "'$file' exists, skipping download"
    }
}
