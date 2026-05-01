<#
.SYNOPSIS
    Build a WordPress-installable ZIP of the Sermon Works plugin.

.DESCRIPTION
    Reads the Version: header from sermons.php and uses `git archive`
    to produce dist/sermon-works-<version>.zip with a `sermon-works/`
    top-level folder, suitable for upload via WP admin (Plugins, Add
    New, Upload Plugin) or attachment to a GitHub release.

    The path allow-list is the same set of files and directories
    bundled at runtime: sermons.php, readme.txt, changelog.txt,
    LICENSE, assets/, includes/, languages/, views/.

    The script archives the working tree at HEAD, so commit any
    intended changes (notably a version bump in sermons.php) BEFORE
    running this script. Tag and release after.

    Why git archive (not Compress-Archive): PowerShell's
    Compress-Archive on Windows produces ZIPs with backslash path
    separators, which the ZIP spec forbids. PHP's ZipArchive on
    Linux (and therefore WordPress) handles those inconsistently;
    one reproducible failure mode is a Plugin "file does not exist"
    error on activation. git archive uses forward slashes, matches
    upstream WordPress.org's own packaging, and embeds the commit
    SHA as a ZIP comment for provenance.

.EXAMPLE
    pwsh ./bin/build-release-zip.ps1
#>

[CmdletBinding()]
param(
    [string]$Ref = 'HEAD'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$RepoRoot  = Resolve-Path (Join-Path $ScriptDir '..')
$DistDir   = Join-Path $RepoRoot 'dist'

$PluginFile = Join-Path $RepoRoot 'sermons.php'
if (-not (Test-Path $PluginFile)) {
    throw "Plugin file not found: $PluginFile"
}

$header = Get-Content $PluginFile -TotalCount 20 -Encoding UTF8
$match  = $header | Select-String -Pattern '^\s*\*\s*Version:\s*(\S+)' | Select-Object -First 1
if (-not $match) {
    throw 'Could not parse Version: from sermons.php header'
}
$Version = $match.Matches[0].Groups[1].Value

if (-not (Test-Path $DistDir)) {
    New-Item -ItemType Directory -Path $DistDir -Force | Out-Null
}

$ZipPath = Join-Path $DistDir "sermon-works-$Version.zip"
if (Test-Path $ZipPath) {
    Remove-Item $ZipPath -Force
}

Write-Host "Sermon Works build" -ForegroundColor Cyan
Write-Host "  version : $Version"
Write-Host "  ref     : $Ref"
Write-Host "  repo    : $RepoRoot"
Write-Host "  output  : dist/sermon-works-$Version.zip"
Write-Host ''

$Pathspec = @(
    'sermons.php',
    'readme.txt',
    'changelog.txt',
    'LICENSE',
    'assets',
    'includes',
    'languages',
    'views'
)

Push-Location $RepoRoot
try {
    & git archive --format=zip --prefix=sermon-works/ -o $ZipPath $Ref @Pathspec
    if ($LASTEXITCODE -ne 0) {
        throw "git archive failed with exit code $LASTEXITCODE"
    }
}
finally {
    Pop-Location
}

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

    $hasBackslash = $entries | Where-Object { $_.FullName -match '\\' } | Select-Object -First 1
    if ($hasBackslash) {
        Write-Warning "ZIP entries contain backslashes. ZIP spec violation, something is wrong."
    }
}
finally {
    $zip.Dispose()
}
