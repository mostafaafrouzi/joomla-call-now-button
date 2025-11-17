<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Mostafa Afrouzi. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\CallNowButton\Site\Helper;

defined('_JEXEC') or die;

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
     * Application instance
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  1.0.0
     */
    protected $app;

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
        } elseif (is_array($params) && isset($params['params'])) {
            $this->params = $params['params'];
        } else {
            $this->params = new Registry();
        }
        $this->app = Factory::getApplication();
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
        // Check if button is active
        if (!$this->params->get('active', 1)) {
            return false;
        }

        // Check phone number or URL for single button type
        $buttonType = $this->params->get('button_type', 'single');
        if ($buttonType === 'single') {
            $linkType = $this->params->get('link_type', 'phone');
            
            if ($linkType === 'whatsapp' || $linkType === 'phone') {
                $phoneNumber = $this->params->get('phone_number', '');
                if (empty($phoneNumber)) {
                    return false;
                }
            } elseif ($linkType === 'custom') {
                $customUrl = $this->params->get('custom_url', '');
                if (empty($customUrl)) {
                    return false;
                }
            }
        } else {
            // For multibutton, check if there are items
            $multibuttonItems = $this->params->get('multibutton_items', []);
            
            // Handle both array and JSON string formats
            if (is_string($multibuttonItems)) {
                if (!empty($multibuttonItems)) {
                    $decoded = json_decode($multibuttonItems, true);
                    if (is_array($decoded)) {
                        $multibuttonItems = $decoded;
                    } else {
                        // Try to unserialize if JSON fails
                        $unserialized = @unserialize($multibuttonItems);
                        if (is_array($unserialized)) {
                            $multibuttonItems = $unserialized;
                        } else {
                            $multibuttonItems = [];
                        }
                    }
                } else {
                    $multibuttonItems = [];
                }
            }
            
            // Ensure it's an array
            if (!is_array($multibuttonItems)) {
                $multibuttonItems = [];
            }
            
            // Check if multibutton items array is not empty
            // Even if empty, allow rendering attempt - renderMultibutton will return empty string
            if (empty($multibuttonItems)) {
                // Allow rendering attempt even with empty items - renderMultibutton will handle it
                // This is better for debugging and allows multibutton to display if items are added later
                return true;
            }
            
            // Check if at least one item has required fields (button_url)
            // This is a preliminary check - renderMultibutton will do more thorough validation
            $hasValidItem = false;
            foreach ($multibuttonItems as $item) {
                // Normalize item to array for checking
                $itemData = null;
                if (is_object($item)) {
                    $itemData = get_object_vars($item);
                    if (empty($itemData)) {
                        // Try alternative method
                        foreach ($item as $prop => $value) {
                            $itemData[$prop] = $value;
                        }
                    }
                } elseif (is_array($item)) {
                    $itemData = $item;
                }
                
                // Check if item has button_url
                if (is_array($itemData) && isset($itemData['button_url'])) {
                    $url = trim($itemData['button_url']);
                    if (!empty($url) && $url !== '#') {
                        $hasValidItem = true;
                        break;
                    }
                }
                
                // Also check if item has button_title - might indicate valid item
                if (is_array($itemData) && isset($itemData['button_title']) && !empty(trim($itemData['button_title']))) {
                    // Allow item with title - renderMultibutton will check URL
                    $hasValidItem = true;
                    break;
                }
            }
            
            // Always return true to allow rendering attempt
            // renderMultibutton will return empty string if no valid items
            return true;
        }

        // Check display mode
        $displayMode = $this->params->get('display_mode', 'mobile_only');
        
        if ($displayMode === 'mobile_only') {
            $userAgent = $this->app->client->mobile;
            if (!$userAgent) {
                return false;
            }
        } elseif ($displayMode === 'desktop_only') {
            $userAgent = $this->app->client->mobile;
            if ($userAgent) {
                return false;
            }
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
        $document = Factory::getDocument();
        
        // Load CSS
        HTMLHelper::_('stylesheet', 
            'mod_callnowbutton/call-now-button.css', 
            ['relative' => true, 'version' => 'auto']
        );
        
        // Load JavaScript for multibutton if needed
        $buttonType = $this->params->get('button_type', 'single');
        $appearance = $this->params->get('appearance', 'single');
        if ($buttonType === 'multibutton' && $appearance === 'single') {
            HTMLHelper::_('script', 
                'mod_callnowbutton/multibutton.js', 
                ['relative' => true, 'version' => 'auto', 'defer' => true]
            );
        }
        
        // Load custom CSS for button styling
        $this->addCustomStyles();
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
        $document = Factory::getDocument();
        
        $buttonColor = $this->params->get('button_color', '#25D366');
        $iconColor = $this->params->get('icon_color', '#FFFFFF');
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
            .cnb-button.cnb-middle-left,
            .cnb-animation-pulse.cnb-middle-left,
            .cnb-animation-bounce.cnb-middle-left,
            .cnb-animation-shake.cnb-middle-left {
                left: {$buttonMargin}px !important;
            }
            .cnb-button.cnb-middle-right,
            .cnb-animation-pulse.cnb-middle-right,
            .cnb-animation-bounce.cnb-middle-right,
            .cnb-animation-shake.cnb-middle-right {
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
        
        // Apply scale to button for single and icontext appearances
        // Use CSS variable for scale so it can be combined with animation transforms
        if ($appearance === 'single' || $appearance === 'icontext') {
            $css .= "
            .cnb-button.cnb-single,
            .cnb-button.cnb-icontext {
                --cnb-scale: {$buttonSize};
            }
            ";
            
            // For buttons without animation, apply scale directly
            if ($animation === 'none') {
                $css .= "
                .cnb-button.cnb-single:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake),
                .cnb-button.cnb-icontext:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake) {
                    transform: scale({$buttonSize}) !important;
                }
                ";
            }
        }

        // Responsive overrides using media queries (when enabled)
        if ($responsiveEnabled && ($appearance === 'single' || $appearance === 'icontext')) {
            $css .= "
            /* Desktop (>= 1025px) */
            @media (min-width: 1025px) {
                .cnb-button.cnb-single,
                .cnb-button.cnb-icontext { --cnb-scale: {$sizeDesktop}; }
                .cnb-button.cnb-single:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake),
                .cnb-button.cnb-icontext:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake) { transform: scale({$sizeDesktop}) !important; }
            }
            /* Tablet Portrait (768px - 1024px) */
            @media (min-width: 768px) and (max-width: 1024px) and (orientation: portrait) {
                .cnb-button.cnb-single,
                .cnb-button.cnb-icontext { --cnb-scale: {$sizeTablet}; }
                .cnb-button.cnb-single:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake),
                .cnb-button.cnb-icontext:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake) { transform: scale({$sizeTablet}) !important; }
            }
            /* Tablet Landscape (768px - 1024px) */
            @media (min-width: 768px) and (max-width: 1024px) and (orientation: landscape) {
                .cnb-button.cnb-single,
                .cnb-button.cnb-icontext { --cnb-scale: {$sizeTabletLandscape}; }
                .cnb-button.cnb-single:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake),
                .cnb-button.cnb-icontext:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake) { transform: scale({$sizeTabletLandscape}) !important; }
            }
            /* Mobile Portrait (<= 767px) */
            @media (max-width: 767px) and (orientation: portrait) {
                .cnb-button.cnb-single,
                .cnb-button.cnb-icontext { --cnb-scale: {$sizeMobile}; }
                .cnb-button.cnb-single:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake),
                .cnb-button.cnb-icontext:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake) { transform: scale({$sizeMobile}) !important; }
            }
            /* Mobile Landscape (<= 767px) */
            @media (max-width: 767px) and (orientation: landscape) {
                .cnb-button.cnb-single,
                .cnb-button.cnb-icontext { --cnb-scale: {$sizeMobileLandscape}; }
                .cnb-button.cnb-single:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake),
                .cnb-button.cnb-icontext:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake) { transform: scale({$sizeMobileLandscape}) !important; }
            }
            ";
        }
        
        $document->addStyleDeclaration($css);
        
        // Apply margin to multibutton container and options
        $buttonType = $this->params->get('button_type', 'single');
        if ($buttonType === 'multibutton') {
            // Calculate options offset based on margin
            // In default CSS: main button bottom: 20px, options bottom: 70px (50px gap)
            // So options should be: margin + 50px to maintain the same gap
            $optionsOffsetBottom = $buttonMargin + 50; // 50px gap from main button
            $optionsOffsetTop = $buttonMargin + 50; // Same gap for top positions
            $optionsOffsetMiddle = $buttonMargin + 50; // Same gap for middle positions
            
            $multibuttonMarginCss = "
            /* Multibutton main button margins */
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
            .cnb-multibutton-container.cnb-middle-left {
                left: {$buttonMargin}px !important;
            }
            .cnb-multibutton-container.cnb-middle-right {
                right: {$buttonMargin}px !important;
            }
            
            /* Multibutton options positioning - adjust based on margin */
            /* Maintain 50px gap between main button and options */
            .cnb-multibutton-container.cnb-bottom-right .cnb-multibutton-options,
            .cnb-multibutton-container.cnb-bottom-left .cnb-multibutton-options,
            .cnb-multibutton-container.cnb-bottom-center .cnb-multibutton-options {
                bottom: {$optionsOffsetBottom}px !important;
            }
            .cnb-multibutton-container.cnb-top-right .cnb-multibutton-options,
            .cnb-multibutton-container.cnb-top-left .cnb-multibutton-options,
            .cnb-multibutton-container.cnb-top-center .cnb-multibutton-options {
                top: {$optionsOffsetTop}px !important;
            }
            .cnb-multibutton-container.cnb-middle-left .cnb-multibutton-options {
                left: {$optionsOffsetMiddle}px !important;
            }
            .cnb-multibutton-container.cnb-middle-right .cnb-multibutton-options {
                right: {$optionsOffsetMiddle}px !important;
            }
            ";
            $document->addStyleDeclaration($multibuttonMarginCss);
            
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
            $document->addStyleDeclaration($tooltipCss);
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
            $document->addStyleDeclaration($typoCss);
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
            if (!empty($customUrlRel)) {
                $rel = $customUrlRel;
            }
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
        $html .= ' id="callnowbutton"';
        $html .= ' class="' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') . '"';
        // Add data-appearance for CSS hooks
        $html .= ' data-appearance="' . htmlspecialchars($appearance, ENT_QUOTES, 'UTF-8') . '"';
        
        // No onclick tracking
        
        $html .= '>';
        
        // Add icon or text based on appearance
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
        
        // Get multibutton items from subform
        $multibuttonItems = $this->params->get('multibutton_items', []);
        
        // In Joomla, subform data is stored as JSON string
        if (is_string($multibuttonItems)) {
            if (!empty(trim($multibuttonItems))) {
                $decoded = json_decode($multibuttonItems, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $multibuttonItems = $decoded;
                } else {
                    // Try to unserialize if JSON fails
                    $unserialized = @unserialize($multibuttonItems);
                    if (is_array($unserialized)) {
                        $multibuttonItems = $unserialized;
                    } else {
                        $multibuttonItems = [];
                    }
                }
            } else {
                $multibuttonItems = [];
            }
        } elseif (is_object($multibuttonItems)) {
            // Convert object to array
            $multibuttonItems = (array)$multibuttonItems;
        }
        
        // Ensure it's an array
        if (!is_array($multibuttonItems)) {
            $multibuttonItems = [];
        }
        
        // Filter out empty items - handle both objects and arrays
        $validItems = [];
        foreach ($multibuttonItems as $key => $item) {
            $hasUrl = false;
            $itemData = null;
            
            // Normalize item to array
            if (is_object($item)) {
                // Convert object to array - handle both stdClass and custom objects
                $itemData = [];
                foreach ($item as $prop => $value) {
                    $itemData[$prop] = $value;
                }
                // Also try get_object_vars
                if (empty($itemData)) {
                    $itemData = get_object_vars($item);
                }
            } elseif (is_array($item)) {
                $itemData = $item;
            }
            
            // Check if item has button_url
            if (is_array($itemData) && isset($itemData['button_url'])) {
                $url = trim($itemData['button_url']);
                if (!empty($url) && $url !== '#') {
                    $hasUrl = true;
                }
            }
            
            // If item has URL, add to valid items (use original item format for rendering)
            if ($hasUrl) {
                $validItems[] = $itemData ?: $item;
            }
        }
        
        // Replace with valid items
        $multibuttonItems = $validItems;
        
        // If no valid items, return empty but log for debugging
        if (empty($multibuttonItems)) {
            // Debug: Log original items to help troubleshoot
            $originalItems = $this->params->get('multibutton_items', []);
            if (defined('JDEBUG') && JDEBUG) {
                error_log('CallNowButton renderMultibutton: No valid items found.');
                error_log('Original items type: ' . gettype($originalItems));
                if (is_array($originalItems)) {
                    error_log('Original items count: ' . count($originalItems));
                } elseif (is_string($originalItems)) {
                    error_log('Original items string length: ' . strlen($originalItems));
                    error_log('Original items preview: ' . substr($originalItems, 0, 200));
                }
            }
            return '';
        }
        
        // Build container
        $html = '<div class="cnb-multibutton-container cnb-' . htmlspecialchars($position, ENT_QUOTES, 'UTF-8') . '">';
        
        // Main button
        // Determine appearance for main button from global appearance (only icontext is special)
        $globalAppearance = $this->params->get('appearance', 'single');
        $multimainAppearance = ($globalAppearance === 'icontext') ? 'icontext' : 'single';
        $classes = ['cnb-button', 'cnb-' . $multimainAppearance, 'cnb-' . $position, 'cnb-multibutton-main'];
        $animation = $this->params->get('animation', 'none');
        if ($animation !== 'none') {
            $classes[] = 'cnb-animation-' . $animation;
        }
        
        // Get main button icon
        $mainButtonIcon = $this->params->get('main_button_icon', 'phone');
        $mainButtonIconCustom = $this->params->get('main_button_icon_custom', '');
        $iconColor = $this->params->get('icon_color', '#FFFFFF');
        
        // Main button (just opens menu, doesn't call)
        $html .= '<a href="#"';
        $html .= ' id="callnowbutton"';
        $html .= ' class="' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' aria-label="' . htmlspecialchars($lang->_('MOD_CALLNOWBUTTON_CALL_NOW'), ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-appearance="' . htmlspecialchars($multimainAppearance, ENT_QUOTES, 'UTF-8') . '">';
        
        // Get main button text first for alt attribute
        $mainButtonText = trim((string)$this->params->get('main_button_text', ''));
        if ($mainButtonText === '') {
            $mainButtonText = $lang->_('MOD_CALLNOWBUTTON_CALL_NOW');
        }
        
        // Add main button icon (+ optional text if icontext)
        if ($multimainAppearance === 'icontext') {
            if (!empty($mainButtonIconCustom)) {
                $iconPath = Uri::root() . ltrim($mainButtonIconCustom, '/');
                $html .= '<span class="cnb-icon-circle"><img src="' . htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($mainButtonText, ENT_QUOTES, 'UTF-8') . '" width="20" height="20" class="cnb-icon" /></span>';
            } else {
                $iconSvg = $this->getIcon($mainButtonIcon, $iconColor);
                if (preg_match('/<svg\s+/i', $iconSvg)) {
                    $iconSvg = preg_replace('/<svg\s+/i', '<svg class="cnb-icon" width="20" height="20" ', $iconSvg, 1);
                }
                $html .= '<span class="cnb-icon-circle">' . $iconSvg . '</span>';
            }
            $html .= '<span class="cnb-button-text">' . htmlspecialchars($mainButtonText, ENT_QUOTES, 'UTF-8') . '</span>';
        } else {
            if (!empty($mainButtonIconCustom)) {
                $iconPath = Uri::root() . ltrim($mainButtonIconCustom, '/');
                $html .= '<img src="' . htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($mainButtonText, ENT_QUOTES, 'UTF-8') . '" width="24" height="24" />';
            } else {
                $html .= $this->getIcon($mainButtonIcon, $iconColor);
            }
        }
        
        $html .= '</a>';
        
        // Options list
        $html .= '<ul class="cnb-multibutton-options">';
        
        // Render items from subform
        if (!empty($multibuttonItems) && is_array($multibuttonItems)) {
            $user = Factory::getUser();
            $levels = $user->getAuthorisedViewLevels();
            
            foreach ($multibuttonItems as $key => $item) {
                // In Joomla, subform items can be objects or arrays
                // Convert to array for consistent handling
                if (is_object($item)) {
                    // Handle Joomla object with properties
                    $itemArray = [
                        'button_title' => isset($item->button_title) ? $item->button_title : (isset($item->{'button_title'}) ? $item->{'button_title'} : ''),
                        'button_url' => isset($item->button_url) ? $item->button_url : (isset($item->{'button_url'}) ? $item->{'button_url'} : ''),
                        'button_url_target' => isset($item->button_url_target) ? $item->button_url_target : (isset($item->{'button_url_target'}) ? $item->{'button_url_target'} : '0'),
                        'button_url_rel' => isset($item->button_url_rel) ? $item->button_url_rel : (isset($item->{'button_url_rel'}) ? $item->{'button_url_rel'} : ''),
                        'button_bgcolor' => isset($item->button_bgcolor) ? $item->button_bgcolor : (isset($item->{'button_bgcolor'}) ? $item->{'button_bgcolor'} : '#4285F4'),
                        'button_color' => isset($item->button_color) ? $item->button_color : (isset($item->{'button_color'}) ? $item->{'button_color'} : '#FFFFFF'),
                        'button_icon' => isset($item->button_icon) ? $item->button_icon : (isset($item->{'button_icon'}) ? $item->{'button_icon'} : 'phone'),
                        'button_icon_custom' => isset($item->button_icon_custom) ? $item->button_icon_custom : (isset($item->{'button_icon_custom'}) ? $item->{'button_icon_custom'} : ''),
                        'access' => isset($item->access) ? $item->access : (isset($item->{'access'}) ? $item->{'access'} : 1),
                    ];
                    $item = $itemArray;
                } elseif (!is_array($item)) {
                    continue;
                }
                
                // Ensure item is an array
                if (!is_array($item)) {
                    continue;
                }
                
                // Check access level
                $access = isset($item['access']) ? (int)$item['access'] : 1;
                if (!in_array($access, $levels)) {
                    continue;
                }
                
                // Get item values with defaults - handle empty strings
                $buttonTitle = isset($item['button_title']) && trim($item['button_title']) !== '' ? trim($item['button_title']) : 'Button ' . ($key + 1);
                $buttonUrl = isset($item['button_url']) && trim($item['button_url']) !== '' ? trim($item['button_url']) : '#';
                $buttonTarget = isset($item['button_url_target']) && $item['button_url_target'] == '1' ? '_blank' : '_self';
                $buttonRel = isset($item['button_url_rel']) ? trim($item['button_url_rel']) : '';
                $buttonBgColor = isset($item['button_bgcolor']) && trim($item['button_bgcolor']) !== '' ? trim($item['button_bgcolor']) : '#4285F4';
                $buttonColor = isset($item['button_color']) && trim($item['button_color']) !== '' ? trim($item['button_color']) : '#FFFFFF';
                $buttonIcon = isset($item['button_icon']) && trim($item['button_icon']) !== '' ? trim($item['button_icon']) : 'phone';
                $buttonIconCustom = isset($item['button_icon_custom']) ? trim($item['button_icon_custom']) : '';
                
                // Skip if no URL (and URL is required)
                if (empty($buttonUrl) || $buttonUrl === '#') {
                    continue;
                }
                
                // Get title display mode
                $titleDisplay = $this->params->get('multibutton_title_display', 'hover');
                
                $html .= '<li>';
                $html .= '<a href="' . htmlspecialchars($buttonUrl, ENT_QUOTES, 'UTF-8') . '"';
                $html .= ' target="' . htmlspecialchars($buttonTarget, ENT_QUOTES, 'UTF-8') . '"';
                if (!empty($buttonRel)) {
                    $html .= ' rel="' . htmlspecialchars($buttonRel, ENT_QUOTES, 'UTF-8') . '"';
                }
                $html .= ' title="' . htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8') . '"';
                $html .= ' style="background-color: ' . htmlspecialchars($buttonBgColor, ENT_QUOTES, 'UTF-8') . '; color: ' . htmlspecialchars($buttonColor, ENT_QUOTES, 'UTF-8') . ';"';
                $html .= ' aria-label="' . htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8') . '"';
                $html .= ' class="cnb-multibutton-item' . ($titleDisplay === 'always' ? ' cnb-title-always' : ($titleDisplay === 'hover' ? ' cnb-title-hover' : '')) . '"';
                $html .= '>';
                
                // Add icon
                if (!empty($buttonIconCustom)) {
                    $iconPath = Uri::root() . ltrim($buttonIconCustom, '/');
                    $html .= '<img src="' . htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8') . '" width="24" height="24" style="display: block;" />';
                } else {
                    $iconHtml = $this->getIcon($buttonIcon, $buttonColor);
                    // Ensure icon has proper styling - replace first occurrence of <svg
                    if (preg_match('/<svg\s+/i', $iconHtml)) {
                        $iconHtml = preg_replace('/<svg\s+/i', '<svg style="display: block; width: 24px; height: 24px; vertical-align: middle;" ', $iconHtml, 1);
                    }
                    // Ensure all paths have fill color - fix regex to properly handle fill attribute
                    $iconHtml = preg_replace('/(<path[^>]*?)\s*(fill="[^"]*")?\s*/i', '$1 fill="' . htmlspecialchars($buttonColor, ENT_QUOTES, 'UTF-8') . '" ', $iconHtml);
                    $html .= $iconHtml;
                }
                
                // Add tooltip/title text based on display mode
                if (!empty($buttonTitle) && $titleDisplay !== 'none') {
                    $tooltipClass = $titleDisplay === 'always' ? 'cnb-title-text' : 'cnb-tooltip';
                    $html .= '<span class="' . htmlspecialchars($tooltipClass, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                
                $html .= '</a>';
                $html .= '</li>';
            }
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        // Add inline JavaScript to ensure it runs after HTML is rendered
        $html .= '<script>
        (function() {
            function initMultibutton() {
                var container = document.querySelector(".cnb-multibutton-container:not([data-initialized])");
                if (!container) {
                    return;
                }
                var mainButton = container.querySelector(".cnb-multibutton-main");
                var optionsList = container.querySelector(".cnb-multibutton-options");
                if (!mainButton || !optionsList) {
                    return;
                }
                container.setAttribute("data-initialized", "true");
                mainButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (optionsList.classList.contains("active")) {
                        optionsList.classList.remove("active");
                    } else {
                        optionsList.classList.add("active");
                    }
                });
                var clickHandler = function(e) {
                    if (!container.contains(e.target) && optionsList.classList.contains("active")) {
                        optionsList.classList.remove("active");
                    }
                };
                setTimeout(function() {
                    document.addEventListener("click", clickHandler);
                }, 100);
            }
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", initMultibutton);
            } else {
                initMultibutton();
            }
            setTimeout(initMultibutton, 200);
        })();
        </script>';
        
        return $html;
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
        // SVG icons are stored as inline HTML code in PHP arrays (not as separate files)
        // This allows dynamic color changes and easy customization
        $icons = [
            'phone' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="24" height="24"><path d="M27.01355,23.48859l-1.753,1.75305a5.001,5.001,0,0,1-5.19928,1.18243c-1.97193-.69372-4.87335-2.36438-8.43848-5.9295S6.387,14.028,5.6933,12.05615A5.00078,5.00078,0,0,1,6.87573,6.85687L8.62878,5.10376a1,1,0,0,1,1.41431.00012l2.828,2.8288A1,1,0,0,1,12.871,9.3468L11.0647,11.153a1.0038,1.0038,0,0,0-.0821,1.32171,40.74278,40.74278,0,0,0,4.07624,4.58374,40.74143,40.74143,0,0,0,4.58374,4.07623,1.00379,1.00379,0,0,0,1.32171-.08209l1.80622-1.80627a1,1,0,0,1,1.41412-.00012l2.8288,2.828A1.00007,1.00007,0,0,1,27.01355,23.48859Z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'email' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'sms' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'telegram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161l-1.97 9.272c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.941z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'messenger' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.577 4.123 1.585 5.837L0 24l6.35-1.756C7.951 23.344 9.91 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.5c-1.784 0-3.46-.49-4.896-1.34L2.5 22.5l2.206-4.66C3.5 16.5 2.5 14.4 2.5 12 2.5 6.201 6.701 1.5 12 1.5S21.5 6.201 21.5 12 17.299 21.5 12 21.5z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'skype' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.577 4.123 1.585 5.837L0 24l6.35-1.756C7.951 23.344 9.91 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm5.568 16.655c-.192.545-.48 1.04-.86 1.48-.38.44-.84.81-1.38 1.11-.54.3-1.13.45-1.77.45-.9 0-1.68-.24-2.34-.72-.66-.48-1.17-1.14-1.53-1.98l1.71-1.05c.24.54.57.96.99 1.26.42.3.9.45 1.44.45.36 0 .69-.06.99-.18.3-.12.56-.3.78-.54.22-.24.39-.54.51-.9.12-.36.18-.75.18-1.17 0-.42-.06-.81-.18-1.17-.12-.36-.29-.66-.51-.9-.22-.24-.48-.42-.78-.54-.3-.12-.63-.18-.99-.18-.54 0-1.02.15-1.44.45-.42.3-.75.72-.99 1.26l-1.71-1.05c.36-.84.87-1.5 1.53-1.98.66-.48 1.44-.72 2.34-.72.64 0 1.23.15 1.77.45.54.3 1 .68 1.38 1.11.38.44.668.935.86 1.48.192.545.288 1.12.288 1.73 0 .61-.096 1.185-.288 1.73z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'viber' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 2.079.549 4.027 1.507 5.713L.028 24l6.305-1.506c1.625.892 3.418 1.393 5.352 1.393 6.624 0 11.99-5.367 11.99-11.987C23.675 5.367 18.308.001 12.017.001zm.162 18.292c-1.787 0-3.48-.49-4.93-1.344l-.352-.209-3.675.877.877-3.672-.21-.353a8.26 8.26 0 01-1.342-4.914c0-4.625 3.768-8.393 8.393-8.393s8.393 3.768 8.393 8.393-3.768 8.393-8.393 8.393zm4.508-5.334l-1.844-.928c-.144-.073-.3-.11-.456-.11-.24 0-.48.072-.696.216l-.84.528c-.168.096-.384.096-.552 0l-1.68-.84c-1.68-.84-2.784-2.928-2.784-4.848 0-.24.048-.48.144-.696l.528-.84c.144-.216.216-.456.216-.696 0-.156-.037-.312-.11-.456l-.928-1.844c-.192-.384-.576-.576-.96-.576-.24 0-.48.072-.696.216l-1.2.6c-.48.24-.816.696-.96 1.2-.144.504-.072 1.032.216 1.488 1.44 2.88 3.84 5.28 6.72 6.72.456.288.984.36 1.488.216.504-.144.96-.48 1.2-.96l.6-1.2c.144-.216.216-.456.216-.696 0-.384-.192-.768-.576-.96z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'tiktok' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'snapchat' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12.206 0C8.912 0 5.844 1.099 3.193 3.074c-.126.096-.252.19-.376.286-.403.31-.81.617-1.2.944C.816 4.867.278 5.94.085 7.124c-.018.106-.034.215-.048.323C-.01 8.3-.051 9.147.062 9.994c.032.236.072.47.119.702.262 1.326 1.08 2.38 2.226 3.063.267.16.538.31.82.447.086.042.175.078.262.118.01.004.02.006.03.01-.004.01-.01.02-.015.03-.093.327-.17.657-.23.99-.053.3-.08.604-.082.91 0 .032.003.064.004.096.008.204.02.407.044.607.117.99.41 1.9.87 2.75.46.85 1.02 1.64 1.68 2.35.19.21.39.41.6.6.47.42.98.79 1.52 1.11.54.32 1.12.59 1.72.81.6.22 1.23.38 1.88.49.15.02.3.04.45.06.31.03.63.05.94.05.31 0 .63-.02.94-.05.15-.02.3-.04.45-.06.65-.11 1.28-.27 1.88-.49.6-.22 1.18-.49 1.72-.81.54-.32 1.05-.69 1.52-1.11.21-.19.41-.39.6-.6.66-.71 1.22-1.5 1.68-2.35.46-.85.75-1.76.87-2.75.02-.2.04-.403.04-.607 0-.032 0-.064.004-.096 0-.306-.03-.61-.082-.91-.06-.333-.137-.663-.23-.99-.01-.004-.02-.006-.03-.01.087-.04.176-.076.262-.118.282-.137.553-.287.82-.447 1.146-.683 1.964-1.737 2.226-3.063.047-.232.087-.466.119-.702.113-.847.072-1.694-.002-2.547-.014-.108-.03-.217-.048-.323C23.722 5.94 23.184 4.867 22.38 4.304c-.39-.327-.797-.634-1.2-.944-.124-.096-.25-.19-.376-.286C18.156 1.099 15.088 0 12.206 0zm.027 5.55c.325-.001.641.042.944.127.305.085.597.212.87.38.544.337 1.017.811 1.35 1.355.168.273.295.565.38.87.085.303.127.619.127.944 0 .325-.042.641-.127.944-.085.305-.212.597-.38.87-.333.544-.806 1.018-1.35 1.355-.273.168-.565.295-.87.38-.303.085-.619.127-.944.127-.325 0-.641-.042-.944-.127-.305-.085-.597-.212-.87-.38-.544-.337-1.017-.811-1.35-1.355-.168-.273-.295-.565-.38-.87-.085-.303-.127-.619-.127-.944 0-.325.042-.641.127-.944.085-.305.212-.597.38-.87.333-.544.806-1.018 1.35-1.355.273-.168.565-.295.87-.38.303-.085.619-.127.944-.127z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'discord' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'signal' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.577 4.123 1.585 5.837L0 24l6.35-1.756C7.951 23.344 9.91 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm-.29 5.71c1.85 0 3.35 1.5 3.35 3.35s-1.5 3.35-3.35 3.35-3.35-1.5-3.35-3.35S9.86 5.71 11.71 5.71zm4.54 11.71l-9.07-5.82c1.44-1.05 3.24-1.68 5.21-1.68 1.97 0 3.77.63 5.21 1.68l-9.07 5.82z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'line' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.028 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'wechat' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.597-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm6.595.31c2.092 0 3.786 1.726 3.786 3.853 0 2.128-1.694 3.855-3.786 3.855a4.1 4.1 0 0 1-1.771-.392.628.628 0 0 0-.525.038l-1.335.782a.22.22 0 0 1-.302-.099.244.244 0 0 1-.027-.146l.195-1.477a.471.471 0 0 0-.163-.537 3.826 3.826 0 0 1-1.384-2.637c0-2.127 1.693-3.854 3.785-3.854zm-1.395 2.31a.95.95 0 0 0-.949.958.95.95 0 0 0 .949.957.95.95 0 0 0 .95-.957.95.95 0 0 0-.95-.958zm2.79 0a.95.95 0 0 0-.95.958.95.95 0 0 0 .95.957.95.95 0 0 0 .95-.957.95.95 0 0 0-.95-.958z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'location' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'link' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
        ];
        
        return isset($icons[$iconType]) ? $icons[$iconType] : $icons['phone'];
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
        $iconColor = $this->params->get('icon_color', '#FFFFFF');
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="24" height="24">';
        $svg .= '<path d="M27.01355,23.48859l-1.753,1.75305a5.001,5.001,0,0,1-5.19928,1.18243c-1.97193-.69372-4.87335-2.36438-8.43848-5.9295S6.387,14.028,5.6933,12.05615A5.00078,5.00078,0,0,1,6.87573,6.85687L8.62878,5.10376a1,1,0,0,1,1.41431.00012l2.828,2.8288A1,1,0,0,1,12.871,9.3468L11.0647,11.153a1.0038,1.0038,0,0,0-.0821,1.32171,40.74278,40.74278,0,0,0,4.07624,4.58374,40.74143,40.74143,0,0,0,4.58374,4.07623,1.00379,1.00379,0,0,0,1.32171-.08209l1.80622-1.80627a1,1,0,0,1,1.41412-.00012l2.8288,2.828A1.00007,1.00007,0,0,1,27.01355,23.48859Z" fill="' . htmlspecialchars($iconColor, ENT_QUOTES, 'UTF-8') . '"/>';
        $svg .= '</svg>';
        
        return $svg;
    }

    // Tracking helpers removed
}
