<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Call Now Button. All rights reserved.
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

        // Check if frontpage should be hidden
        $hideFrontpage = $this->params->get('hide_frontpage', 0);
        $menu = $this->app->getMenu();
        $active = $menu->getActive();
        
        if ($hideFrontpage && ($active === null || ($active && $active->home))) {
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
        $zIndex = $this->params->get('z_index', 9999);
        $animation = $this->params->get('animation', 'none');
        $appearance = $this->params->get('appearance', 'single');
        
        $css = "
        .cnb-button {
            background-color: {$buttonColor} !important;
            z-index: {$zIndex} !important;
        }
        .cnb-button svg path {
            fill: {$iconColor} !important;
        }
        ";
        
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
        
        // Apply scale to button only for single appearance
        // Use CSS variable for scale so it can be combined with animation transforms
        if ($appearance === 'single') {
            $css .= "
            .cnb-button.cnb-single {
                --cnb-scale: {$buttonSize};
            }
            ";
            
            // For buttons without animation, apply scale directly
            if ($animation === 'none') {
                $css .= "
                .cnb-button.cnb-single:not(.cnb-animation-pulse):not(.cnb-animation-bounce):not(.cnb-animation-shake) {
                    transform: scale({$buttonSize}) !important;
                }
                ";
            }
        }
        
        $document->addStyleDeclaration($css);
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
        
        // Check if multibutton - multibutton only works with single appearance
        if ($buttonType === 'multibutton') {
            // Force appearance to single for multibutton
            if ($appearance !== 'single') {
                $appearance = 'single';
            }
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
        
        // Add position class only for single button
        if ($appearance === 'single') {
            // Add animation class first, then position (so position overrides animation position)
            $animation = $this->params->get('animation', 'none');
            if ($animation !== 'none') {
                $classes[] = 'cnb-animation-' . $animation;
            }
            // Position class must come after animation for proper CSS specificity
            $classes[] = 'cnb-' . $position;
        }
        
        // Build onclick tracking code
        $onclick = $this->getTrackingCode();
        
        // Build aria-label
        $lang = Factory::getLanguage();
        $lang->load('mod_callnowbutton', JPATH_SITE);
        $ariaLabel = !empty($buttonText) ? '' : 'aria-label="' . 
            htmlspecialchars($lang->_('MOD_CALLNOWBUTTON_CALL_NOW'), ENT_QUOTES, 'UTF-8') . '"';
        
        // Build button HTML
        $html = '<a ' . $ariaLabel;
        $html .= ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' id="callnowbutton"';
        $html .= ' class="' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') . '"';
        
        if (!empty($onclick)) {
            $html .= ' onclick="' . htmlspecialchars($onclick, ENT_QUOTES, 'UTF-8') . '"';
        }
        
        $html .= '>';
        
        // Add icon or text based on appearance
        if ($appearance === 'full' || $appearance === 'tfull') {
            // Full width buttons always show text
            $displayText = !empty($buttonText) ? $buttonText : 
                Factory::getLanguage()->_('MOD_CALLNOWBUTTON_CALL_NOW');
            $html .= '<span class="cnb-button-text">' . 
                htmlspecialchars($displayText, ENT_QUOTES, 'UTF-8') . '</span>';
        } elseif ($appearance === 'single' || empty($buttonText)) {
            // Show icon for single button
            if (!empty($buttonIconCustom)) {
                $iconPath = Uri::root() . ltrim($buttonIconCustom, '/');
                $html .= '<img src="' . htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') . '" alt="" width="24" height="24" />';
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
        $classes = ['cnb-button', 'cnb-single', 'cnb-' . $position, 'cnb-multibutton-main'];
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
        $html .= '>';
        
        // Add main button icon
        if (!empty($mainButtonIconCustom)) {
            $iconPath = Uri::root() . ltrim($mainButtonIconCustom, '/');
            $html .= '<img src="' . htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') . '" alt="" width="24" height="24" />';
        } else {
            $html .= $this->getIcon($mainButtonIcon, $iconColor);
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
        $icons = [
            'phone' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="24" height="24"><path d="M27.01355,23.48859l-1.753,1.75305a5.001,5.001,0,0,1-5.19928,1.18243c-1.97193-.69372-4.87335-2.36438-8.43848-5.9295S6.387,14.028,5.6933,12.05615A5.00078,5.00078,0,0,1,6.87573,6.85687L8.62878,5.10376a1,1,0,0,1,1.41431.00012l2.828,2.8288A1,1,0,0,1,12.871,9.3468L11.0647,11.153a1.0038,1.0038,0,0,0-.0821,1.32171,40.74278,40.74278,0,0,0,4.07624,4.58374,40.74143,40.74143,0,0,0,4.58374,4.07623,1.00379,1.00379,0,0,0,1.32171-.08209l1.80622-1.80627a1,1,0,0,1,1.41412-.00012l2.8288,2.828A1.00007,1.00007,0,0,1,27.01355,23.48859Z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'email' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'sms' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'telegram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161l-1.97 9.272c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.941z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'messenger' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.577 4.123 1.585 5.837L0 24l6.35-1.756C7.951 23.344 9.91 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.5c-1.784 0-3.46-.49-4.896-1.34L2.5 22.5l2.206-4.66C3.5 16.5 2.5 14.4 2.5 12 2.5 6.201 6.701 1.5 12 1.5S21.5 6.201 21.5 12 17.299 21.5 12 21.5z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'skype' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.577 4.123 1.585 5.837L0 24l6.35-1.756C7.951 23.344 9.91 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm5.568 16.655c-.192.545-.48 1.04-.86 1.48-.38.44-.84.81-1.38 1.11-.54.3-1.13.45-1.77.45-.9 0-1.68-.24-2.34-.72-.66-.48-1.17-1.14-1.53-1.98l1.71-1.05c.24.54.57.96.99 1.26.42.3.9.45 1.44.45.36 0 .69-.06.99-.18.3-.12.56-.3.78-.54.22-.24.39-.54.51-.9.12-.36.18-.75.18-1.17 0-.42-.06-.81-.18-1.17-.12-.36-.29-.66-.51-.9-.22-.24-.48-.42-.78-.54-.3-.12-.63-.18-.99-.18-.54 0-1.02.15-1.44.45-.42.3-.75.72-.99 1.26l-1.71-1.05c.36-.84.87-1.5 1.53-1.98.66-.48 1.44-.72 2.34-.72.64 0 1.23.15 1.77.45.54.3 1 .68 1.38 1.11.38.44.668.935.86 1.48.192.545.288 1.12.288 1.73 0 .61-.096 1.185-.288 1.73z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
            'viber' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 2.079.549 4.027 1.507 5.713L.028 24l6.305-1.506c1.625.892 3.418 1.393 5.352 1.393 6.624 0 11.99-5.367 11.99-11.987C23.675 5.367 18.308.001 12.017.001zm.162 18.292c-1.787 0-3.48-.49-4.93-1.344l-.352-.209-3.675.877.877-3.672-.21-.353a8.26 8.26 0 01-1.342-4.914c0-4.625 3.768-8.393 8.393-8.393s8.393 3.768 8.393 8.393-3.768 8.393-8.393 8.393zm4.508-5.334l-1.844-.928c-.144-.073-.3-.11-.456-.11-.24 0-.48.072-.696.216l-.84.528c-.168.096-.384.096-.552 0l-1.68-.84c-1.68-.84-2.784-2.928-2.784-4.848 0-.24.048-.48.144-.696l.528-.84c.144-.216.216-.456.216-.696 0-.156-.037-.312-.11-.456l-.928-1.844c-.192-.384-.576-.576-.96-.576-.24 0-.48.072-.696.216l-1.2.6c-.48.24-.816.696-.96 1.2-.144.504-.072 1.032.216 1.488 1.44 2.88 3.84 5.28 6.72 6.72.456.288.984.36 1.488.216.504-.144.96-.48 1.2-.96l.6-1.2c.144-.216.216-.456.216-.696 0-.384-.192-.768-.576-.96z" fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"/></svg>',
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

    /**
     * Get tracking code for onclick
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function getTrackingCode()
    {
        $trackingEnabled = $this->params->get('tracking_enabled', 0);
        $trackingType = $this->params->get('tracking_type', 'gtag');
        $gaId = $this->params->get('ga_id', '');
        $conversionTracking = $this->params->get('conversion_tracking', 0);
        $conversionType = $this->params->get('conversion_type', 'gtag');
        $conversionId = $this->params->get('conversion_id', '');
        
        $code = '';
        
        if ($trackingEnabled) {
            switch ($trackingType) {
                case 'gtag':
                    if (!empty($gaId)) {
                        $code .= "if(typeof gtag !== 'undefined'){gtag('event', 'call', {'event_category': 'Call Now Button', 'event_label': 'Button Click', 'value': 1});}";
                    } else {
                        $code .= "if(typeof gtag !== 'undefined'){gtag('event', 'call', {'event_category': 'Call Now Button', 'event_label': 'Button Click'});}";
                    }
                    break;
                case 'analytics':
                    if (!empty($gaId)) {
                        $code .= "if(typeof ga !== 'undefined'){ga('send', 'event', 'Call Now Button', 'Button Click', 'Call', 1);}";
                    } else {
                        $code .= "if(typeof ga !== 'undefined'){ga('send', 'event', 'Call Now Button', 'Button Click', 'Call');}";
                    }
                    break;
                case 'classic':
                    $code .= "if(typeof _gaq !== 'undefined'){_gaq.push(['_trackEvent', 'Call Now Button', 'Button Click', 'Call']);}";
                    break;
            }
        }
        
        if ($conversionTracking && !empty($conversionId)) {
            switch ($conversionType) {
                case 'gtag':
                    $code .= "if(typeof gtag !== 'undefined'){gtag('event', 'conversion', {'send_to': '" . htmlspecialchars($conversionId, ENT_QUOTES, 'UTF-8') . "'});}";
                    break;
                case 'js':
                    $code .= "if(typeof dataLayer !== 'undefined'){dataLayer.push({'event': 'call_conversion', 'conversion_id': '" . htmlspecialchars($conversionId, ENT_QUOTES, 'UTF-8') . "'});}";
                    break;
            }
        }
        
        return $code;
    }
}
