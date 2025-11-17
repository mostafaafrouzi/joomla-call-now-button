#!/bin/bash
# Build script for Linux/Mac
# Run this to create a release ZIP file

echo ""
echo "============================================"
echo "  Call Now Button Module - Build Script"
echo "============================================"
echo ""

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in PATH"
    echo "Please install PHP and try again"
    exit 1
fi

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Run the build script
php "$SCRIPT_DIR/build.php"

echo ""

