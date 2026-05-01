<#
.SYNOPSIS
    Build a WordPress-installable ZIP of the Sermon Works plugin.

.DESCRIPTION
    Reads the Version: header from sermons.php, stages the runtime files into
    a sermon-works/ folder under dist/staging/, and produces
    dist/sermon-works-<version>.zip suitable for upload via WP admin
    (Plugins, Add New, Upload Plugin) or attachment to a GitHub release.

    Allow-listed paths are explicit: nothing outside the list is shipped.
    Run from the repo root or from anywhere; the script resolves paths
    relative to its own location.

.EXAMPLE
    pwsh ./bin/build-release-zip.ps1
#>

[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$RepoRoot  = Resolve-Path (Join-Path $ScriptDir '..')
$DistDir   = Join-Path $RepoRoot 'dist'
$StageDir  = Join-Path $DistDir  'staging'
$PluginDir = Join-Path $StageDir 'sermon-works'

$PluginFile = Join-Path $RepoRoot 'sermons.php'
if (-not (Test-Path $PluginFile)) {
    throw "Plugin file not found: $PluginFile"
}

$header  = Get-Content $PluginFile -TotalCount 20 -Encoding UTF8
$match   = $header | Select-String -Pattern '^\s*\*\s*Version:\s*(\S+)' | Select-Object -First 1
if (-not $match) {
    throw 'Could not parse Version: from sermons.php header'
}
$Version = $match.Matches[0].Groups[1].Value

Write-Host "Sermon Works build" -ForegroundColor Cyan
Write-Host "  version : $Version"
Write-Host "  repo    : $RepoRoot"
Write-Host "  output  : dist/sermon-works-$Version.zip"
Write-Host ''

if (Test-Path $DistDir) {
    Remove-Item $DistDir -Recurse -Force
}
New-Item -ItemType Directory -Path $PluginDir -Force | Out-Null

$IncludeFiles = @(
    'sermons.php',
    'readme.txt',
    'changelog.txt',
    'LICENSE'
)

$IncludeDirs = @(
    'assets',
    'includes',
    'languages',
    'views'
)

foreach ($file in $IncludeFiles) {
    $src = Join-Path $RepoRoot $file
    if (-not (Test-Path $src)) {
        throw "Required file missing: $file"
    }
    Copy-Item -Path $src -Destination $PluginDir -Force
}

foreach ($dir in $IncludeDirs) {
    $src = Join-Path $RepoRoot $dir
    if (-not (Test-Path $src)) {
        throw "Required directory missing: $dir"
    }
    Copy-Item -Path $src -Destination $PluginDir -Recurse -Force
}

$ZipPath = Join-Path $DistDir "sermon-works-$Version.zip"
Compress-Archive -Path (Join-Path $StageDir 'sermon-works') -DestinationPath $ZipPath -Force

$zipItem = Get-Item $ZipPath
$sizeKB  = [math]::Round($zipItem.Length / 1KB, 1)

Write-Host "ZIP built: $ZipPath ($sizeKB KB)" -ForegroundColor Green
Write-Host ''
Write-Host 'Top-level entries inside the ZIP:' -ForegroundColor Cyan

Add-Type -AssemblyName System.IO.Compression.FileSystem
$zip = [System.IO.Compression.ZipFile]::OpenRead($ZipPath)
try {
    $entries = $zip.Entries
    $topLevel = $entries |
        ForEach-Object {
            $parts = $_.FullName -split '/'
            if ($parts.Length -ge 2 -and $parts[1]) { "$($parts[0])/$($parts[1])" }
            else { $parts[0] }
        } |
        Sort-Object -Unique
    $topLevel | ForEach-Object { Write-Host "  $_" }
    Write-Host ''
    Write-Host "Total entries: $($entries.Count)"
}
finally {
    $zip.Dispose()
}

Remove-Item $StageDir -Recurse -Force
