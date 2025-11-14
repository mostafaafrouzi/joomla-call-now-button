<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Call Now Button. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\CallNowButton\Site\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Extension\ModuleInterface;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Helper\HelperFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Module\CallNowButton\Site\Helper\CallNowButtonHelper;

/**
 * The Call Now Button module service provider.
 *
 * @since  1.0.0
 */
class CallNowButtonModule implements ModuleInterface, ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function register(Container $container)
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\Joomla\\Module\\CallNowButton'));
        $container->registerServiceProvider(new HelperFactory('\\Joomla\\Module\\CallNowButton\\Site\\Helper'));
    }
}

