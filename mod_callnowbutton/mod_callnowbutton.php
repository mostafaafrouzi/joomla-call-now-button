<?php
/**
 * Legacy module entry point (fallback when Dispatcher is unavailable).
 * Joomla 5+ renders this module via src/Site/Dispatcher/Dispatcher.php.
 *
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Mostafa Afrouzi. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\CallNowButton\Site\Helper\CallNowButtonHelper;
use Joomla\Registry\Registry;

if (!class_exists(CallNowButtonHelper::class)) {
    $helperDir = __DIR__ . '/src/Site/Helper';

    if (is_file($helperDir . '/IconRepository.php')) {
        require_once $helperDir . '/IconRepository.php';
    }

    require_once $helperDir . '/CallNowButtonHelper.php';
}

if (!($module->params instanceof Registry)) {
    $module->params = new Registry($module->params);
}

$helper = new CallNowButtonHelper(['params' => $module->params, 'module' => $module]);

if (!$helper->shouldRender()) {
    return;
}

$helper->loadAssets();
$buttonHtml = $helper->renderButton();
$wrapperId = $helper->getWrapperId();
$wrapperClass = $helper->getDisplayModeClass();
$assetMarkup = $helper->getFrontendAssetMarkup();

require ModuleHelper::getLayoutPath('mod_callnowbutton', $module->params->get('layout', 'default'));
