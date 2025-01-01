<?php
/**
 * @author     Daniel Dimitrov - compojoom.com
 * @date: 15.02.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
?>
<div class="form-horizontal">
	<?php $fieldsets = $this->form->getFieldsets('template'); ?>
	<?php foreach ($fieldsets as $key => $value) : ?>
		<div class="">
			<h2><?php echo JText::_($value->label); ?></h2>

			<?php $fields = $this->form->getFieldset($key); ?>

			<?php foreach ($fields as $field) : ?>
				<?php
				if (!CCOMMENT_PRO)
				{
					$fieldClass = $this->form->getFieldAttribute($field->fieldname, 'class', '', 'template');

					if (strstr($fieldClass, 'ccomment-pro'))
					{
						$title = $field->title . ' <span class="ccomment-pro">*</span>';
						$this->form->setFieldAttribute($field->fieldname, 'label', $title, 'template');
					}
				}
				?>
				<div class="form-group">
						<?php echo $field->label; ?>
					<div class="col-sm-4">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
</div>

<div id="template-params" class="row">

</div>