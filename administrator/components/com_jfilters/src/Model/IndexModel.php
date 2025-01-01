<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ConfigContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\ContextInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\ParameterType;

class IndexModel extends ListModel
{
	/**
	 * Delete the indexes of the used contexts.
	 *
	 * @return array
	 * @throws \ReflectionException
	 * @since 1.0.0
	 */
	public function delete()
	{
		$pks = [];
		// Get the contexts that need indexing.
        /** @var ConfigContextCollection $contextConfigCollection */
		$contextConfigCollection = ObjectManager::getInstance()->getObject(ConfigContextCollection::class);
		$contextToBeIndexed      = [];
		if ($contextConfigCollection->getSize() > 0)
		{
			PluginHelper::importPlugin('finder');
			/** @var ContextInterface $context */
			foreach ($contextConfigCollection as $context)
			{
				$needsIndexing = \PlgFinderJfiltersindexer::needsIndexing($context);
				if ($needsIndexing === true)
				{
					$contextToBeIndexed[] = $context->getAlias();
				}
			}
		}

		if($contextToBeIndexed)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('link_id'))
				->from($db->quoteName('#__finder_links', 'links'))
				->innerJoin($db->quoteName('#__finder_types',
						'types') . ' ON ' . $db->quoteName('types.id') . ' = ' . $db->quoteName('links.type_id'))
				->whereIn($db->quoteName('types.title'), $contextToBeIndexed, ParameterType::STRING);

			$db->setQuery($query);
			$pks = $db->loadColumn();

			if (!empty($pks))
			{
				/** @var \Joomla\Component\Finder\Administrator\Model\IndexModel $finderModel */
				$finderModel = Factory::getApplication()->bootComponent('com_finder')->getMVCFactory()->createModel('Index',
					'Administrator', ['ignore_request' => true]);
				$finderModel->delete($pks);
			}
		}

		return $pks;
	}
}