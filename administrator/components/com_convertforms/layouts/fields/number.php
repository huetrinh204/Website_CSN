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

extract($displayData);
?>
<input type="number" name="<?php echo $field->input_name ?>" id="<?php echo $field->input_id; ?>"
	<?php if (isset($field->required) && $field->required) { ?>
		required
		aria-required="true"
	<?php } ?>

	<?php if (isset($field->placeholder)) { ?>
		placeholder="<?php echo htmlspecialchars($field->placeholder, ENT_COMPAT, 'UTF-8'); ?>"
	<?php } ?>

	<?php if (isset($field->step) && is_numeric($field->step)) { ?>
		step="<?php echo (float) $field->step; ?>"
	<?php } ?>

	<?php if (isset($field->min) && is_numeric($field->min)) { ?>
		min="<?php echo (float) $field->min; ?>"
	<?php } ?>

	<?php if (isset($field->max) && is_numeric($field->max)) { ?>
		max="<?php echo (float) $field->max; ?>"
	<?php } ?>

	<?php if (isset($field->value) && $field->value != '') { ?>
		value="<?php echo htmlspecialchars($field->value, ENT_COMPAT, 'UTF-8'); ?>"
	<?php } ?>

	<?php if (isset($field->readonly) && $field->readonly == '1') { ?>
		readonly
	<?php } ?>

	<?php if (isset($field->htmlattributes) && !empty($field->htmlattributes)) { ?>
		<?php foreach ($field->htmlattributes as $key => $value) { ?>
			<?php echo $key ?>="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8') ?>"
		<?php } ?>
	<?php } ?>

	class="<?php echo $field->class ?>"
>