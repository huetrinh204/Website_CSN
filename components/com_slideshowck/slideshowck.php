<?php
/**
 * @name		Slideshow CK
 * @package		com_slideshowck
 * @copyright	Copyright (C) 2019. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Cedric Keiflin - https://www.template-creator.com - https://www.joomlack.fr
 */


// no direct access
defined('_JEXEC') or die;
if (! defined('CK_LOADED')) define('CK_LOADED', 1);

include_once JPATH_ADMINISTRATOR . '/components/com_slideshowck/helpers/defines.php';

// Access check.
if (!\Joomla\CMS\Factory::getUser()->authorise('core.edit', 'com_slideshowck')) {
	return JError::raiseWarning(404, \Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'));
}

// loads the language files from the frontend
$lang	= \Joomla\CMS\Factory::getLanguage();
$lang->load('com_slideshowck', JPATH_SITE . '/components/com_slideshowck', $lang->getTag(), false);
$lang->load('com_slideshowck', JPATH_SITE, $lang->getTag(), false);

// loads the helper in any case
require_once SLIDESHOWCK_PATH . '/helpers/cktext.php';
require_once SLIDESHOWCK_PATH . '/helpers/ckpath.php';
require_once SLIDESHOWCK_PATH . '/helpers/ckfile.php';
require_once SLIDESHOWCK_PATH . '/helpers/ckfolder.php';
require_once SLIDESHOWCK_PATH . '/helpers/ckfof.php';
require_once SLIDESHOWCK_PATH . '/helpers/helper.php';
require_once SLIDESHOWCK_PATH . '/helpers/ckframework.php';
require_once SLIDESHOWCK_PATH . '/helpers/ckcontroller.php';
require_once SLIDESHOWCK_PATH . '/helpers/ckmodel.php';
require_once SLIDESHOWCK_PATH . '/helpers/ckview.php';

\Slideshowck\CKFramework::load();

// Include dependancies
require_once SLIDESHOWCK_PATH . '/controller.php';

$controller	= \Joomla\CMS\MVC\Controller\BaseController::getInstance('Slideshowck');
$controller->execute(\Joomla\CMS\Factory::getApplication()->input->get('task'));
