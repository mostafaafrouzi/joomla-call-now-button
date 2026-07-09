<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Mostafa Afrouzi. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\CallNowButton\Site\Helper;

defined('_JEXEC') or die;

require_once __DIR__ . '/IconRepository.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * Helper class for Call Now Button module
 *
 * @since  1.0.0
 */
class CallNowButtonHelper
{
    /**
     * Module parameters
     *
     * @var    Registry
     * @since  1.0.0
     */
    protected $params;

    /**
     * Module object
     *
     * @var    object|null
     * @since  1.1.0
     */
    protected $module;

    /**
     * Application instance
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  1.0.0
     */
    protected $app;

    /**
     * Per-instance scoped inline CSS blocks rendered with the module markup
     *
     * @var    array<int, string>
     * @since  1.1.1
     */
    protected $inlineCssBlocks = [];

    /**
     * Whether shared CSS/JS tags were already emitted in the page body
     *
     * @var    boolean
     * @since  1.1.1
     */
    protected static $sharedAssetsEmitted = false;

    /**
     * Constructor
     *
     * @param   Registry|array  $params  Module parameters (Registry object or array with 'params' key)
     *
     * @since   1.0.0
     */
    public function __construct($params = null)
    {
        if ($params instanceof Registry) {
            $this->params = $params;
        } elseif (is_array($params)) {
            if (isset($params['params'])) {
                $this->params = $params['params'] instanceof Registry
                    ? $params['params']
                    : new Registry($params['params']);
            } else {
                $this->params = new Registry($params);
            }

            if (isset($params['module'])) {
                $this->module = $params['module'];
            }
        } else {
            $this->params = new Registry();
        }

        $this->app = Factory::getApplication();
    }

    /**
     * Get module instance id
     *
     * @return  integer
     *
     * @since   1.1.0
     */
    protected function getModuleId()
    {
        if ($this->module && isset($this->module->id)) {
            return (int) $this->module->id;
        }

        return 0;
    }

    /**
     * Wrapper element id for per-instance scoping
     *
     * @return  string
     *
     * @since   1.1.0
     */
    public function getWrapperId()
    {
        $moduleId = $this->getModuleId();

        return $moduleId ? 'cnb-mod-' . $moduleId : '';
    }

    /**
     * CSS scope prefix for per-instance generated styles
     *
     * @return  string
     *
     * @since   1.1.0
     */
    protected function getScopePrefix()
    {
        $moduleId = $this->getModuleId();

        return $moduleId ? '#cnb-mod-' . $moduleId . ' ' : '';
    }

    /**
     * Prefix generated CSS selectors with the module wrapper id
     *
     * @param   string  $css  Raw CSS rules
     *
     * @return  string
     *
     * @since   1.1.0
     */
    protected function scopeInlineCss($css)
    {
        $scope = trim($this->getScopePrefix());

        if ($scope === '') {
            return $css;
        }

        return preg_replace_callback(
            '/(?<!@)(?<![\w#])(\.cnb-[^\{]+)\{/m',
            function ($matches) use ($scope) {
                $selectors = preg_split('/\s*,\s*/', trim($matches[1]));
                $scoped = [];

                foreach ($selectors as $selector) {
                    $scoped[] = $scope . ' ' . trim($selector);
                }

                return implode(', ', $scoped) . '{';
            },
            $css
        );
    }

    /**
     * Parse multibutton subform items
     *
     * @param   boolean  $validOnly  Return only items with a usable URL
     *
     * @return  array
     *
     * @since   1.1.0
     */
    protected function getMultibuttonItems($validOnly = false)
    {
        $multibuttonItems = $this->params->get('multibutton_items', []);

        if (is_string($multibuttonItems)) {
            $multibuttonItems = trim($multibuttonItems);

            if ($multibuttonItems === '') {
                return [];
            }

            $decoded = json_decode($multibuttonItems, true);
            $multibuttonItems = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        } elseif (is_object($multibuttonItems)) {
            $multibuttonItems = (array) $multibuttonItems;
        }

        if (!is_array($multibuttonItems)) {
            return [];
        }

        $items = [];

        foreach ($multibuttonItems as $item) {
            $normalized = $this->normalizeMultibuttonItem($item);

            if ($normalized === null) {
                continue;
            }

            if ($validOnly) {
                $url = trim($normalized['button_url'] ?? '');

                if ($url === '' || $url === '#') {
                    continue;
                }
            }

            $items[] = $normalized;
        }

        return $items;
    }

    /**
     * Normalize a multibutton subform row to array
     *
     * @param   mixed  $item  Subform row
     *
     * @return  array|null
     *
     * @since   1.1.0
     */
    protected function normalizeMultibuttonItem($item)
    {
        if (is_object($item)) {
            $item = json_decode(json_encode($item), true);
        }

        if (!is_array($item)) {
            return null;
        }

        return $item;
    }

