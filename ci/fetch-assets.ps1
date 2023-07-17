param (
    [Parameter(Mandatory=$true)]
    [string]$RepoName,
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
        if (!$DeviceDetection) {
            Write-Output "::warning file=$($MyInvocation.ScriptName),line=$($MyInvocation.ScriptLineNumber),title=No License Key::A device detection license was not provided, so On-Premise Data file will not be downloaded."
            return
        }
        ./steps/fetch-hash-assets.ps1 -RepoName $RepoName -LicenseKey $DeviceDetection -Url $DeviceDetectionUrl
        Move-Item -Path $RepoName/$file -Destination $assets
    }
    "20000 User Agents.csv" = {Invoke-WebRequest -Uri "https://media.githubusercontent.com/media/51Degrees/device-detection-data/main/20000%20User%20Agents.csv" -OutFile $assets/$file}
}

foreach ($file in $downloads.Keys) {
    if (!(Test-Path $assets/$file)) {
        Write-Output "Downloading $file"
        Invoke-Command -ScriptBlock $downloads[$file]
    } else {
        Write-Output "'$file' exists, skipping download"
    }
}
