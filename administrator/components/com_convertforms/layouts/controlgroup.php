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

extract($displayData);

$cssclass = isset($field->cssclass) ? $field->cssclass : '';

// Load Input Masking
if (isset($field->inputmask) && is_array($field->inputmask) && ((!empty($field->inputmask['options']) && $field->inputmask['options'] !== 'custom') || ($field->inputmask['options'] === 'custom' && !empty($field->inputmask['custom']))))
{
	HTMLHelper::script('com_convertforms/vendor/inputmask.min.js', ['relative' => true, 'version' => 'auto']);
	HTMLHelper::script('com_convertforms/inputmask.js', ['relative' => true, 'version' => 'auto']);

	Text::script('COM_CONVERTFORMS_ERROR_INPUTMASK_INCOMPLETE');
}

// Safe label is used by the getFieldsArray()
$safeLabel = isset($field->label) ? htmlspecialchars(trim(strip_tags($field->label)), ENT_NOQUOTES, 'UTF-8') : null;

$helpTextPosition = $form['params']->get('help_text_position', 'after');

?>

<div class="cf-control-group <?php echo $cssclass; ?>" data-key="<?php echo $field->key; ?>" data-name="<?php echo $field->name; ?>" <?php echo $safeLabel ? 'data-label="' . $safeLabel . '"' : '' ?> data-type="<?php echo $field->type ?>" <?php echo (isset($field->required) && $field->required) ? 'data-required' : '' ?>>
	<?php if (isset($field->hidelabel) && !$field->hidelabel && !empty($field->label)) { ?>
		<div class="cf-control-label">
			<label class="cf-label" for="<?php echo $field->input_id; ?>">
				<?php echo $field->label ?>
				<?php if ($form['params']->get('required_indication', true) && $field->required) { ?>
					<span class="cf-required-label">*</span>
				<?php } ?>
			</label>
		</div>
	<?php } ?>
	<div class="cf-control-input">
		<?php 
			if ($helpTextPosition == 'before')
			{
				include __DIR__ . '/helptext.php';
			}

			echo $field->input; 

			if ($helpTextPosition == 'after')
			{
				include __DIR__ . '/helptext.php';
			}
		?>
	</div>
</div>