    /**
     * CSS class for cache-safe display mode on the wrapper
     *
     * @return  string
     *
     * @since   1.1.0
     */
    public function getDisplayModeClass()
    {
        $displayMode = $this->params->get('display_mode', 'all');

        if ($displayMode === 'mobile_only') {
            return 'cnb-display-mobile-only';
        }

        if ($displayMode === 'desktop_only') {
            return 'cnb-display-desktop-only';
        }

        return 'cnb-display-all';
    }

    /**
     * Resolve a theme color from optional CSS override or color picker
     *
     * @param   string  $pickerKey    Parameter name for the color field
     * @param   string  $overrideKey  Parameter name for CSS override field
     * @param   string  $default      Default color
     *
     * @return  string
     *
     * @since   1.1.0
     */
    protected function resolveThemeColor($pickerKey, $overrideKey, $default)
    {
        $override = trim((string) $this->params->get($overrideKey, ''));

        if ($override !== '') {
            $validated = $this->validateCssColorValue($override);

            if ($validated !== '') {
                return $validated;
            }
        }

        $picker = trim((string) $this->params->get($pickerKey, $default));
        $validated = $this->validateCssColorValue($picker);

        return $validated !== '' ? $validated : $default;
    }

    /**
     * Validate a CSS color value used in generated styles
     *
     * @param   string  $value  Raw color value
     *
     * @return  string  Sanitized value or empty string
     *
     * @since   1.1.0
     */
    protected function validateCssColorValue($value)
    {
        $value = trim((string) $value);

        if ($value === '' || preg_match('/[;{}<>]/', $value)) {
            return '';
        }

        if (preg_match('/^var\(--[\w-]+(?:,\s*[^;{}<>]+)?\)$/i', $value)) {
            return $value;
        }

        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
            return $value;
        }

