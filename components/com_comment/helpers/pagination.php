<?php
/**
 * @author     Daniel Dimitrov - compojoom.com
 * @date       : 13.02.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class CcommentHelperPagination
 *
 * @since  5.0
 */
class CcommentHelperPagination
{
	private $commentId;

	private $itemId;

	private $component;

	/**
	 * The constructor
	 *
	 * @param   int     $commentId  - the comment id
	 * @param   int     $itemId     - the article/video id
	 * @param   string  $component  - the component name
	 */
	public function __construct($commentId, $itemId, $component)
	{
		$this->commentId = $commentId;
		$this->itemId = $itemId;
		$this->component = $component;
		$this->config = ccommentConfig::getConfig($component);
	}

	/**
	 * Finds on which page the comment should be placed
	 *
	 * @return int|mixed
	 */
	public function findPage()
	{
		$page = 0;

		if ($this->config->get('layout.comments_per_page'))
		{
			$page = $this->getPage($this->commentId);
		}

		return $page;
	}

	/**
	 * Does the actual calculation on which page the comment should be placed
	 *
	 * @param   int  $commentId  - the comment id we are looking for
	 *
	 * @return int|mixed
	 */
	private function getPage($commentId)
	{
		$model = JModelLegacy::getInstance('Comment', 'ccommentModel');
		$comment = $model->getComment($commentId);
		$page = 0;

		if ($comment)
		{
			if ($comment->parentid == -1)
			{
				$db = JFactory::getDbo();
				$filter = '';

				if ($this->config->get('layout.sort') == 0)
				{
					$filter = $db->qn('id') . '<=' . $db->q($commentId);
				}

				if ($this->config->get('layout.sort') == 1)
				{
					$filter = $db->qn('id') . '>=' . $db->q($commentId);
				}

				if ($this->config->get('layout.sort') == 2)
				{
					$position = $this->findPositionOfCommentInThePagination($commentId);
					$page = max(ceil($position / $this->config->get('layout.comments_per_page')), 1);
				}

				if ($this->config->get('layout.sort') == 3)
				{
					$position = $this->findPositionOfCommentInThePagination($commentId);
					$page = max(ceil($position / $this->config->get('layout.comments_per_page')), 1);
				}

				if ($filter)
				{
					$count = $model->countComments($this->itemId, $this->component, true, $filter);
					$page = max(ceil($count / $this->config->get('layout.comments_per_page')), 1);
				}
			}
			else
			{
				// Try to find the parent comment again
				return $this->getPage($comment->parentid);
			}
		}

		return $page;
	}

	/**
	 * Finds the position of the comment in the list
	 * http://stackoverflow.com/a/23799594/471574
	 *
	 * @param   int  $commentId  - the comment id to look for
	 *
	 * @return int
	 */
	private function findPositionOfCommentInThePagination($commentId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$derived = $db->getQuery(true);

		$query->select(array('d.myRowSerial'));
		$query->from(
			'(' . $derived->select('*, @rownum:=@rownum + 1 AS myRowSerial ')
				->from('jos_comment, (SELECT @rownum:=0) AS nothingButSetInitialValue ')
				->where('parentid=-1')
				->order(CcommentHelperComment::getOrdering($this->config)) .
			') d'
		)->where('d.id =' . $db->q($commentId))
			->where('contentid=' . $db->quote($this->itemId))
			->where('component=' . $db->quote($this->component));

		$db->setQuery($query);

		$result = $db->loadObject();

		if ($result)
		{
			return $result->myRowSerial;
		}

		return 1;
	}
}
