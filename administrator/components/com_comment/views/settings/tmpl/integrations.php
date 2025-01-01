<?php
/**
 * @author Daniel Dimitrov - compojoom.com
 * @date: 11.04.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
?>
<div class="form-horizontal">
	<?php $fieldsets = $this->form->getFieldsets('integrations'); ?>
	<?php foreach ($fieldsets as $key => $value) : ?>
		<div class="col-sm-6">
			<h2><?php echo JText::_($value->label); ?></h2>

			<?php $fields = $this->form->getFieldset($key); ?>

			<?php foreach ($fields as $field) : ?>
				<?php
				if (!CCOMMENT_PRO)
				{
					$fieldClass = $this->form->getFieldAttribute($field->fieldname, 'class', '', 'integrations');

					if (strstr($fieldClass, 'ccomment-pro'))
					{
						$title = $field->title . ' <span class="ccomment-pro">*</span>';
						$this->form->setFieldAttribute($field->fieldname, 'label', $title, 'integrations');
					}
				}
				?>
				<div class="form-group">
						<?php echo $field->label; ?>
					<div class="col-sm-8">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
</div>