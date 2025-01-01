<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       27.02.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\String\StringHelper;

jimport('joomla.application.component.viewlegacy');

/**
 * Class CcommentViewComments
 *
 * @since  5.0
 */
class CcommentViewComments extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$settingsModel = JModelLegacy::getInstance('Settings', 'ccommentModel');

		$this->comments = $this->renderComments($this->get('Items'));
		$this->state = $this->get('state');
		$this->pagination = $this->get('Pagination');

		$contentIds = $this->getContentIds($this->comments);

		foreach ($contentIds as $key => $value)
		{
			$plugin = ccommentHelperUtils::getPlugin($key);
			$this->titles[$key] = $plugin->getItemTitles($value);
		}

		$components = $settingsModel->getItems();
		$this->componentList[] = JHtml::_('select.option', '', Jtext::_('JALL'), 'value', 'text');

		foreach ($components as $component)
		{
			$this->componentList[] = JHtml::_('select.option', $component->component, $component->component, 'value', 'text');
		}

		parent::display($tpl);
	}

	/**
	 * Get the contentids for the comments in question
	 *
	 * @param   array  $comments  - array with comments
	 *
	 * @return array
	 */
	private function getContentIds($comments)
	{
		$contentIds = array();

		foreach ($comments as $comment)
		{
			$contentIds[$comment->component][$comment->contentid] = $comment->contentid;
		}

		return $contentIds;
	}

	/**
	 * Prepares the comments for rendering
	 *
	 * @param   array  $comments  - an array with comments
	 *
	 * @return array
	 */
	private function renderComments($comments)
	{
		$i = 0;
		$config = JComponentHelper::getParams('com_comment');
		$length = $config->get('global.comment_length_backend', 140);

		$renderedcomments = array();

		foreach ($comments as $comment)
		{
			if ($comment->notify)
			{
				$notifyimg = "mailgreen.jpg";
				$notifytxt = "notify if new post " . $comment->email;
				$notifyalt = "yes";
			}
			else
			{
				$notifyimg = "mailred.jpg";
				$notifytxt = "not notify if new post " . $comment->email;
				$notifyalt = "no";
			}

			$img = '<img border="0" src="' . JURI::root() . 'media/com_comment/backend/images/' . $notifyimg . '" title="' . $notifytxt . '" alt="' . $notifyalt . '" />';

			if ($comment->email)
			{
				$comment->notify = '<a href="mailto:' . $comment->email . '">' . $img . '</a>';
			}
			else
			{
				$comment->notify = $img;
			}

			$comment->published = JHtml::_('grid.published', $comment, $i, 'publish_g.png', 'publish_x.png', 'comments.notify');
			$comment->delete = '<a href="javascript:return void(0);" onclick="return listItemTask(\'cb' . $i . '\',\'comments.delete\'); "><img src="' . JURI::root() . '/media/com_comment/backend/images/delete_f2.png" width="12" height="12" border="0" alt="" /></a>';

			$comment->checked = JHtml::_('grid.id', $i, $comment->id);
			$comment->link = JRoute::_(Juri::root() . 'index.php?option=com_comment&task=comment.goToComment&id=' . $comment->id);

			if (StringHelper::strlen($comment->comment) > $length)
			{
				$comment->comment = StringHelper::substr($comment->comment, 0, $length) . '...';
			}

			$comment->link_edit = JRoute::_('index.php?option=com_comment&task=comment.edit&id=' . $comment->id);

			if ($comment->userid)
			{
				if ($comment->uname)
				{
					$comment->name = $comment->uname;
				}
				else
				{
					if (!$comment->name)
					{
						$comment->name = JText::_('COM_COMMENT_ANONYMOUS');
					}
				}
			}

			$renderedcomments[] = $comment;
			$i++;
		}

		return $renderedcomments;
	}
}
