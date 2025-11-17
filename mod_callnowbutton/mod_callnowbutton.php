<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Mostafa Afrouzi. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;

// Load helper class
if (!class_exists('Joomla\Module\CallNowButton\Site\Helper\CallNowButtonHelper')) {
    $helperPath = __DIR__ . '/src/Site/Helper/CallNowButtonHelper.php';
    if (file_exists($helperPath)) {
        require_once $helperPath;
    } else {
        // Fallback: try alternative path
        $helperPath = JPATH_SITE . '/modules/mod_callnowbutton/src/Site/Helper/CallNowButtonHelper.php';
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }
}

use Joomla\Module\CallNowButton\Site\Helper\CallNowButtonHelper;

// Ensure params is a Registry object
if (!($module->params instanceof Registry)) {
    $module->params = new Registry($module->params);
}

$params = $module->params;

// Check if button is active
if (!$params->get('active', 1)) {
    return;
}

// Check button type and validate accordingly
$buttonType = $params->get('button_type', 'single');
if ($buttonType === 'single') {
    // Get link type for single button
    $linkType = $params->get('link_type', 'phone');
    
    if ($linkType === 'whatsapp' || $linkType === 'phone') {
        // Get phone number for whatsapp or phone
        $phoneNumber = $params->get('phone_number', '');
        if (empty($phoneNumber)) {
            return;
        }
        
        // Additional validation for WhatsApp - must have country code
        if ($linkType === 'whatsapp') {
            // Remove all non-numeric characters
            $phoneDigits = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // WhatsApp requires country code - minimum 10 digits, maximum 15 digits
            if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
                // Invalid phone number - don't render button
                return;
            }
        }
    } elseif ($linkType === 'custom') {
        // Get custom URL
        $customUrl = $params->get('custom_url', '');
        if (empty($customUrl)) {
            return;
        }
    }
} else {
    // For multibutton, check if there are items
    // In Joomla, subform data can be array directly from params
    $multibuttonItems = $params->get('multibutton_items', []);
    
    // Handle JSON string format (sometimes stored as JSON in database)
    if (is_string($multibuttonItems) && !empty(trim($multibuttonItems))) {
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
    } elseif (is_object($multibuttonItems)) {
        // Convert object to array
        $multibuttonItems = (array)$multibuttonItems;
    }
    
    // Ensure it's an array
    if (!is_array($multibuttonItems)) {
        $multibuttonItems = [];
    }
    
    // If empty array, don't return yet - let helper handle it
    // This allows helper's shouldRender() to check properly
}

// Initialize helper
$helper = new CallNowButtonHelper($params);

// Check visibility rules
if (!$helper->shouldRender()) {
    return;
}

// Load CSS and JS
$helper->loadAssets();

// Get button HTML
$buttonHtml = $helper->renderButton();

// Debug: Log if multibutton is empty
if ($buttonType === 'multibutton' && empty($buttonHtml)) {
    // If multibutton but no HTML, try to debug
    $multibuttonItemsDebug = $params->get('multibutton_items', []);
    error_log('CallNowButton Debug - multibutton_items type: ' . gettype($multibuttonItemsDebug));
    if (is_string($multibuttonItemsDebug)) {
        error_log('CallNowButton Debug - multibutton_items string length: ' . strlen($multibuttonItemsDebug));
        error_log('CallNowButton Debug - multibutton_items string: ' . substr($multibuttonItemsDebug, 0, 200));
    }
    if (is_array($multibuttonItemsDebug)) {
        error_log('CallNowButton Debug - multibutton_items array count: ' . count($multibuttonItemsDebug));
    }
}

// Include the template
require ModuleHelper::getLayoutPath('mod_callnowbutton', $params->get('layout', 'default'));

