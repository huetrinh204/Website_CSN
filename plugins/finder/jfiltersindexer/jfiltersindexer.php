<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ConfigContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\ContextInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

class PlgFinderJfiltersindexer extends Adapter
{
	/**
	 * Store the contexts that need indexing.
	 *
	 * @var array
	 */
	protected static $contextNeedsIndex = [];

    /**
     * Check if indexing needed to add records to '#__jfilters_links_items' table.
     *
     * @param ContextInterface $context
     * @return bool
     * @since 1.0.0
     */
	public static function needsIndexing(ContextInterface $context): bool
	{
		if (!isset(self::$contextNeedsIndex[$context->getName()]))
		{
			self::$contextNeedsIndex[$context->getName()] = false;
			/** @var DatabaseDriver $db */
			$db = Factory::getContainer()->get('DatabaseDriver');
            $contextName = $context->getName();
            $contextAlias = $context->getAlias();

			// 1st check if our index table has records.
			$query = $db->getQuery(true);
			$query->select($db->quoteName('link_id'))
				->from($db->quoteName('#__jfilters_links_items'))
				->where($db->quoteName('context') . ' = :context')
				->bind(':context', $contextName, ParameterType::STRING);
			$db->setQuery($query);
			$jfilters_link_ids = $db->loadColumn();

			$query = $db->getQuery(true);
			$query->select($db->quoteName('link_id'))
				->from($db->quoteName('#__finder_links', 'links'))
				->innerJoin($db->quoteName('#__finder_types',
						'types') . ' ON ' . $db->quoteName('types.id') . ' = ' . $db->quoteName('links.type_id'))
				->where($db->quoteName('types.title') . ' = :title')
				->bind(':title', $contextAlias, ParameterType::STRING);
			$db->setQuery($query);
			$link_ids = $db->loadColumn();

			// If the records in '#__jfilters_links_items' are not the same number as in '#__finder_links', needs indexing.
			if (count($jfilters_link_ids) != count($link_ids))
			{
				self::$contextNeedsIndex[$context->getName()] = true;
			}
		}

		return self::$contextNeedsIndex[$context->getName()];
	}

	/**
     * Triggered after saving an item or after running the indexer.
     * Performs the indexing in our table.
     *
     * @param Result $item
     * @param $link_id
     * @return bool
     */
    public function onFinderIndexAfterIndex(Result $item, $link_id)
    {
        $taxonomy = $item->getTaxonomy('Type');
        $taxonomy = reset($taxonomy);
        if (isset($taxonomy->title)) {
            /** @var ConfigContextCollection $contextConfigCollection */
            $contextConfigCollection = ObjectManager::getInstance()->getObject(ConfigContextCollection::class);
            /** @var ContextInterface $contextCurrent */
            $contextCurrent = $contextConfigCollection->getByAttribute('alias', $taxonomy->title);
            if ($contextCurrent) {
                $contextIdField = $contextCurrent->getItem()->getId()->getDbColumn();
            }
        }

        // We do not use such context in JFilters
        if (!isset($contextIdField) || empty($contextIdField) && !isset($item->$contextIdField)) {
            return false;
        }

        //there is no such record
        if (empty($link_id)) {
            $this->removeRelationship($item);
            return false;
        }

        $entry = new \stdClass();
        $entry->link_id = $link_id;
        $entry->item_id = $item->$contextIdField;
        $entry->context = $this->getExtension($item) . '.' . $this->getLayout($item);

        if (empty($this->getItem($link_id))) {
            //remove other records of that item (with different link_id)
            $this->removeRelationship($item);
            $this->db->insertObject('#__jfilters_links_items', $entry);
        } else {
            $this->db->updateObject('#__jfilters_links_items', $entry, 'link_id');
        }
        return true;
    }

    /**
     * Triggered after deleting an item or pressing 'Delete' in com_finder.
     *
     * @param int $linkId
     */
    public function onFinderIndexAfterDelete($linkId)
    {
        $this->removeRelationship('', $linkId);
    }

    /**
     * Triggered after purging (press 'Clear Index') in com_finder.
     */
    public function onFinderIndexAfterPurge()
    {
        $this->db->truncateTable('#__jfilters_links_items');
    }

    /**
     *  Delete the record from the table
     *
     * @param string|Result $row
     * @param int $link_id
     * @return bool
     */
    protected function removeRelationship($row = '', $link_id = 0)
    {
        $query = $this->db->getQuery(true)
            ->delete($this->db->quoteName('#__jfilters_links_items'));
        if ($link_id) {
            $query->where($this->db->quoteName('link_id') . '= :link_id')
                ->bind(':link_id', $link_id, ParameterType::INTEGER);
            /** @var Result $row */
        } elseif (is_object($row)) {
            $rowId = $row->id;
            $rowContext = $row->context;
            $query->where($this->db->quoteName('item_id') . '= :itemId AND ' . $this->db->quoteName('context') . ' = :context')
                ->bind(':itemId', $rowId, ParameterType::INTEGER)
                ->bind(':context', $rowContext, ParameterType::STRING);
        }
        $this->db->setQuery($query)->execute();
        return true;
    }

    /**
     * Get item from the db table
     *
     * @param int $link_id
     * @return array
     */
    protected function getItem($link_id)
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['link_id', 'item_id', 'context']))
            ->from($this->db->quoteName('#__jfilters_links_items'))
            ->where($this->db->quoteName('link_id') . ' = :link_id')
            ->bind(':link_id', $link_id, ParameterType::INTEGER);
        $this->db->setQuery($query);
        return $this->db->loadResult();
    }

    /**
     * Get the extension name
     *
     * @param Result $row
     * @return string
     */
    protected function getExtension(Result $row)
    {
        if (!empty($row->extension)) {
            return $row->extension;
        }

        // Not every result has a context (e.g. contacts are missing it)
        if ($row->context) {
            $parts = explode('.', $row->context);
            $extension = $parts[0];
        }
        else {
            $taxonomy = $row->getTaxonomy('Type');
            $taxonomy = reset($taxonomy);
            $extension = $taxonomy->title == 'Contact' ? 'com_contact' : '';
        }

        return $extension;
    }

    /**
     * Get the layout name
     *
     * @param Result $row
     * @return string
     */
    protected function getLayout(Result $row)
    {
        if (!empty($row->layout)) {
            return $row->layout;
        }
        $parts = explode('.', $row->context);
        return !empty($parts) && isset($parts[1]) ? $parts[1] : '';
    }

    /**
     * @param Result $item
     * @return bool
     */
    protected function index(Result $item)
    {
        return true;
    }

    /**
     * @return bool
     */
    protected function setup()
    {
        return true;
    }

    /**
     * If not defined an error is generated by the Adapter
     *
     * @param mixed $query A DatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     *
     */
    protected function getListQuery($query = null)
    {
        $db = $this->db;

        // Check if we can use the supplied SQL query.
        $query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true)
            ->select('1');

        return $query;
    }

    /**
     * Method to remove outdated index entries.
     * No need to delete or return the deleted items here.
     * This is done by the parent function which executes a onFinderIndexAfterDelete() on every deleted item.
     *
     * @return int
     * @since 1.0.1
     */
    public function onFinderGarbageCollection()
    {
        return 0;
    }
}
