# Build Script for Call Now Button Module (PowerShell)
# This script creates a release ZIP file for the module

param(
    [string]$Version = ""
)

# Define paths
$BuildRoot = Split-Path -Parent $PSScriptRoot
$SourceDir = Join-Path $BuildRoot "mod_callnowbutton"
$BuildDir = $PSScriptRoot
$ReleasesDir = Join-Path $BuildDir "releases"
$ManifestFile = Join-Path $SourceDir "mod_callnowbutton.xml"

Write-Host ""
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "  Call Now Button Module - Build Script  " -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host ""

# Get version from manifest if not provided
if ([string]::IsNullOrEmpty($Version)) {
    Write-Host "Reading version from manifest..." -ForegroundColor Yellow
    
    if (-not (Test-Path $ManifestFile)) {
        Write-Host "Error: Manifest file not found!" -ForegroundColor Red
        Write-Host "Path: $ManifestFile" -ForegroundColor Red
        exit 1
    }
    
    try {
        [xml]$manifest = Get-Content $ManifestFile -Encoding UTF8
        $Version = $manifest.extension.version
        
        if ([string]::IsNullOrEmpty($Version)) {
            Write-Host "Error: Could not read version from manifest!" -ForegroundColor Red
            exit 1
        }
        
        Write-Host "Success: Version $Version" -ForegroundColor Green
    }
    catch {
        Write-Host "Error: Could not parse manifest XML!" -ForegroundColor Red
        Write-Host $_.Exception.Message -ForegroundColor Red
        exit 1
    }
}

Write-Host ""

# Create releases directory
if (-not (Test-Path $ReleasesDir)) {
    New-Item -ItemType Directory -Path $ReleasesDir -Force | Out-Null
    Write-Host "Success: Created releases directory" -ForegroundColor Green
}

# Define ZIP filename
$ZipFilename = "mod_callnowbutton-$Version.zip"
$ZipPath = Join-Path $ReleasesDir $ZipFilename

# Remove old ZIP if exists
if (Test-Path $ZipPath) {
    Remove-Item $ZipPath -Force
    Write-Host "Removed old ZIP file" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Creating ZIP archive..." -ForegroundColor Yellow

# Create ZIP using Compress-Archive
try {
    # Get all files to include
    $filesToZip = Get-ChildItem -Path $SourceDir -Recurse -File | Where-Object {
        $relativePath = $_.FullName.Substring($SourceDir.Length + 1)
        $shouldInclude = $true
        
        # Exclude patterns
        if ($relativePath -match '\.git|\.DS_Store|Thumbs\.db|\.idea|node_modules|\.vscode|\.bak|\.tmp') {
            $shouldInclude = $false
        }
        
        $shouldInclude
    }
    
    # Create temporary directory for ZIP structure
    $tempDir = Join-Path $BuildDir "temp_build"
    if (Test-Path $tempDir) {
        Remove-Item $tempDir -Recurse -Force
    }
    New-Item -ItemType Directory -Path $tempDir -Force | Out-Null
    
    # Copy files maintaining structure
    foreach ($file in $filesToZip) {
        $relativePath = $file.FullName.Substring($SourceDir.Length + 1)
        $destPath = Join-Path $tempDir $relativePath
        $destDir = Split-Path $destPath -Parent
        
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        Copy-Item $file.FullName -Destination $destPath -Force
    }
    
    # Create ZIP
    Compress-Archive -Path "$tempDir\*" -DestinationPath $ZipPath -Force
    
    # Clean up temp directory
    Remove-Item $tempDir -Recurse -Force
    
    Write-Host "Success: ZIP created successfully" -ForegroundColor Green
    
    # Get ZIP file info
    $zipInfo = Get-Item $ZipPath
    $sizeKB = [math]::Round($zipInfo.Length / 1KB, 2)
    $filesCount = $filesToZip.Count
    
    Write-Host "  Files added: $filesCount" -ForegroundColor White
    Write-Host "  Size: $sizeKB KB" -ForegroundColor White
}
catch {
    Write-Host "Error creating ZIP file!" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}

Write-Host ""

# Generate checksums
Write-Host "Generating checksums..." -ForegroundColor Yellow

$md5 = (Get-FileHash -Path $ZipPath -Algorithm MD5).Hash.ToLower()
$sha256 = (Get-FileHash -Path $ZipPath -Algorithm SHA256).Hash.ToLower()
$sha512 = (Get-FileHash -Path $ZipPath -Algorithm SHA512).Hash.ToLower()

Write-Host "Success: MD5:    $md5" -ForegroundColor Green
Write-Host "Success: SHA256: $sha256" -ForegroundColor Green
Write-Host "Success: SHA512: $sha512" -ForegroundColor Green

Write-Host ""

# Update updates.xml
$updatesXmlPath = Join-Path $BuildRoot "updates\updates.xml"

if (Test-Path $updatesXmlPath) {
    Write-Host "Updating updates.xml..." -ForegroundColor Yellow
    
    try {
        [xml]$updatesXml = Get-Content $updatesXmlPath -Encoding UTF8
        $updatesXml.updates.update.version = $Version
        $downloadUrl = "https://github.com/YOUR_USERNAME/YOUR_REPO/releases/download/v$Version/mod_callnowbutton-$Version.zip"
        $updatesXml.updates.update.downloads.downloadurl = $downloadUrl
        
        $updatesXml.Save($updatesXmlPath)
        
        Write-Host "Success: Updated updates.xml with version $Version" -ForegroundColor Green
    }
    catch {
        Write-Host "Warning: Could not update updates.xml" -ForegroundColor Yellow
        Write-Host $_.Exception.Message -ForegroundColor Yellow
    }
}

Write-Host ""

# Summary
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "  Build Completed Successfully!  " -ForegroundColor Green
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Release file: build\releases\$ZipFilename" -ForegroundColor White
Write-Host "Version: $Version" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Test the ZIP file by installing it in Joomla" -ForegroundColor White
Write-Host "2. Create a GitHub release with tag v$Version" -ForegroundColor White
Write-Host "3. Upload the ZIP file to GitHub release" -ForegroundColor White
Write-Host "4. Commit updated updates/updates.xml to repository" -ForegroundColor White
Write-Host ""
