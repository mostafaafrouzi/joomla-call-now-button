<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Call Now Button. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Document\Document;

/**
 * Icon selector field with visual preview
 *
 * @since  1.0.0
 */
class JFormFieldIconselector extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.0.0
     */
    protected $type = 'Iconselector';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.0.0
     */
    protected function getInput()
    {
        // Get field value
        $value = $this->value ?: 'phone';
        
        // Icon options with SVG preview
        $icons = [
            'phone' => [
                'label' => 'Phone',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32"><path d="M27.01355,23.48859l-1.753,1.75305a5.001,5.001,0,0,1-5.19928,1.18243c-1.97193-.69372-4.87335-2.36438-8.43848-5.9295S6.387,14.028,5.6933,12.05615A5.00078,5.00078,0,0,1,6.87573,6.85687L8.62878,5.10376a1,1,0,0,1,1.41431.00012l2.828,2.8288A1,1,0,0,1,12.871,9.3468L11.0647,11.153a1.0038,1.0038,0,0,0-.0821,1.32171,40.74278,40.74278,0,0,0,4.07624,4.58374,40.74143,40.74143,0,0,0,4.58374,4.07623,1.00379,1.00379,0,0,0,1.32171-.08209l1.80622-1.80627a1,1,0,0,1,1.41412-.00012l2.8288,2.828A1.00007,1.00007,0,0,1,27.01355,23.48859Z" fill="currentColor"/></svg>'
            ],
            'whatsapp' => [
                'label' => 'WhatsApp',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" fill="currentColor"/></svg>'
            ],
            'email' => [
                'label' => 'Email',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/></svg>'
            ],
            'sms' => [
                'label' => 'SMS',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z" fill="currentColor"/></svg>'
            ],
            'telegram' => [
                'label' => 'Telegram',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161l-1.97 9.272c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.941z" fill="currentColor"/></svg>'
            ],
            'messenger' => [
                'label' => 'Messenger',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.577 4.123 1.585 5.837L0 24l6.35-1.756C7.951 23.344 9.91 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.5c-1.784 0-3.46-.49-4.896-1.34L2.5 22.5l2.206-4.66C3.5 16.5 2.5 14.4 2.5 12 2.5 6.201 6.701 1.5 12 1.5S21.5 6.201 21.5 12 17.299 21.5 12 21.5z" fill="currentColor"/></svg>'
            ],
            'skype' => [
                'label' => 'Skype',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.577 4.123 1.585 5.837L0 24l6.35-1.756C7.951 23.344 9.91 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm5.568 16.655c-.192.545-.48 1.04-.86 1.48-.38.44-.84.81-1.38 1.11-.54.3-1.13.45-1.77.45-.9 0-1.68-.24-2.34-.72-.66-.48-1.17-1.14-1.53-1.98l1.71-1.05c.24.54.57.96.99 1.26.42.3.9.45 1.44.45.36 0 .69-.06.99-.18.3-.12.56-.3.78-.54.22-.24.39-.54.51-.9.12-.36.18-.75.18-1.17 0-.42-.06-.81-.18-1.17-.12-.36-.29-.66-.51-.9-.22-.24-.48-.42-.78-.54-.3-.12-.63-.18-.99-.18-.54 0-1.02.15-1.44.45-.42.3-.75.72-.99 1.26l-1.71-1.05c.36-.84.87-1.5 1.53-1.98.66-.48 1.44-.72 2.34-.72.64 0 1.23.15 1.77.45.54.3 1 .68 1.38 1.11.38.44.668.935.86 1.48.192.545.288 1.12.288 1.73 0 .61-.096 1.185-.288 1.73z" fill="currentColor"/></svg>'
            ],
            'viber' => [
                'label' => 'Viber',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 2.079.549 4.027 1.507 5.713L.028 24l6.305-1.506c1.625.892 3.418 1.393 5.352 1.393 6.624 0 11.99-5.367 11.99-11.987C23.675 5.367 18.308.001 12.017.001zm.162 18.292c-1.787 0-3.48-.49-4.93-1.344l-.352-.209-3.675.877.877-3.672-.21-.353a8.26 8.26 0 01-1.342-4.914c0-4.625 3.768-8.393 8.393-8.393s8.393 3.768 8.393 8.393-3.768 8.393-8.393 8.393zm4.508-5.334l-1.844-.928c-.144-.073-.3-.11-.456-.11-.24 0-.48.072-.696.216l-.84.528c-.168.096-.384.096-.552 0l-1.68-.84c-1.68-.84-2.784-2.928-2.784-4.848 0-.24.048-.48.144-.696l.528-.84c.144-.216.216-.456.216-.696 0-.156-.037-.312-.11-.456l-.928-1.844c-.192-.384-.576-.576-.96-.576-.24 0-.48.072-.696.216l-1.2.6c-.48.24-.816.696-.96 1.2-.144.504-.072 1.032.216 1.488 1.44 2.88 3.84 5.28 6.72 6.72.456.288.984.36 1.488.216.504-.144.96-.48 1.2-.96l.6-1.2c.144-.216.216-.456.216-.696 0-.384-.192-.768-.576-.96z" fill="currentColor"/></svg>'
            ],
            'location' => [
                'label' => 'Location',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/></svg>'
            ],
            'link' => [
                'label' => 'Link',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z" fill="currentColor"/></svg>'
            ],
        ];
        
        // Build HTML
        $html = '<div class="cnb-icon-selector" id="' . $this->id . '_container">';
        $html .= '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<div class="cnb-icon-grid">';
        
        foreach ($icons as $iconKey => $iconData) {
            $selected = ($value === $iconKey) ? ' selected' : '';
            $html .= '<div class="cnb-icon-item' . $selected . '" data-icon="' . htmlspecialchars($iconKey, ENT_QUOTES, 'UTF-8') . '">';
            $html .= '<div class="cnb-icon-preview">' . $iconData['svg'] . '</div>';
            $html .= '<div class="cnb-icon-label">' . htmlspecialchars($iconData['label'], ENT_QUOTES, 'UTF-8') . '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        // Add CSS and JS
        $doc = Factory::getDocument();
        $doc->addStyleDeclaration('
            .cnb-icon-selector {
                margin: 10px 0;
            }
            .cnb-icon-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 10px;
                margin-top: 10px;
            }
            .cnb-icon-item {
                border: 2px solid #ddd;
                border-radius: 4px;
                padding: 10px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                background: #fff;
            }
            .cnb-icon-item:hover {
                border-color: #0073aa;
                background: #f0f8ff;
            }
            .cnb-icon-item.selected {
                border-color: #0073aa;
                background: #0073aa;
                color: #fff;
            }
            .cnb-icon-preview {
                display: flex;
                justify-content: center;
                align-items: center;
                margin-bottom: 5px;
            }
            .cnb-icon-preview svg {
                width: 32px;
                height: 32px;
            }
            .cnb-icon-item.selected .cnb-icon-preview svg {
                fill: #fff;
            }
            .cnb-icon-label {
                font-size: 12px;
                font-weight: 500;
            }
        ');
        
        // Use Joomla's document ready or immediate execution
        $script = '
            (function() {
                function initIconSelector() {
                    var container = document.getElementById("' . $this->id . '_container");
                    if (!container) {
                        setTimeout(initIconSelector, 100);
                        return;
                    }
                    
                    var items = container.querySelectorAll(".cnb-icon-item");
                    var input = document.getElementById("' . $this->id . '");
                    
                    if (!input || items.length === 0) {
                        return;
                    }
                    
                    items.forEach(function(item) {
                        item.addEventListener("click", function() {
                            items.forEach(function(i) { i.classList.remove("selected"); });
                            item.classList.add("selected");
                            if (input) {
                                input.value = item.getAttribute("data-icon");
                                // Trigger change event for Joomla form validation
                                if (typeof jQuery !== "undefined") {
                                    jQuery(input).trigger("change");
                                } else {
                                    var event = new Event("change", { bubbles: true });
                                    input.dispatchEvent(event);
                                }
                            }
                        });
                    });
                    
                    // Set initial selected state
                    var currentValue = input.value || "' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '";
                    items.forEach(function(item) {
                        if (item.getAttribute("data-icon") === currentValue) {
                            item.classList.add("selected");
                        }
                    });
                }
                
                if (document.readyState === "loading") {
                    document.addEventListener("DOMContentLoaded", initIconSelector);
                } else {
                    initIconSelector();
                }
            })();
        ';
        
        $doc->addScriptDeclaration($script);
        
        // Load form validation script if not already loaded
        static $validationLoaded = false;
        if (!$validationLoaded) {
            $validationPath = JPATH_SITE . '/modules/mod_callnowbutton/admin/js/form-validate.js';
            if (file_exists($validationPath)) {
                $doc->addScript(Uri::root() . 'modules/mod_callnowbutton/admin/js/form-validate.js', ['version' => 'auto'], ['defer' => true]);
                $validationLoaded = true;
            }
        }
        
        return $html;
    }
}

