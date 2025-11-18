# Call Now Button Module for Joomla

[![Joomla](https://img.shields.io/badge/Joomla-5.0%20%7C%206.0-blue.svg)](https://www.joomla.org)
[![License](https://img.shields.io/badge/License-GPL%20v2+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP](https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3%20%7C%208.4-777BB4.svg)](https://www.php.net/)

Professional floating call-to-action button module for Joomla. A modern, responsive solution for displaying contact buttons (phone, WhatsApp, custom URLs) with extensive customization options.

## ✨ Key Features

### Button Types
- **Single Button**: One-click call/contact button
- **Multibutton (Expanding)**: Multiple action buttons in a floating menu

### Link Types
- **Phone**: Direct dial links (`tel:`)
- **WhatsApp**: WhatsApp chat links with country code support
- **Custom URL**: Any custom link with full control over `rel` and `target` attributes

### Appearance Options
- **Circular Icon**: Classic floating button with icon only
- **Icon with Text**: Pill-shaped button with icon and text label

### Positioning & Display
- **8 Position Options**: Bottom/Top (Left, Center, Right), Middle (Left, Right)
- **Full Width**: Full-width display at top or bottom (Icon with Text only)
- **Custom Margins**: Manual control over button distance from screen edges
- **Responsive Sizes**: Device-specific size controls (Desktop, Tablet, Mobile)

### Customization
- **18 Built-in Icons**: Phone, WhatsApp, Telegram, Instagram, Facebook, Twitter, LinkedIn, YouTube, and more
- **Custom Icon Upload**: Upload your own icon image
- **Color Controls**: Full control over button and icon colors
- **Typography**: Font size, color, and weight controls for text buttons
- **Animations**: Pulse, Bounce, Shake (with pill-shaped support)
- **Z-Index Control**: Customizable stacking order

### Multibutton Features
- **Tooltip Themes**: Light/Dark tooltip backgrounds
- **Title Display**: Show titles on hover, always, or never
- **Individual Item Styling**: Custom colors and icons per item
- **Smooth Animations**: Staggered slide animations for expanding menu

### SEO & Standards Compliance
- **SEO Optimized**: Proper `rel` attributes (nofollow, noopener, noreferrer)
- **Link Attributes**: `target` control for new window/tab
- **Title Attributes**: Anchor text used as `title` for better SEO
- **Alt Text**: Proper `alt` attributes for custom image icons
- **ARIA Labels**: Accessibility support for screen readers
- **Joomla Standards**: Follows Joomla 5.x and 6.x coding standards
- **Namespace Support**: Proper Joomla namespace implementation

### Technical Features
- **Responsive Design**: Mobile-first approach with breakpoint controls
- **Display Modes**: All devices, mobile only, or desktop only
- **Multilingual**: English and Persian (Farsi) language support
- **Auto Updates**: Built-in Joomla update system via GitHub Releases
- **Changelog Integration**: Release notes displayed in Joomla admin

## 📦 Installation

### Method 1: Download from GitHub

1. Go to [Releases](https://github.com/mostafaafrouzi/joomla-call-now-button/releases)
2. Download the latest version
3. In Joomla Admin: **Extensions > Manage > Install**
4. Upload the ZIP file

### Method 2: Direct Install

Download the latest release ZIP file directly:
```
https://github.com/mostafaafrouzi/joomla-call-now-button/releases/latest/download/mod_callnowbutton.zip
```

**Note:** This link always downloads the latest version. The filename in the download will include the version number (e.g., `mod_callnowbutton-1.0.0.zip`).

Or visit the [Releases page](https://github.com/mostafaafrouzi/joomla-call-now-button/releases) to download a specific version.

## 🔄 Automatic Updates

The module supports Joomla's automatic update system:

1. In Joomla Admin: **System > Update > Extensions**
2. Click **Check for Updates**
3. Available updates will be displayed
4. Click **Update**

### Viewing Changelog

- **In Manage Extensions**: Click on the version number
- **In Update Extensions**: Click the Changelog button to view changes

## 🛠️ Development

### Repository Structure

```
joomla-call-now-button/
├── mod_callnowbutton/      # Module code
├── updates/                # Update files
│   ├── updates.xml
│   └── changelog.xml
├── build/                  # Build scripts
└── .github/workflows/      # GitHub Actions (automated)
```

### Automated Release with GitHub Actions

**⚠️ Important:** The release workflow **ONLY** runs when you push a tag with pattern `v*` (e.g., `v1.0.0`).

**Regular commits (like updating README) do NOT trigger the release workflow.**

#### Steps to Create a New Release:

1. Update version in `mod_callnowbutton.xml`
2. Update `changelog.xml` with new changes
3. Commit and push:
   ```bash
   git add .
   git commit -m "Release v1.0.0"
   git push origin main
   ```
4. **Create and push tag (this triggers the workflow):**
   ```bash
   git tag v1.0.0
   git push origin v1.0.0
   ```
5. GitHub Actions automatically:
   - Builds ZIP file (with version in filename)
   - Creates a fixed-name ZIP (`mod_callnowbutton.zip`) for `/latest/download/` URL
   - Creates GitHub Release
   - Updates `updates.xml`
   - Generates checksums (MD5, SHA256, SHA512)

#### When Does the Workflow Run?

- ✅ **Runs:** When you push a tag starting with `v` (e.g., `v1.0.0`, `v1.0.1`)
- ❌ **Does NOT run:** Regular commits, README updates, code changes without tags

### Local Build (Optional)

```bash
build/build.bat        # Windows
build/build.sh         # Linux/Mac
build/build.php        # Cross-platform
```

## 📋 Requirements

- **Joomla**: 5.0.0 or higher (compatible with Joomla 6.x)
- **PHP**: 8.1.0 or higher (8.1, 8.2, 8.3, 8.4)
- **Browser**: Modern browsers with CSS3 and JavaScript support

## 👨‍💻 Developer

**Mostafa Afrouzi**  
*Web Designer & Developer, SEO & Digital Marketing Specialist*

- 🌐 **Website**: [afrouzi.ir/en](https://afrouzi.ir/en)

## 📄 License

This module is released under the GNU General Public License version 2 or later.

## 🙏 Support

If you find this module useful:
- ⭐ Star the repository
- 🐛 Report bugs
- 💡 Suggest improvements
- 🔄 Share with others

---

**Made with ❤️ for the Joomla community**
