<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       24.01.15
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

$user = JFactory::getUser();

if (!$user->authorise('core.manage', 'com_comment'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

require_once(JPATH_ADMINISTRATOR. '/components/com_comment/version.php');

require_once JPATH_LIBRARIES . '/compojoom/include.php';

JLoader::discover('ccomment', JPATH_ADMINISTRATOR. '/components/com_comment/library');
JLoader::discover('ccommentHelper', JPATH_ADMINISTRATOR. '/components/com_comment/helpers');
JLoader::discover('ccommentHelper', JPATH_SITE . '/components/com_comment/helpers');

require_once(JPATH_COMPONENT_ADMINISTRATOR .'/controller.php' );

// Load language
CompojoomLanguage::load('com_comment', JPATH_SITE);
CompojoomLanguage::load('com_comment', JPATH_ADMINISTRATOR);
CompojoomLanguage::load('com_comment.sys', JPATH_ADMINISTRATOR);

$input = JFactory::getApplication()->input;

$controller = JControllerLegacy::getInstance('ccomment');
$controller->execute($input->getCmd('task'));
$controller->redirect();
