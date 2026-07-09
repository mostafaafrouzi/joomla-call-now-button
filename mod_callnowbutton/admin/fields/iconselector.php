<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Mostafa Afrouzi. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;
use Joomla\Module\CallNowButton\Site\Helper\IconRepository;

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
        $this->loadIconRepository();

        $value = $this->value ?: 'phone';
        $icons = IconRepository::getSelectorIcons();

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

        $this->registerAssets();

        return $html;
    }

    /**
     * Ensure IconRepository is available in admin context.
     *
     * @return  void
     *
     * @since   1.1.0
     */
    protected function loadIconRepository()
    {
        if (class_exists(IconRepository::class, false)) {
            return;
        }

        $path = dirname(__DIR__, 2) . '/src/Site/Helper/IconRepository.php';

        if (is_file($path)) {
            require_once $path;
        }
    }

    /**
     * Register admin assets once per request.
     *
     * @return  void
     *
     * @since   1.1.0
     */
    protected function registerAssets()
    {
        static $assetsLoaded = false;

        if ($assetsLoaded) {
            return;
        }

        $doc = Factory::getDocument();
        $base = Uri::root() . 'modules/mod_callnowbutton/admin';

        $doc->addStyleSheet($base . '/css/icon-selector.css', ['version' => 'auto']);
        $doc->addScript($base . '/js/icon-selector.js', ['version' => 'auto'], ['defer' => true]);

        $validationPath = JPATH_SITE . '/modules/mod_callnowbutton/admin/js/form-validate.js';

        if (file_exists($validationPath)) {
            $doc->addScript(Uri::root() . 'modules/mod_callnowbutton/admin/js/form-validate.js', ['version' => 'auto'], ['defer' => true]);
        }

        $assetsLoaded = true;
    }
}
