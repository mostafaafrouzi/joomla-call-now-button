# Call Now Button Module for Joomla

[![Joomla](https://img.shields.io/badge/Joomla-5.0%20%7C%206.0-blue.svg)](https://www.joomla.org)
[![License](https://img.shields.io/badge/License-GPL%20v2+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.0.0-orange.svg)](https://github.com/your-repo/releases)

A professional and feature-rich Joomla module for adding floating call-to-action buttons to your website. This module provides a modern, responsive solution for displaying contact buttons (phone, WhatsApp, custom links) with extensive customization options.

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
  - [Basic Settings](#basic-settings)
  - [Presentation Settings](#presentation-settings)
- [Button Types](#-button-types)
  - [Single Button](#single-button)
  - [Multibutton (Expanding)](#multibutton-expanding)
- [SEO & Accessibility](#-seo--accessibility)
- [Responsive Design](#-responsive-design)
- [Browser Support](#-browser-support)
- [Troubleshooting](#-troubleshooting)
- [Support](#-support)
- [License](#-license)

## ✨ Features

### Core Features
- ✅ **Dual Button Types**: Single button or expanding multibutton
- ✅ **Multiple Link Types**: Phone, WhatsApp, or custom URL
- ✅ **Two Appearance Styles**: Circular icon button or icon with text (pill-shaped)
- ✅ **8 Position Options**: Bottom/Top (Left/Center/Right) + Full Width options
- ✅ **Responsive Design**: Fully responsive with device-specific size controls
- ✅ **Custom Animations**: Pulse, Bounce, Shake, or None
- ✅ **18 Built-in Icons**: Phone, WhatsApp, Telegram, Instagram, Facebook, and more
- ✅ **Custom Icon Support**: Upload your own icon images
- ✅ **Color Customization**: Full control over button and icon colors
- ✅ **Typography Controls**: Font size, color, and weight for text buttons
- ✅ **SEO Optimized**: Proper `rel`, `target`, and `title` attributes
- ✅ **Accessibility**: ARIA labels and alt text support
- ✅ **Multilingual**: English and Persian (Farsi) language support

### Advanced Features
- 📱 **Device-Specific Display**: Show on all devices, mobile only, or desktop only
- 🎨 **Visual Icon Selector**: Easy-to-use icon picker interface
- 📏 **Responsive Sizing**: Different sizes for desktop, tablet, and mobile
- 🎯 **Custom Margins**: Control button distance from screen edges
- 🌈 **Tooltip Themes**: Light or dark tooltip themes for multibutton
- 🔗 **Link Attributes**: Control `rel` and `target` attributes for SEO
- 📊 **Z-Index Control**: Manage layering with other page elements

## 📦 Requirements

- **Joomla**: 5.0 or higher (6.0 compatible)
- **PHP**: 7.4 or higher
- **Browser**: Modern browsers (Chrome, Firefox, Safari, Edge)

## 🚀 Installation

### Method 1: Install via Joomla Admin

1. Download the module ZIP file from the [Releases](https://github.com/your-repo/releases) page
2. Log in to your Joomla Administrator panel
3. Navigate to **Extensions > Manage > Install**
4. Click **Upload Package File** and select the downloaded ZIP file
5. Click **Upload & Install**
6. Go to **Extensions > Modules** and find "Call Now Button"
7. Click on the module to configure it

### Method 2: Install via FTP

1. Extract the ZIP file
2. Upload the `mod_callnowbutton` folder to `/modules/` directory
3. Go to **Extensions > Manage > Discover**
4. Click **Discover** to find the module
5. Install the discovered module

## ⚙️ Configuration

### Basic Settings

#### Active
Enable or disable the module. When disabled, the button will not appear on the frontend.

#### Button Type
Choose between two button types:

- **Single Button**: A single floating button with one action
- **Multibutton (Expanding)**: A main button that expands to show multiple action buttons

#### Link Type (Single Button Only)
Select the type of link for the single button:

- **Phone**: Opens the phone dialer (`tel:` link)
- **WhatsApp**: Opens WhatsApp chat with the specified number
- **Custom URL**: Use any custom URL (website, email, etc.)

#### Phone Number
Enter your phone number with country code (without + sign).

**Important for WhatsApp**: 
- Must include country code (e.g., `989123456789` for Iran)
- Numbers only, no spaces or special characters
- 10-15 digits recommended

**Examples**:
- Iran: `989123456789`
- UAE: `971501234567`
- USA: `15551234567`

#### Custom URL (Single Button Only)
Enter any custom URL when Link Type is set to "Custom URL".

**Examples**:
- Website: `https://example.com`
- Email: `mailto:info@example.com`
- Phone: `tel:+1234567890`

#### Open Link In (Custom URL Only)
Control how the custom URL opens:

- **Same Window**: Opens in the current window/tab
- **New Window**: Opens in a new browser tab

#### Rel Attribute (Custom URL Only)
Set the `rel` attribute for SEO purposes. Options include:

- None
- `nofollow`
- `noopener`
- `noreferrer`
- Combinations of the above

#### Button Icon (Single Button Only)
Choose from 18 built-in icons or upload a custom icon:

**Built-in Icons**:
- Phone, WhatsApp, Telegram, Viber
- Instagram, Facebook, Twitter/X, LinkedIn
- YouTube, TikTok, Snapchat, Discord
- Signal, Line, WeChat, Email, Message, Chat

#### Button Text (Single Button Only)
Optional text to display on the button (for "Icon with Text" appearance).

#### Main Button Text (Multibutton Only)
Text to display on the main multibutton (for "Icon with Text" appearance).

#### Main Button Icon (Multibutton Only)
Icon for the main multibutton button.

#### Title Display (Multibutton Only)
Control how item titles are displayed:

- **None**: No titles shown
- **Hover**: Titles appear on hover
- **Always**: Titles always visible

#### Tooltip Theme (Multibutton Only)
Choose the tooltip background theme:

- **Light**: White background with dark text
- **Dark**: Dark background with light text (default)

#### Multibutton Items (Multibutton Only)
Add multiple action buttons. Each item can have:

- **Button Title**: Display text for the button
- **Button URL**: Link URL (phone, WhatsApp, or custom)
- **Open Link In**: Same window or new window
- **Rel Attribute**: SEO link attributes
- **Background Color**: Custom color for each button
- **Icon Color**: Color for the button icon
- **Button Icon**: Choose from built-in icons or upload custom

### Presentation Settings

#### Display Mode
Control which devices show the button:

- **All Devices**: Button appears on all devices
- **Mobile Only**: Button appears only on mobile devices
- **Desktop Only**: Button appears only on desktop devices

#### Button Appearance
Choose the visual style:

- **Single Circular Button**: Classic circular floating button with icon only
- **Icon with Text**: Modern pill-shaped button with icon and text

#### Button Position
Select where the button appears on the screen:

**Standard Positions**:
- Bottom Left, Bottom Center, Bottom Right
- Top Left, Top Center, Top Right

**Full Width Positions** (Icon with Text only):
- Full Width (Bottom): Full-width button at the bottom
- Full Width (Top): Full-width button at the top

#### Button Color
Set the background color of the button. Default: `#25D366` (WhatsApp green).

#### Icon Color
Set the color of the icon. Default: `#FFFFFF` (white).

#### Button Size
Control the overall size of the button. Range: 85% to 125% of default size.

#### Enable Responsive Sizes
Enable device-specific sizing for better mobile experience.

When enabled, you can set different sizes for:

- **Desktop Size**: 90% to 200% (default: 100%)
- **Tablet Portrait Size**: 90% to 120% (default: 100%)
- **Tablet Landscape Size**: 90% to 120% (default: 100%)
- **Mobile Portrait Size**: 85% to 115% (default: 95%)
- **Mobile Landscape Size**: 85% to 115% (default: 100%)

#### Button Margin
Set the distance (in pixels) of the button from screen edges. Range: 0-200px (default: 20px).

#### Button Animation
Choose an animation effect:

- **None**: No animation
- **Pulse**: Gentle pulsing effect
- **Bounce**: Bouncing animation
- **Shake**: Shaking animation

#### Icon with Text Typography (Icon with Text Only)

**Font Size**: 10px to 20px (default: 14px)

**Font Color**: Text color (default: white)

**Font Weight**: 
- 400 (Normal)
- 500 (Medium)
- 600 (Semibold) - default
- 700 (Bold)
- 800 (Extra Bold)
- 900 (Black)

#### Icon with Text Icon Sizing (Icon with Text Only)

**Icon Size**: 14px to 32px (default: 20px)

**Icon Circle Size**: 18px to 40px (default: 26px)

#### Z-Index
Control the stacking order of the button. Higher values appear above other elements. Range: 1-99999 (default: 9999).

## 🎯 Button Types

### Single Button

A single floating button that performs one action. Perfect for:

- Direct phone calls
- Quick WhatsApp contact
- Custom links (websites, forms, etc.)

**Configuration**:
1. Set **Button Type** to "Single Button"
2. Choose **Link Type** (Phone, WhatsApp, or Custom URL)
3. Enter the phone number or custom URL
4. Customize appearance, position, and colors

### Multibutton (Expanding)

A main button that expands to reveal multiple action buttons. Ideal for:

- Multiple contact methods (Phone, WhatsApp, Email, etc.)
- Social media links
- Multiple departments or agents

**Configuration**:
1. Set **Button Type** to "Multibutton (Expanding)"
2. Configure the main button (text and icon)
3. Add items using the **Multibutton Items** section
4. Each item can have its own URL, colors, and icon
5. Set title display mode and tooltip theme

**How it works**:
- Click the main button to expand/collapse the menu
- Items appear with smooth animations
- Titles can be shown on hover or always visible
- Each item opens its configured link

## 🔍 SEO & Accessibility

### SEO Features

- **Title Attributes**: Button text is used as `title` attribute for better SEO
- **Rel Attributes**: Control `nofollow`, `noopener`, `noreferrer` for external links
- **Target Attributes**: Control whether links open in same or new window
- **Alt Text**: Custom images include proper `alt` attributes
- **Semantic HTML**: Uses proper anchor tags with meaningful attributes

### Accessibility Features

- **ARIA Labels**: Screen reader support
- **Keyboard Navigation**: Fully keyboard accessible
- **Focus Indicators**: Clear focus states for accessibility
- **Alt Text**: All images include descriptive alt text
- **Color Contrast**: Customizable colors ensure proper contrast

### Best Practices

1. **Phone Numbers**: Always include country code for international compatibility
2. **WhatsApp Numbers**: Use digits only, no + sign (e.g., `989123456789`)
3. **Custom URLs**: Use HTTPS for security and SEO
4. **Rel Attributes**: Use `nofollow` for external links you don't want to endorse
5. **Target Attributes**: Use `_blank` with `rel="noopener noreferrer"` for external links

## 📱 Responsive Design

The module is fully responsive and adapts to all screen sizes:

- **Desktop**: Full-size button with all features
- **Tablet**: Optimized sizing and positioning
- **Mobile**: Touch-friendly size and positioning

### Responsive Size Controls

Enable **Responsive Sizes** to set different button sizes for each device type:

- **Desktop** (≥1025px): Larger size for better visibility
- **Tablet Portrait** (768px-1024px): Medium size
- **Tablet Landscape** (1024px-1025px): Medium size
- **Mobile Portrait** (≤767px): Smaller size to save space
- **Mobile Landscape** (≤767px, landscape): Optimized for horizontal view

## 🌐 Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Opera (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## 🛠️ Troubleshooting

### Button Not Appearing

1. Check that **Active** is set to "Yes"
2. Verify **Display Mode** settings match your device
3. Check browser console for JavaScript errors
4. Ensure module is published and assigned to pages

### WhatsApp Link Not Working

1. Verify phone number includes country code (no + sign)
2. Check number is 10-15 digits
3. Ensure number contains only digits (no spaces or special characters)
4. Test the number format: `https://wa.me/YOURNUMBER`

### Custom Icon Not Showing

1. Verify image file is uploaded correctly
2. Check file format (JPG, PNG, SVG supported)
3. Ensure file size is reasonable (< 500KB recommended)
4. Clear Joomla cache

### Position Issues

1. Check **Z-Index** value (may be behind other elements)
2. Verify **Button Margin** settings
3. Test different positions to find the best fit
4. Check for CSS conflicts with your template

### Animation Not Working

1. Ensure animation is not set to "None"
2. Check browser supports CSS animations
3. Verify no JavaScript errors in console
4. Try a different animation type

## 📞 Support

- **GitHub Issues**: [Report bugs or request features](https://github.com/your-repo/issues)
- **Email**: support@callnowbutton.com
- **Website**: [https://callnowbutton.com](https://callnowbutton.com)

## 📄 License

This module is licensed under the GNU General Public License version 2 or later.

See [LICENSE.txt](LICENSE.txt) for full license details.

## 🙏 Credits

This module is inspired by the WordPress "Call Now Button" plugin and adapted for Joomla with additional features and improvements.

## 📝 Changelog

### Version 1.0.0
- Initial release
- Single button and multibutton support
- 18 built-in icons
- Custom icon upload
- Two appearance styles
- Responsive design
- SEO and accessibility features
- Multilingual support (English, Persian)
- Full customization options

---

**Made with ❤️ for the Joomla community**
