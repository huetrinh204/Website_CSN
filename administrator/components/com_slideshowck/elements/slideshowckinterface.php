<?php
/**
 * @copyright	Copyright (C) 2017 Cedric KEIFLIN alias ced1870
 * http://www.joomlack.fr
 * @license		GNU/GPL
 * */

defined('JPATH_PLATFORM') or die;

use Slideshowck\CKFramework;

include_once JPATH_ROOT . '/administrator/components/com_slideshowck/helpers/ckframework.php';
include_once JPATH_ROOT . '/administrator/components/com_slideshowck/helpers/defines.php';

\Joomla\CMS\Form\FormHelper::loadFieldClass('hidden');
CKFramework::load();
// custom class extension for J3 compatibility
if (class_exists('\Joomla\CMS\Form\Field\HiddenField')) {
	class JFormFieldSlideshowckinterfaceBase extends \Joomla\CMS\Form\Field\HiddenField {}
} else {
	class JFormFieldSlideshowckinterfaceBase extends JFormFieldHidden {}	
}

class JFormFieldSlideshowckinterface extends JFormFieldSlideshowckinterfaceBase
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 */
	protected $type = 'slideshowckinterface';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 */
	protected function getLabel()
	{
		return '';
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 */
	protected function getInput()
	{
		// loads the language files from the frontend
		$lang	= \Joomla\CMS\Factory::getLanguage();
		$lang->load('com_slideshowck', JPATH_SITE . '/components/com_slideshowck', $lang->getTag(), false);
		$lang->load('com_slideshowck', JPATH_SITE, $lang->getTag(), false);

		if (version_compare(JVERSION, '4') >= 0) {
		$css = '.slideshowck-field-suffix {
	display: inline-block;
	line-height: 25px;
	transform: translate(0, -50%);
	position: absolute;
	top: 20px;
	height: 25px;
	right: 20px;
}

.slideshowck-field-icon {
	display: inline-block;
	vertical-align: top;
	margin-top: 10px;
	width: 20px;
}

.slideshowck-field-icon + input,
.slideshowck-field-icon + fieldset,
.slideshowck-field-icon + select {
	display: inline-block;
	width: calc(100% - 30px);
}

.ckbutton-group input[type="text"] {
	min-height: 28px;
	box-sizing: border-box;
	font-size: 13px;
}';
		} else {
			$css = '.slideshowck-field-icon {
	display: inline-block;
	vertical-align: top;
	margin-top: 4px;
	width: 20px;
}';
		}

		$doc = \Joomla\CMS\Factory::getDocument();
		$doc->addStyleDeclaration($css);

		return '';
	}
}
