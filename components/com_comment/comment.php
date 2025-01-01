<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       22.10.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// Load the compojoom framework
require_once JPATH_LIBRARIES . '/compojoom/include.php';

JLoader::discover('ccommentHelper', JPATH_SITE . '/components/com_comment/helpers/');
JLoader::discover('ccomment', JPATH_ADMINISTRATOR . '/components/com_comment/library/');

ccommentHelperUtils::loadLanguage();

$controller = JControllerLegacy::getInstance('ccomment');
$controller->execute(JFactory::getApplication()->input->getCmd('task', ''));
$controller->redirect();
