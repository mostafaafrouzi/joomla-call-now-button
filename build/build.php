<?php
/**
 * Build Script for Call Now Button Module
 * 
 * This script automatically creates a release ZIP file for the module
 * 
 * @package     Call Now Button
 * @copyright   Copyright (C) 2024 Mostafa Afrouzi. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
define('BUILD_ROOT', dirname(__DIR__));
define('SOURCE_DIR', BUILD_ROOT . '/mod_callnowbutton');
define('BUILD_DIR', BUILD_ROOT . '/build');
define('RELEASES_DIR', BUILD_DIR . '/releases');

// Colors for CLI output
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

/**
 * Print colored message
 */
function printMessage($message, $color = COLOR_RESET) {
    echo $color . $message . COLOR_RESET . PHP_EOL;
}

/**
 * Get version from manifest XML
 */
function getVersion() {
    $manifestFile = SOURCE_DIR . '/mod_callnowbutton.xml';
    
    if (!file_exists($manifestFile)) {
        printMessage("Error: Manifest file not found!", COLOR_RED);
        exit(1);
    }
    
    $xml = simplexml_load_file($manifestFile);
    if (!$xml) {
        printMessage("Error: Could not parse manifest XML!", COLOR_RED);
        exit(1);
    }
    
    return (string) $xml->version;
}

/**
 * Create ZIP archive
 */
function createZip($version) {
    $zipFilename = "mod_callnowbutton-{$version}.zip";
    $zipPath = RELEASES_DIR . '/' . $zipFilename;
    
    // Create releases directory if not exists
    if (!is_dir(RELEASES_DIR)) {
        mkdir(RELEASES_DIR, 0755, true);
    }
    
    // Remove old ZIP if exists
    if (file_exists($zipPath)) {
        unlink($zipPath);
        printMessage("Removed old ZIP file", COLOR_YELLOW);
    }
    
    // Create new ZIP
    $zip = new ZipArchive();
    
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        printMessage("Error: Could not create ZIP file!", COLOR_RED);
        exit(1);
    }
    
    // Files and folders to exclude
    $exclude = [
        '.git',
        '.gitignore',
        '.DS_Store',
        'Thumbs.db',
        '.idea',
        'node_modules',
        '.vscode'
    ];
    
    // Add files to ZIP
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(SOURCE_DIR),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    $filesAdded = 0;
    foreach ($files as $name => $file) {
        // Skip directories
        if ($file->isDir()) {
            continue;
        }
        
        // Get relative path
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen(SOURCE_DIR) + 1);
        
        // Check if file should be excluded
        $shouldExclude = false;
        foreach ($exclude as $pattern) {
            if (strpos($relativePath, $pattern) !== false) {
                $shouldExclude = true;
                break;
            }
        }
        
        if (!$shouldExclude) {
            $zip->addFile($filePath, $relativePath);
            $filesAdded++;
        }
    }
    
    $zip->close();
    
    return [
        'filename' => $zipFilename,
        'path' => $zipPath,
        'size' => filesize($zipPath),
        'files' => $filesAdded
    ];
}

/**
 * Update updates.xml with new version
 */
function updateUpdatesXml($version) {
    $updatesFile = BUILD_ROOT . '/updates/updates.xml';
    
    if (!file_exists($updatesFile)) {
        printMessage("Warning: updates.xml not found, skipping update", COLOR_YELLOW);
        return;
    }
    
    $xml = simplexml_load_file($updatesFile);
    if (!$xml) {
        printMessage("Warning: Could not parse updates.xml", COLOR_YELLOW);
        return;
    }
    
    // Update version and download URL
    $xml->update->version = $version;
    $xml->update->downloads->downloadurl = "https://github.com/mostafaafrouzi/joomla-call-now-button/releases/download/v{$version}/mod_callnowbutton-{$version}.zip";
    
    // Save updated XML
    $dom = new DOMDocument('1.0', 'utf-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $dom->save($updatesFile);
    
    printMessage("✓ Updated updates.xml with version {$version}", COLOR_GREEN);
}

/**
 * Generate checksum
 */
function generateChecksum($filePath) {
    return [
        'md5' => md5_file($filePath),
        'sha256' => hash_file('sha256', $filePath),
        'sha512' => hash_file('sha512', $filePath)
    ];
}

// Main execution
printMessage("===========================================", COLOR_BLUE);
printMessage("  Call Now Button Module - Build Script  ", COLOR_BLUE);
printMessage("===========================================", COLOR_BLUE);
echo PHP_EOL;

// Get version
printMessage("Reading version from manifest...", COLOR_YELLOW);
$version = getVersion();
printMessage("✓ Version: {$version}", COLOR_GREEN);
echo PHP_EOL;

// Create ZIP
printMessage("Creating ZIP archive...", COLOR_YELLOW);
$zipInfo = createZip($version);
printMessage("✓ ZIP created: {$zipInfo['filename']}", COLOR_GREEN);
printMessage("  Files added: {$zipInfo['files']}", COLOR_RESET);
printMessage("  Size: " . number_format($zipInfo['size'] / 1024, 2) . " KB", COLOR_RESET);
echo PHP_EOL;

// Generate checksums
printMessage("Generating checksums...", COLOR_YELLOW);
$checksums = generateChecksum($zipInfo['path']);
printMessage("✓ MD5:    {$checksums['md5']}", COLOR_GREEN);
printMessage("✓ SHA256: {$checksums['sha256']}", COLOR_GREEN);
printMessage("✓ SHA512: {$checksums['sha512']}", COLOR_GREEN);
echo PHP_EOL;

// Update updates.xml
printMessage("Updating updates.xml...", COLOR_YELLOW);
updateUpdatesXml($version);
echo PHP_EOL;

// Summary
printMessage("===========================================", COLOR_BLUE);
printMessage("  Build Completed Successfully!  ", COLOR_GREEN);
printMessage("===========================================", COLOR_BLUE);
echo PHP_EOL;
printMessage("Release file: build/releases/{$zipInfo['filename']}", COLOR_RESET);
printMessage("Version: {$version}", COLOR_RESET);
echo PHP_EOL;
printMessage("Next steps:", COLOR_YELLOW);
printMessage("1. Test the ZIP file by installing it in Joomla", COLOR_RESET);
printMessage("2. Create a GitHub release with tag v{$version}", COLOR_RESET);
printMessage("3. Upload the ZIP file to GitHub release", COLOR_RESET);
printMessage("4. Commit updated updates/updates.xml to repository", COLOR_RESET);
echo PHP_EOL;