        if (preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*(?:0?\.\d+|1(?:\.0)?|\d{1,3}%))?\s*\)$/i', $value)) {
            return $value;
        }

        return '';
    }

    /**
     * Sanitize user-provided custom CSS before injection
     *
     * @param   string  $css  Raw CSS
     *
     * @return  string
     *
     * @since   1.1.0
     */
    protected function sanitizeCustomCss($css)
    {
        $css = (string) $css;

        if (trim($css) === '') {
            return '';
        }

        $css = str_replace(['<', '>'], '', $css);

        $blocked = ['@import', 'javascript:', 'expression(', 'behavior:', '-moz-binding'];

        foreach ($blocked as $pattern) {
            if (stripos($css, $pattern) !== false) {
                return '';
            }
        }

        return trim($css);
    }

    /**
     * Build inline scale CSS variable for button element
     *
     * @return  string
     *
     * @since   1.1.0
     */
    protected function getScaleStyleAttribute()
    {
        $scale = (float) $this->params->get('button_size', 1);

        return ' style="--cnb-scale:' . htmlspecialchars((string) $scale, ENT_QUOTES, 'UTF-8') . ';"';
    }

    /**
     * Build rel attribute for external links
     *
     * @param   string  $rel     Configured rel value
     * @param   string  $target  Link target
     *
     * @return  string
     *
     * @since   1.1.0
     */
    protected function buildRelAttribute($rel, $target)
    {
        $rel = trim((string) $rel);
        $parts = $rel !== '' ? preg_split('/\s+/', $rel) : [];

        if ($target === '_blank') {
            foreach (['noopener', 'noreferrer'] as $required) {
                if (!in_array($required, $parts, true)) {
                    $parts[] = $required;
                }
            }
        }

        return implode(' ', array_unique($parts));
    }

    /**
     * Check if button should be rendered
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    public function shouldRender()
    {
        if (!(int) $this->params->get('active', 1)) {
            return false;
        }

        $buttonType = $this->params->get('button_type', 'single');

        if ($buttonType === 'single') {
            $linkType = $this->params->get('link_type', 'phone');

            if ($linkType === 'whatsapp' || $linkType === 'phone') {
                if (empty($this->params->get('phone_number', ''))) {
                    return false;
                }
            } elseif ($linkType === 'custom') {
                if (empty($this->params->get('custom_url', ''))) {
                    return false;
                }
            }
        } elseif (empty($this->getMultibuttonItems(true))) {
            return false;
        }

        return true;
    }

    /**
     * Load CSS and JavaScript assets
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function loadAssets()
    {
        $this->registerWebAssets();
        $this->addCustomStyles();
    }

    /**
     * Register front-end assets via Joomla Web Asset Manager
     *
     * @return  void
     *
     * @since   1.1.1
     */
    protected function registerWebAssets()
    {
        $document = Factory::getDocument();

        if (!method_exists($document, 'getWebAssetManager')) {
            return;
        }

        $wa = $document->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('mod_callnowbutton');
        $wa->useStyle('mod_callnowbutton.style');

        if ($this->params->get('button_type', 'single') === 'multibutton') {
            $wa->useScript('mod_callnowbutton.multibutton');
        }
    }

    /**
     * Emit CSS/JS tags next to the module when the template position renders after head
     *
     * @return  string
     *
     * @since   1.1.1
     */
    public function getFrontendAssetMarkup()
    {
        $html = '';

        if (!self::$sharedAssetsEmitted) {
            self::$sharedAssetsEmitted = true;

            $cssUrl = HTMLHelper::_(
                'stylesheet',
                'mod_callnowbutton/call-now-button.css',
                ['relative' => true, 'version' => 'auto', 'pathOnly' => true]
            );

            if ($cssUrl) {
                $html .= '<link rel="stylesheet" href="' . htmlspecialchars($cssUrl, ENT_QUOTES, 'UTF-8') . '">';
            }

            if ($this->params->get('button_type', 'single') === 'multibutton') {
                $jsUrl = HTMLHelper::_(
                    'script',
                    'mod_callnowbutton/multibutton.js',
                    ['relative' => true, 'version' => 'auto', 'pathOnly' => true]
                );

                if ($jsUrl) {
                    $html .= '<script src="' . htmlspecialchars($jsUrl, ENT_QUOTES, 'UTF-8') . '" defer></script>';
                }
            }
        }

        if (!empty($this->inlineCssBlocks)) {
            $html .= '<style>' . implode("\n", $this->inlineCssBlocks) . '</style>';
        }

        return $html;
    }

    /**
     * Store scoped inline CSS for this module instance
     *
     * @param   string  $css  Raw CSS rules
     *
     * @return  void
     *
     * @since   1.1.1
     */
    protected function addInlineCssBlock($css)
    {
        $css = trim($css);

        if ($css === '') {
            return;
        }

        $scopedCss = $this->scopeInlineCss($css);
        $this->inlineCssBlocks[] = $scopedCss;
        Factory::getDocument()->addStyleDeclaration($scopedCss);
    }

    /**
     * Add custom inline styles based on parameters
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function addCustomStyles()
    {
        $buttonColor = $this->resolveThemeColor('button_color', 'button_color_css', '#25D366');
        $iconColor = $this->resolveThemeColor('icon_color', 'icon_color_css', '#FFFFFF');
        $buttonSize = $this->params->get('button_size', 1);
        $buttonMargin = (int)$this->params->get('button_margin', 20);
        $zIndex = $this->params->get('z_index', 9999);
        $animation = $this->params->get('animation', 'none');
        $appearance = $this->params->get('appearance', 'single');
        $responsiveEnabled = (int)$this->params->get('responsive_size_enabled', 0) === 1;
        $sizeDesktop = (float)$this->params->get('size_desktop', 1.0);
        $sizeTablet = (float)$this->params->get('size_tablet', 1.0);
        $sizeTabletLandscape = (float)$this->params->get('size_tablet_landscape', 1.0);
        $sizeMobile = (float)$this->params->get('size_mobile', 0.95);
        $sizeMobileLandscape = (float)$this->params->get('size_mobile_landscape', 1.0);
        $iconTextFontSize = (int)$this->params->get('icontext_font_size', 14);
        $iconTextFontColor = $this->params->get('icontext_font_color', '#FFFFFF');
        $iconTextFontWeight = (int)$this->params->get('icontext_font_weight', 600);
        $iconTextIconSize = (int)$this->params->get('icontext_icon_size', 20);
        $iconTextIconCircle = (int)$this->params->get('icontext_icon_circle', 26);
        $tooltipTheme = $this->params->get('multibutton_tooltip_theme', 'dark');
        $buttonType = $this->params->get('button_type', 'single');

        $css = "
        .cnb-button {
            background-color: {$buttonColor} !important;
            z-index: {$zIndex} !important;
        }
        .cnb-button svg path {
            fill: {$iconColor} !important;
        }
        ";
        
        // Apply custom margin to all position classes (including animation variants)
        if ($appearance === 'single' || $appearance === 'icontext') {
            $css .= "
            .cnb-button.cnb-bottom-left,
            .cnb-button.cnb-bottom-right,
            .cnb-button.cnb-bottom-center,
            .cnb-animation-pulse.cnb-bottom-left,
            .cnb-animation-pulse.cnb-bottom-right,
            .cnb-animation-pulse.cnb-bottom-center,
            .cnb-animation-bounce.cnb-bottom-left,
            .cnb-animation-bounce.cnb-bottom-right,
            .cnb-animation-bounce.cnb-bottom-center,
            .cnb-animation-shake.cnb-bottom-left,
            .cnb-animation-shake.cnb-bottom-right,
            .cnb-animation-shake.cnb-bottom-center {
                bottom: {$buttonMargin}px !important;
            }
            .cnb-button.cnb-bottom-left,
            .cnb-animation-pulse.cnb-bottom-left,
            .cnb-animation-bounce.cnb-bottom-left,
            .cnb-animation-shake.cnb-bottom-left {
                left: {$buttonMargin}px !important;
            }
            .cnb-button.cnb-bottom-right,
            .cnb-animation-pulse.cnb-bottom-right,
            .cnb-animation-bounce.cnb-bottom-right,
            .cnb-animation-shake.cnb-bottom-right {
                right: {$buttonMargin}px !important;
            }
            .cnb-button.cnb-top-left,
            .cnb-button.cnb-top-right,
            .cnb-button.cnb-top-center,
            .cnb-animation-pulse.cnb-top-left,
            .cnb-animation-pulse.cnb-top-right,
            .cnb-animation-pulse.cnb-top-center,
            .cnb-animation-bounce.cnb-top-left,
            .cnb-animation-bounce.cnb-top-right,
            .cnb-animation-bounce.cnb-top-center,
            .cnb-animation-shake.cnb-top-left,
            .cnb-animation-shake.cnb-top-right,
            .cnb-animation-shake.cnb-top-center {
                top: {$buttonMargin}px !important;
            }
            .cnb-button.cnb-top-left,
            .cnb-animation-pulse.cnb-top-left,
            .cnb-animation-bounce.cnb-top-left,
            .cnb-animation-shake.cnb-top-left {
                left: {$buttonMargin}px !important;
            }
            .cnb-button.cnb-top-right,
            .cnb-animation-pulse.cnb-top-right,
            .cnb-animation-bounce.cnb-top-right,
            .cnb-animation-shake.cnb-top-right {
                right: {$buttonMargin}px !important;
            }
            ";
        }
        
        // Pulse animation color - must match button color
        if ($animation === 'pulse') {
            $css .= "
            .cnb-animation-pulse::before,
            .cnb-animation-pulse::after {
                border-color: {$buttonColor} !important;
                opacity: 0.6;
            }
            ";
        }
        
        // Apply scale to button for single, icontext, and multibutton main trigger
        // Use CSS variable for scale so it can be combined with animation transforms
        if ($appearance === 'single' || $appearance === 'icontext') {
            $css .= "
            .cnb-button.cnb-single,
            .cnb-button.cnb-icontext {
                --cnb-scale: {$buttonSize};
            }
            ";
        }

        if ($buttonType === 'multibutton') {
            $css .= "
            .cnb-multibutton-main {
                --cnb-scale: {$buttonSize};
            }
            ";
        }

        // Responsive overrides using media queries (when enabled)
        if ($responsiveEnabled && ($appearance === 'single' || $appearance === 'icontext')) {
            $css .= "
            /* Desktop (>= 1025px) */
            @media (min-width: 1025px) {
                .cnb-button.cnb-single,
                .cnb-button.cnb-icontext { --cnb-scale: {$sizeDesktop}; }
            }
            /* Tablet Portrait (768px - 1024px) */
            @media (min-width: 768px) and (max-width: 1024px) and (orientation: portrait) {
                .cnb-button.cnb-single,
                .cnb-button.cnb-icontext { --cnb-scale: {$sizeTablet}; }
            }
            /* Tablet Landscape (768px - 1024px) */
            @media (min-width: 768px) and (max-width: 1024px) and (orientation: landscape) {
                .cnb-button.cnb-single,
                .cnb-button.cnb-icontext { --cnb-scale: {$sizeTabletLandscape}; }
            }
            /* Mobile Portrait (<= 767px) */
            @media (max-width: 767px) and (orientation: portrait) {
                .cnb-button.cnb-single,
                .cnb-button.cnb-icontext { --cnb-scale: {$sizeMobile}; }
            }
            /* Mobile Landscape (<= 767px) */
            @media (max-width: 767px) and (orientation: landscape) {
                .cnb-button.cnb-single,
                .cnb-button.cnb-icontext { --cnb-scale: {$sizeMobileLandscape}; }
            }
            ";
        }

        $this->addInlineCssBlock($css);

        // Apply margin to multibutton container
        if ($buttonType === 'multibutton') {
            $multibuttonMarginCss = "
            .cnb-multibutton-container.cnb-bottom-left,
            .cnb-multibutton-container.cnb-bottom-right,
            .cnb-multibutton-container.cnb-bottom-center {
                bottom: {$buttonMargin}px !important;
            }
            .cnb-multibutton-container.cnb-bottom-left {
                left: {$buttonMargin}px !important;
            }
            .cnb-multibutton-container.cnb-bottom-right {
                right: {$buttonMargin}px !important;
            }
            .cnb-multibutton-container.cnb-top-left,
            .cnb-multibutton-container.cnb-top-right,
            .cnb-multibutton-container.cnb-top-center {
                top: {$buttonMargin}px !important;
            }
            .cnb-multibutton-container.cnb-top-left {
                left: {$buttonMargin}px !important;
            }
            .cnb-multibutton-container.cnb-top-right {
                right: {$buttonMargin}px !important;
            }
            ";
            $this->addInlineCssBlock($multibuttonMarginCss);
            
            // Tooltip theme CSS for multibutton
            if ($tooltipTheme === 'light') {
                $tooltipCss = "
                .cnb-tooltip,
                .cnb-title-text {
                    background: rgba(255, 255, 255, 0.95) !important;
                    color: #000000 !important;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
                }";
            } else {
                // Dark theme (default)
                $tooltipCss = "
                .cnb-tooltip,
                .cnb-title-text {
                    background: rgba(0, 0, 0, 0.9) !important;
                    color: #FFFFFF !important;
                }";
            }
            $this->addInlineCssBlock($tooltipCss);
        }

        // Additional typography CSS for Icon + Text
        if ($appearance === 'icontext') {
            $typoCss = "
            .cnb-button.cnb-icontext .cnb-button-text {
                font-size: {$iconTextFontSize}px !important;
                font-weight: {$iconTextFontWeight} !important;
                color: {$iconTextFontColor} !important;
            }
            .cnb-button.cnb-icontext .cnb-icon {
                width: {$iconTextIconSize}px !important;
                height: {$iconTextIconSize}px !important;
            }
            .cnb-button.cnb-icontext .cnb-icon-circle {
                width: {$iconTextIconCircle}px !important;
                height: {$iconTextIconCircle}px !important;
            }";
            $this->addInlineCssBlock($typoCss);
        }

        $customCss = $this->sanitizeCustomCss($this->params->get('custom_css', ''));

        if ($customCss !== '') {
            $this->addInlineCssBlock($customCss);
        }
    }

    /**
     * Render the button HTML
     *
     * @return  string
     *
     * @since   1.0.0
     */
    public function renderButton()
    {
        $buttonText = $this->params->get('button_text', '');
        $appearance = $this->params->get('appearance', 'single');
        $position = $this->params->get('position', 'bottom-right');
        $buttonType = $this->params->get('button_type', 'single');
        
        // Check if multibutton - respect icontext for main button, otherwise treat as single
        if ($buttonType === 'multibutton') {
            return $this->renderMultibutton('', $position);
        }
        
        // Get link type and build URL
        $linkType = $this->params->get('link_type', 'phone');
        $href = $this->buildButtonUrl($linkType);
        
        if (empty($href)) {
            return '';
        }
        
        // Get button icon
        $buttonIcon = $this->params->get('button_icon', 'phone');
        $buttonIconCustom = $this->params->get('button_icon_custom', '');
        $iconColor = $this->params->get('icon_color', '#FFFFFF');
        
        // Build CSS classes
        $classes = ['cnb-button', 'cnb-' . $appearance];
        
        // Add position class for single-like appearances
        if ($appearance === 'single' || $appearance === 'icontext') {
            // Add animation class first, then position (so position overrides animation position)
            $animation = $this->params->get('animation', 'none');
            if ($animation !== 'none') {
                $classes[] = 'cnb-animation-' . $animation;
            }
            // Handle full width mapped via position when using icontext
            if ($appearance === 'icontext' && ($position === 'full-bottom' || $position === 'full-top')) {
                $classes[] = ($position === 'full-bottom') ? 'cnb-full' : 'cnb-tfull';
            } else {
                // Position class must come after animation for proper CSS specificity
                $classes[] = 'cnb-' . $position;
            }
        }
        
        // Tracking removed
        $onclick = '';
        
        // Build aria-label
        $lang = Factory::getLanguage();
        $lang->load('mod_callnowbutton', JPATH_SITE);
        $ariaLabel = !empty($buttonText) ? '' : 'aria-label="' . 
            htmlspecialchars($lang->_('MOD_CALLNOWBUTTON_CALL_NOW'), ENT_QUOTES, 'UTF-8') . '"';
        
        // Get target and rel for custom URL
        $target = '';
        $rel = '';
        if ($linkType === 'custom') {
            $customUrlTarget = $this->params->get('custom_url_target', '0');
            $target = $customUrlTarget == '1' ? '_blank' : '_self';
            $customUrlRel = trim($this->params->get('custom_url_rel', ''));
            $rel = $this->buildRelAttribute($customUrlRel, $target);
        }
        
        // Build title attribute from anchor text for SEO
        $titleText = '';
        if ($appearance === 'full' || $appearance === 'tfull') {
            $titleText = !empty($buttonText) ? $buttonText : $lang->_('MOD_CALLNOWBUTTON_CALL_NOW');
        } elseif ($appearance === 'icontext') {
            $titleText = !empty($buttonText) ? $buttonText : $lang->_('MOD_CALLNOWBUTTON_CALL_NOW');
        } elseif (!empty($buttonText)) {
            $titleText = $buttonText;
        } else {
            $titleText = $lang->_('MOD_CALLNOWBUTTON_CALL_NOW');
        }
        
        $scaleAttr = ($appearance === 'single' || $appearance === 'icontext')
            ? $this->getScaleStyleAttribute()
            : '';

        // Build button HTML
        $html = '<a ' . $ariaLabel;
        $html .= ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"';
        if (!empty($target)) {
            $html .= ' target="' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '"';
        }
        if (!empty($rel)) {
            $html .= ' rel="' . htmlspecialchars($rel, ENT_QUOTES, 'UTF-8') . '"';
        }
        $html .= ' title="' . htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' class="' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-appearance="' . htmlspecialchars($appearance, ENT_QUOTES, 'UTF-8') . '"';
        $html .= $scaleAttr;

        $html .= '>';
        if ($appearance === 'full' || $appearance === 'tfull') {
            // Full width buttons always show text
            $displayText = !empty($buttonText) ? $buttonText : 
                Factory::getLanguage()->_('MOD_CALLNOWBUTTON_CALL_NOW');
            $html .= '<span class="cnb-button-text">' . 
                htmlspecialchars($displayText, ENT_QUOTES, 'UTF-8') . '</span>';
        } elseif ($appearance === 'icontext') {
            // Icon + Text combo
            // Get display text first for alt attribute
            $displayText = !empty($buttonText) ? $buttonText : Factory::getLanguage()->_('MOD_CALLNOWBUTTON_CALL_NOW');
            // Icon first
            if (!empty($buttonIconCustom)) {
                $iconPath = Uri::root() . ltrim($buttonIconCustom, '/');
                $html .= '<span class="cnb-icon-circle"><img src="' . htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($displayText, ENT_QUOTES, 'UTF-8') . '" width="20" height="20" class="cnb-icon" /></span>';
            } else {
                $iconSvg = $this->getIcon($buttonIcon, $iconColor);
                // ensure svg has class for styling
                if (preg_match('/<svg\s+/i', $iconSvg)) {
                    $iconSvg = preg_replace('/<svg\s+/i', '<svg class="cnb-icon" width="20" height="20" ', $iconSvg, 1);
                }
                $html .= '<span class="cnb-icon-circle">' . $iconSvg . '</span>';
            }
            // Then text
            $html .= '<span class="cnb-button-text">' . htmlspecialchars($displayText, ENT_QUOTES, 'UTF-8') . '</span>';
        } elseif ($appearance === 'single' || empty($buttonText)) {
            // Show icon for single button
            if (!empty($buttonIconCustom)) {
                $iconPath = Uri::root() . ltrim($buttonIconCustom, '/');
                $html .= '<img src="' . htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8') . '" width="24" height="24" />';
            } else {
                $html .= $this->getIcon($buttonIcon, $iconColor);
            }
        } else {
            $html .= htmlspecialchars($buttonText, ENT_QUOTES, 'UTF-8');
        }
        
        $html .= '</a>';
        
        return $html;
    }
    
    /**
     * Build button URL based on link type
     *
     * @param   string  $linkType  Link type (phone, whatsapp, custom)
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function buildButtonUrl($linkType)
    {
        if ($linkType === 'whatsapp') {
            // Get phone number for WhatsApp
            $phoneNumber = $this->params->get('phone_number', '');
            if (empty($phoneNumber)) {
                return '';
            }
            
            // Remove all non-numeric characters (for WhatsApp, we don't want +)
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            if (empty($phoneNumber)) {
                return '';
            }
            
            // Validate WhatsApp phone number - must have country code (at least 10 digits)
            if (strlen($phoneNumber) < 10) {
                // Invalid phone number - return empty to prevent rendering
                return '';
            }
            
            // Maximum 15 digits (E.164 standard)
            if (strlen($phoneNumber) > 15) {
                return '';
            }
            
            // Build WhatsApp URL
            return 'https://wa.me/' . $phoneNumber;
            
        } elseif ($linkType === 'phone') {
            // Get phone number for phone call
            $phoneNumber = $this->params->get('phone_number', '');
            if (empty($phoneNumber)) {
                return '';
            }
            
            // Keep + and numbers for tel: links
            $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
            
            if (empty($phoneNumber)) {
                return '';
            }
            
            // Build tel: URL
            return 'tel:' . $phoneNumber;
            
        } elseif ($linkType === 'custom') {
            // Get custom URL
            $customUrl = $this->params->get('custom_url', '');
            if (empty($customUrl)) {
                return '';
            }
            
            return $customUrl;
        }
        
        return '';
    }
    
    /**
     * Render multibutton (expanding menu)
     *
     * @param   string  $phoneNumber  Phone number
     * @param   string  $position     Button position
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function renderMultibutton($phoneNumber, $position)
    {
        $lang = Factory::getLanguage();
        $lang->load('mod_callnowbutton', JPATH_SITE);

        $multibuttonItems = $this->getMultibuttonItems(true);

        if (empty($multibuttonItems)) {
            return '';
        }

        $moduleId = $this->getModuleId();
        $optionsId = $moduleId ? 'cnb-options-' . $moduleId : 'cnb-options-' . uniqid('', false);
        $clickableLabel = (int) $this->params->get('multibutton_clickable_label', 1) === 1;
        $titleDisplay = $this->params->get('multibutton_title_display', 'hover');

        $globalAppearance = $this->params->get('appearance', 'single');
        $multimainAppearance = ($globalAppearance === 'icontext') ? 'icontext' : 'single';
        $classes = ['cnb-button', 'cnb-' . $multimainAppearance, 'cnb-multibutton-main'];
        $animation = $this->params->get('animation', 'none');

        if ($animation !== 'none') {
            $classes[] = 'cnb-animation-' . $animation;
        }

        $mainButtonIcon = $this->params->get('main_button_icon', 'phone');
        $mainButtonIconCustom = $this->params->get('main_button_icon_custom', '');
        $iconColor = $this->params->get('icon_color', '#FFFFFF');
        $mainButtonText = trim((string) $this->params->get('main_button_text', ''));

        if ($mainButtonText === '') {
            $mainButtonText = $lang->_('MOD_CALLNOWBUTTON_CALL_NOW');
        }

        $html = '<div class="cnb-multibutton-container cnb-' . htmlspecialchars($position, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<button type="button"';
        $html .= ' class="' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' aria-label="' . htmlspecialchars($mainButtonText, ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' aria-expanded="false"';
        $html .= ' aria-controls="' . htmlspecialchars($optionsId, ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-appearance="' . htmlspecialchars($multimainAppearance, ENT_QUOTES, 'UTF-8') . '"';
        $html .= $this->getScaleStyleAttribute();
        $html .= '>';

        if ($multimainAppearance === 'icontext') {
            $html .= $this->renderMultibuttonMainIcon($mainButtonIcon, $mainButtonIconCustom, $iconColor, $mainButtonText, true);
            $html .= '<span class="cnb-button-text">' . htmlspecialchars($mainButtonText, ENT_QUOTES, 'UTF-8') . '</span>';
        } else {
            $html .= $this->renderMultibuttonMainIcon($mainButtonIcon, $mainButtonIconCustom, $iconColor, $mainButtonText, false);
        }

        $html .= '</button>';
        $html .= '<ul class="cnb-multibutton-options" id="' . htmlspecialchars($optionsId, ENT_QUOTES, 'UTF-8') . '">';

        $user = Factory::getApplication()->getIdentity();
        $levels = $user->getAuthorisedViewLevels();

        foreach ($multibuttonItems as $key => $item) {
            $access = isset($item['access']) ? (int) $item['access'] : 1;

            if (!in_array($access, $levels, true)) {
                continue;
            }

            $buttonTitle = isset($item['button_title']) && trim($item['button_title']) !== ''
                ? trim($item['button_title'])
                : 'Button ' . ($key + 1);
            $buttonUrl = trim($item['button_url'] ?? '');
            $buttonTarget = isset($item['button_url_target']) && $item['button_url_target'] == '1' ? '_blank' : '_self';
            $buttonRel = $this->buildRelAttribute($item['button_url_rel'] ?? '', $buttonTarget);
            $buttonBgColor = isset($item['button_bgcolor']) && trim($item['button_bgcolor']) !== ''
                ? trim($item['button_bgcolor'])
                : '#4285F4';
            $buttonColor = isset($item['button_color']) && trim($item['button_color']) !== ''
                ? trim($item['button_color'])
                : '#FFFFFF';
            $buttonIcon = isset($item['button_icon']) && trim($item['button_icon']) !== ''
                ? trim($item['button_icon'])
                : 'phone';
            $buttonIconCustom = isset($item['button_icon_custom']) ? trim($item['button_icon_custom']) : '';

            $itemClasses = ['cnb-multibutton-item'];
            $showTitle = $titleDisplay !== 'none' && $buttonTitle !== '';

            if ($clickableLabel && $showTitle) {
                $itemClasses[] = 'cnb-row-link';
                $itemClasses[] = $titleDisplay === 'always' ? 'cnb-title-always' : 'cnb-title-hover';
            } elseif ($showTitle) {
                $itemClasses[] = $titleDisplay === 'always' ? 'cnb-title-always' : 'cnb-title-hover';
            } else {
                $itemClasses[] = 'cnb-icon-only';
            }

            $html .= '<li class="cnb-multibutton-option">';
            $html .= '<a href="' . htmlspecialchars($buttonUrl, ENT_QUOTES, 'UTF-8') . '"';
            $html .= ' target="' . htmlspecialchars($buttonTarget, ENT_QUOTES, 'UTF-8') . '"';

            if ($buttonRel !== '') {
                $html .= ' rel="' . htmlspecialchars($buttonRel, ENT_QUOTES, 'UTF-8') . '"';
            }

            $html .= ' aria-label="' . htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8') . '"';
            $html .= ' class="' . htmlspecialchars(implode(' ', $itemClasses), ENT_QUOTES, 'UTF-8') . '"';
            $html .= ' style="background-color: ' . htmlspecialchars($buttonBgColor, ENT_QUOTES, 'UTF-8')
                . '; color: ' . htmlspecialchars($buttonColor, ENT_QUOTES, 'UTF-8') . ';"';
            $html .= '>';
            $html .= '<span class="cnb-item-icon">'
                . $this->renderMultibuttonItemIcon($buttonIcon, $buttonIconCustom, $buttonColor, $buttonTitle)
                . '</span>';

            if ($showTitle) {
                if ($clickableLabel) {
                    $html .= '<span class="cnb-item-label">' . htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8') . '</span>';
                } else {
                    $tooltipClass = $titleDisplay === 'always' ? 'cnb-title-text' : 'cnb-tooltip';
                    $html .= '<span class="' . htmlspecialchars($tooltipClass, ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8') . '</span>';
                }
            }

            $html .= '</a></li>';
        }

        $html .= '</ul></div>';

        return $html;
    }

    /**
     * Render multibutton main trigger icon markup
     *
     * @param   string   $icon         Icon key
     * @param   string   $customIcon   Custom icon path
     * @param   string   $iconColor    Icon color
     * @param   string   $altText      Alt / aria text
     * @param   boolean  $wrapped      Wrap icon in icon circle span
     *
     * @return  string
     *
     * @since   1.1.0
     */
    protected function renderMultibuttonMainIcon($icon, $customIcon, $iconColor, $altText, $wrapped)
    {
        if (!empty($customIcon)) {
            $iconPath = Uri::root() . ltrim($customIcon, '/');
            $img = '<img src="' . htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') . '" alt="'
                . htmlspecialchars($altText, ENT_QUOTES, 'UTF-8') . '" width="' . ($wrapped ? '20' : '24')
                . '" height="' . ($wrapped ? '20' : '24') . '" class="cnb-icon" />';

            return $wrapped ? '<span class="cnb-icon-circle">' . $img . '</span>' : $img;
        }

        $iconSvg = $this->getIcon($icon, $iconColor);

        if (preg_match('/<svg\s+/i', $iconSvg)) {
            $size = $wrapped ? '20' : '24';
            $iconSvg = preg_replace(
                '/<svg\s+/i',
                '<svg class="cnb-icon" width="' . $size . '" height="' . $size . '" ',
                $iconSvg,
                1
            );
        }

        return $wrapped ? '<span class="cnb-icon-circle">' . $iconSvg . '</span>' : $iconSvg;
    }

    /**
     * Render multibutton option icon markup
     *
     * @param   string  $icon        Icon key
     * @param   string  $customIcon  Custom icon path
     * @param   string  $color       Icon color
     * @param   string  $altText     Alt text
     *
     * @return  string
     *
     * @since   1.1.0
     */
    protected function renderMultibuttonItemIcon($icon, $customIcon, $color, $altText)
    {
        if (!empty($customIcon)) {
            $iconPath = Uri::root() . ltrim($customIcon, '/');

            return '<img src="' . htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') . '" alt="'
                . htmlspecialchars($altText, ENT_QUOTES, 'UTF-8') . '" width="24" height="24" />';
        }

        $iconHtml = $this->getIcon($icon, $color);

        if (preg_match('/<svg\s+/i', $iconHtml)) {
            $iconHtml = preg_replace(
                '/<svg\s+/i',
                '<svg style="display: block; width: 24px; height: 24px; vertical-align: middle;" ',
                $iconHtml,
                1
            );
        }

        return preg_replace(
            '/(<path[^>]*?)\s*(fill="[^"]*")?\s*/i',
            '$1 fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '" ',
            $iconHtml
        );
    }

    /**
     * Get icon SVG based on icon type
     *
     * @param   string  $iconType  Icon type
     * @param   string  $color     Icon color
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function getIcon($iconType, $color = '#FFFFFF')
    {
        return IconRepository::render($iconType, $color, 24);
    }

    /**
     * Get phone icon SVG
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function getPhoneIcon()
    {
        return IconRepository::render('phone', $this->params->get('icon_color', '#FFFFFF'), 24);
    }

    // Tracking helpers removed
}
