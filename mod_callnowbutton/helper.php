<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Call Now Button. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Module\CallNowButton\Site\Helper\CallNowButtonHelper;

/**
 * Helper class for Call Now Button module
 */
class ModCallNowButtonHelper
{
    /**
     * Get the helper instance
     *
     * @param   \Joomla\Registry\Registry  $params  Module parameters
     *
     * @return  CallNowButtonHelper
     */
    public static function getHelper($params)
    {
        return new CallNowButtonHelper($params);
    }
}

