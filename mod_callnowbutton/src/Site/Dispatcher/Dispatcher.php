<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Call Now Button. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\CallNowButton\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// Load helper class if not already loaded
if (!class_exists('Joomla\Module\CallNowButton\Site\Helper\CallNowButtonHelper')) {
    $helperPath = __DIR__ . '/../Helper/CallNowButtonHelper.php';
    if (file_exists($helperPath)) {
        require_once $helperPath;
    }
}

/**
 * Module dispatcher class for Call Now Button module
 *
 * @since  1.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   1.0.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        // Get helper instance
        $helper = $this->getHelperFactory()->getHelper('CallNowButtonHelper', ['params' => $this->module->params]);
        
        // Check if button should render
        if (!$helper->shouldRender()) {
            $data['buttonHtml'] = '';
            return $data;
        }
        
        // Load assets
        $helper->loadAssets();
        
        // Get button HTML
        $data['buttonHtml'] = $helper->renderButton();

        return $data;
    }
}

