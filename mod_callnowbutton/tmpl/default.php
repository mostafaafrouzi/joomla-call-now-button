<?php
/**
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Mostafa Afrouzi. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<!-- Call Now Button Module -->
<?php echo $assetMarkup ?? ''; ?>
<div class="cnb-wrapper <?php echo htmlspecialchars($wrapperClass ?? 'cnb-display-all', ENT_QUOTES, 'UTF-8'); ?>"<?php echo !empty($wrapperId) ? ' id="' . htmlspecialchars($wrapperId, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
    <?php echo $buttonHtml; ?>
</div>

