<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Mostafa Afrouzi. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Installer\InstallerScript;

/**
 * Installation script for Call Now Button module
 *
 * @since  1.0.0
 */
class Mod_CallnowbuttonInstallerScript extends InstallerScript
{
    /**
     * Extension script constructor.
     *
     * @since   1.0.0
     */
    public function __construct()
    {
        // Joomla 5: PHP 8.1+, Joomla 6: PHP 8.3+
        $this->minimumPhp = '8.1.0';
        $this->minimumJoomla = '5.0.0';
    }

    /**
     * Method to run before an install/update/uninstall method
     *
     * @param   string            $type    The type of change (install, update or discover_install)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function preflight($type, $parent)
    {
        // Load language files early to ensure proper display of name and description
        $lang = Factory::getLanguage();
        $extension = 'mod_callnowbutton';
        
        // Get the path to the extension
        $basePath = $parent->getParent()->getPath('source');
        
        // Get current language tag
        $langTag = $lang->getTag();
        
        // Try multiple paths to load language files
        $paths = [
            $basePath,
            $basePath . '/language/' . $langTag,
            $basePath . '/language/en-GB',
            JPATH_ADMINISTRATOR . '/language/' . $langTag,
            JPATH_ADMINISTRATOR . '/language/en-GB',
        ];
        
        foreach ($paths as $path) {
            $lang->load($extension . '.sys', $path, $langTag, false, false);
            $lang->load($extension . '.sys', $path, 'en-GB', false, false);
            $lang->load($extension, $path, $langTag, false, false);
            $lang->load($extension, $path, 'en-GB', false, false);
        }
        
        return true;
    }

    /**
     * Method to install the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function install($parent)
    {
        // Load language files to ensure proper display
        $lang = Factory::getLanguage();
        $lang->load('mod_callnowbutton', JPATH_SITE . '/modules/mod_callnowbutton', null, true);
        $lang->load('mod_callnowbutton', JPATH_ADMINISTRATOR . '/modules/mod_callnowbutton', null, true);
        
        $app = Factory::getApplication();
        
        $message = '<div style="padding: 20px; background: #f0f8ff; border-left: 4px solid #25D366; margin: 20px 0;">';
        $message .= '<h2 style="color: #25D366; margin-top: 0;">✅ Call Now Button Module Installed Successfully!</h2>';
        $message .= '<p><strong>Thank you for installing Call Now Button Module!</strong></p>';
        $message .= '<p>This module has been successfully installed and is ready to use.</p>';
        $message .= '<hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">';
        $message .= '<h3 style="color: #333; margin-top: 20px;">👨‍💻 Developer Information</h3>';
        $message .= '<p><strong>Mostafa Afrouzi</strong><br>';
        $message .= 'Web Designer & Developer, SEO & Digital Marketing Specialist</p>';
        $message .= '<p><strong>Website:</strong> <a href="https://afrouzi.ir" target="_blank" rel="noopener noreferrer">afrouzi.ir</a><br>';
        $message .= '<strong>Email:</strong> <a href="mailto:mostafa.afrouzi@gmail.com">mostafa.afrouzi@gmail.com</a><br>';
        $message .= '<strong>Phone:</strong> <a href="tel:+989176262858">+98 917 626 2858</a></p>';
        $message .= '<hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">';
        $message .= '<h3 style="color: #333; margin-top: 20px;">🛠️ Professional Services</h3>';
        $message .= '<p>I offer professional services including:</p>';
        $message .= '<ul style="margin-left: 20px;">';
        $message .= '<li>Web & E-commerce Design & Development (Joomla, WordPress, Laravel, Vue.js)</li>';
        $message .= '<li>Technical SEO & Search Engine Optimization</li>';
        $message .= '<li>Google Ads & PPC Advertising</li>';
        $message .= '<li>Marketing Automation</li>';
        $message .= '<li>Mobile App Development</li>';
        $message .= '<li>Custom Joomla Extensions & Modules</li>';
        $message .= '<li>Professional Consultation & Support</li>';
        $message .= '</ul>';
        $message .= '<p><strong>Free Consultation Available</strong> — Contact me for a detailed quote tailored to your project needs.</p>';
        $message .= '<hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">';
        $message .= '<p style="margin-bottom: 0;"><strong>Next Steps:</strong></p>';
        $message .= '<ol style="margin-left: 20px; margin-top: 10px;">';
        $message .= '<li>Go to <strong>Extensions > Modules</strong></li>';
        $message .= '<li>Click <strong>New</strong> and select <strong>Call Now Button</strong></li>';
        $message .= '<li>Configure your button settings</li>';
        $message .= '<li>Assign the module to your desired pages</li>';
        $message .= '</ol>';
        $message .= '</div>';
        
        $app->enqueueMessage($message, 'message');
        
        return true;
    }

    /**
     * Method to update the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function update($parent)
    {
        // Load language files to ensure proper display
        $lang = Factory::getLanguage();
        $lang->load('mod_callnowbutton', JPATH_SITE . '/modules/mod_callnowbutton', null, true);
        $lang->load('mod_callnowbutton', JPATH_ADMINISTRATOR . '/modules/mod_callnowbutton', null, true);
        
        $app = Factory::getApplication();
        
        $message = '<div style="padding: 20px; background: #fff8e1; border-left: 4px solid #ff9800; margin: 20px 0;">';
        $message .= '<h2 style="color: #ff9800; margin-top: 0;">🔄 Call Now Button Module Updated Successfully!</h2>';
        $message .= '<p><strong>Thank you for updating Call Now Button Module!</strong></p>';
        $message .= '<p>The module has been successfully updated to the latest version.</p>';
        $message .= '<hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">';
        $message .= '<h3 style="color: #333; margin-top: 20px;">👨‍💻 Developer Information</h3>';
        $message .= '<p><strong>Mostafa Afrouzi</strong><br>';
        $message .= 'Web Designer & Developer, SEO & Digital Marketing Specialist</p>';
        $message .= '<p><strong>Website:</strong> <a href="https://afrouzi.ir" target="_blank" rel="noopener noreferrer">afrouzi.ir</a><br>';
        $message .= '<strong>Email:</strong> <a href="mailto:mostafa.afrouzi@gmail.com">mostafa.afrouzi@gmail.com</a><br>';
        $message .= '<strong>Phone:</strong> <a href="tel:+989176262858">+98 917 626 2858</a></p>';
        $message .= '<hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">';
        $message .= '<h3 style="color: #333; margin-top: 20px;">🛠️ Professional Services</h3>';
        $message .= '<p>I offer professional services including:</p>';
        $message .= '<ul style="margin-left: 20px;">';
        $message .= '<li>Web & E-commerce Design & Development (Joomla, WordPress, Laravel, Vue.js)</li>';
        $message .= '<li>Technical SEO & Search Engine Optimization</li>';
        $message .= '<li>Google Ads & PPC Advertising</li>';
        $message .= '<li>Marketing Automation</li>';
        $message .= '<li>Mobile App Development</li>';
        $message .= '<li>Custom Joomla Extensions & Modules</li>';
        $message .= '<li>Professional Consultation & Support</li>';
        $message .= '</ul>';
        $message .= '<p><strong>Free Consultation Available</strong> — Contact me for a detailed quote tailored to your project needs.</p>';
        $message .= '</div>';
        
        $app->enqueueMessage($message, 'message');
        
        return true;
    }

    /**
     * Method to run after an install/update/uninstall method
     *
     * @param   string            $type    The type of change (install, update or discover_install)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function postflight($type, $parent)
    {
        // Add update site after install/update
        if ($type === 'install' || $type === 'update') {
            $this->addUpdateSite();
        }

        return true;
    }

    /**
     * Adds update site to the extension
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function addUpdateSite()
    {
        $db = Factory::getDbo();

        // Get extension ID
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('module'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('mod_callnowbutton'));

        $db->setQuery($query);
        $extensionId = $db->loadResult();

        if (!$extensionId) {
            return;
        }

        // Update site URL - should be updated with your GitHub repository
        // Format: https://raw.githubusercontent.com/{OWNER}/{REPO}/{BRANCH}/updates/updates.xml
        $updateUrl = 'https://raw.githubusercontent.com/mostafaafrouzi/joomla-call-now-button/main/updates/updates.xml';

        // Check if update site already exists
        $query = $db->getQuery(true)
            ->select($db->quoteName('update_site_id'))
            ->from($db->quoteName('#__update_sites'))
            ->where($db->quoteName('location') . ' = ' . $db->quote($updateUrl));

        $db->setQuery($query);
        $updateSiteId = $db->loadResult();

        if ($updateSiteId) {
            // Update existing update site
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__update_sites'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->set($db->quoteName('last_check_timestamp') . ' = 0')
                ->where($db->quoteName('update_site_id') . ' = ' . (int) $updateSiteId);

            $db->setQuery($query);
            $db->execute();
        } else {
            // Insert new update site
            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__update_sites'))
                ->set($db->quoteName('name') . ' = ' . $db->quote('Call Now Button - GitHub'))
                ->set($db->quoteName('type') . ' = ' . $db->quote('collection'))
                ->set($db->quoteName('location') . ' = ' . $db->quote($updateUrl))
                ->set($db->quoteName('enabled') . ' = 1')
                ->set($db->quoteName('last_check_timestamp') . ' = 0');

            $db->setQuery($query);
            $db->execute();
            $updateSiteId = $db->insertid();

            // Link update site to extension
            if ($updateSiteId) {
                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__update_sites_extensions'))
                    ->set($db->quoteName('update_site_id') . ' = ' . (int) $updateSiteId)
                    ->set($db->quoteName('extension_id') . ' = ' . (int) $extensionId);

                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    /**
     * Method to uninstall the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function uninstall($parent)
    {
        return true;
    }
}

