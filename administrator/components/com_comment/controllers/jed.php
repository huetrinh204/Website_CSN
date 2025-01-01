<?php
/**
 * @package    Com_Comment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       26.04.2015
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * The updates provisioning Controller
 *
 * @since  4.0
 */
class CcommentControllerJed extends CompojoomControllerJed
{
	protected $component = 'com_comment';

	protected $isPro = CCOMMENT_PRO;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   12.2
	 */
	public function __construct($config = array())
	{
		$url = 'http://extensions.joomla.org/extensions/extension/contacts-and-feedback/articles-comments/ccomment-pro';

		if (!$this->isPro)
		{
			$url = 'http://extensions.joomla.org/extensions/extension/contacts-and-feedback/articles-comments/ccomment-core';
		}

		$this->data = array(
				'component' => $this->component,
				'title' => 'CComment ' . ($this->isPro ? 'PRO' : 'Core'),
				'jed_url' => $url
		);

		parent::__construct();
	}
}
