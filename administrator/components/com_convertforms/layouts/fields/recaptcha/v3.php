<?php

/**
 * @package         Convert Forms
 * @version         4.4.8 Free
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

extract($displayData);

Text::script('COM_CONVERTFORMS_RECAPTCHA_NOT_LOADED');
HTMLHelper::_('script', 'https://www.google.com/recaptcha/api.js?render=' . $site_key . '&render=explicit&hl=' . Factory::getLanguage()->getTag());

HTMLHelper::_('script', 'com_convertforms/recaptcha_v3.js', ['version' => 'auto', 'relative' => true]);

?>
<div class="nr-recaptcha g-v3-recaptcha" data-sitekey="<?php echo $site_key; ?>"></div>
<input type="hidden" class="g-recaptcha-response" name="g-recaptcha-response" />

<?php if ($badge === 'inline'): ?>
	<style>
		.grecaptcha-badge {
			visibility: hidden;
		}
	</style>
	<div class="cf-recaptcha-v3-text-badge"><?php echo Text::_('COM_CONVERTFORMS_RECAPTCHA_V3_TEXT_BADGE'); ?></div>
<?php endif; ?>