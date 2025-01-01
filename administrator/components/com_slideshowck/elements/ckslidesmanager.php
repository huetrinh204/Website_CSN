<?php

/**
 * @copyright	Copyright (C) 2011 Cedric KEIFLIN alias ced1870
 * https://www.joomlack.fr
 * @license		GNU/GPL
 * */
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once 'ckformfield.php';
require_once JPATH_ADMINISTRATOR . '/components/com_slideshowck/helpers/ckframework.php';
require_once JPATH_ADMINISTRATOR . '/components/com_slideshowck/helpers/helper.php';

Slideshowck\CKFramework::load();
SlideshowckHelper::loadCkbox();

\Joomla\CMS\Language\Text::script('SLIDESHOWCK_ADDSLIDE');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_SELECTIMAGE');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_SELECT_LINK');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_REMOVE2');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_SELECT');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_CAPTION');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_USETOSHOW');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_IMAGE');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_VIDEO');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_TEXTOPTIONS');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_IMAGEOPTIONS');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_LINKOPTIONS');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_VIDEOOPTIONS');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_ALIGNEMENT_LABEL');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_TOPLEFT');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_TOPCENTER');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_TOPRIGHT');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_MIDDLELEFT');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_CENTER');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_MIDDLERIGHT');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_BOTTOMLEFT');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_BOTTOMCENTER');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_BOTTOMRIGHT');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_LINK');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_TARGET');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_SAMEWINDOW');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_NEWWINDOW');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_VIDEOURL');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_REMOVE');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_IMPORTFROMFOLDER');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_ARTICLEOPTIONS');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_SLIDETIME');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_CLEAR');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_SELECT');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_TITLE');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_STARTDATE');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_ENDDATE');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_SAVE');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_TEXT_CUSTOM');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_ARTICLE');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_TEXT');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_VIDEO_AUTOPLAY');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_VIDEO_LOOP');
\Joomla\CMS\Language\Text::script('SLIDESHOWCK_VIDEO_CONTROLS');
\Joomla\CMS\Language\Text::script('CK_SAVE_CLOSE');

class JFormFieldCkslidesmanager extends CKFormField {

	protected $type = 'ckslidesmanager';

	protected function getInput() {

		// loads the language files from the frontend
		$lang	= \Joomla\CMS\Factory::getLanguage();
		$lang->load('com_slideshowck', JPATH_SITE . '/components/com_slideshowck', $lang->getTag(), false);
		$lang->load('com_slideshowck', JPATH_SITE, $lang->getTag(), false);

		require_once(JPATH_ROOT . '/administrator/components/com_slideshowck/helpers/defines.js.php');
		$path = 'media/com_slideshowck/assets/elements/ckslidesmanager/';
		\Joomla\CMS\HTML\HTMLHelper::_('jquery.framework');
		// \Joomla\CMS\HTML\HTMLHelper::_('jquery.ui', array('core', 'sortable'));
		\Joomla\CMS\HTML\HTMLHelper::_('script', 'media/com_slideshowck/assets/jquery-uick-custom.js');
		\Joomla\CMS\HTML\HTMLHelper::_('script', 'media/com_slideshowck/assets/admin.js');
		\Joomla\CMS\HTML\HTMLHelper::_('script', $path . 'ckslidesmanager.js');
		if (\Slideshowck\CKFof::isSite()) {
			\Joomla\CMS\HTML\HTMLHelper::_('stylesheet', 'media/com_slideshowck/assets/front-edition.css');
		}
		
		\Joomla\CMS\HTML\HTMLHelper::_('stylesheet', 'media/com_slideshowck/assets/jquery-ui.min.css');
		\Joomla\CMS\HTML\HTMLHelper::_('stylesheet', $path . 'ckslidesmanager.css');

		$html = '<input name="' . $this->name . '" id="ckslides" type="hidden" value="' . $this->value . '" />'
				. '<div class="ckaddslide ckbutton ckbutton-success" onclick="javascript:ckAddSlide(false, \'top\');"><i class="far fa-plus-square"></i> ' . \Joomla\CMS\Language\Text::_('SLIDESHOWCK_ADDSLIDE') . '</div>'
				. '<ul id="ckslideslist" class="ckinterface" style="clear:both;"></ul>'
				. '<div class="ckaddslide ckbutton ckbutton-success" onclick="javascript:ckAddSlide();"><i class="far fa-plus-square"></i> ' . \Joomla\CMS\Language\Text::_('SLIDESHOWCK_ADDSLIDE') . '</div>';

		return $html;
	}

	protected function getLabel() {

		return '';
	}
}

