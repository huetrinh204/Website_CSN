<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ConfigContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\SubFormField;
use Bluecoder\Component\Jfilters\Administrator\Model\SubFormField\Indexer;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseQuery;

class PlgContentJfilters extends CMSPlugin
{
    /**
     * Check if we need to index subform field values.
     *
     * @return bool
     * @throws ReflectionException
     * @since 1.0.0
     */
    public static function isSubformFieldValueNeedsIndexing()
    {
        $needsIndexing = false;
        /** @var Indexer $indexer */
        $indexer = ObjectManager::getInstance()->getObject(Indexer::class);
        $indexer->clearState();
        $total = $indexer->getState()->total;

        // We have subform field value records. Lets see if our table is empty.
        if($total) {
            /** @var \Joomla\Database\DatabaseDriver $db */
            $db = Factory::getContainer()->get('DatabaseDriver');
            /** @var DatabaseQuery $query */
            $query = $db->getQuery(true);
            $query->select('*')
                ->from($db->quoteName('#__jfilters_fields_subform_values'));
            $db->setQuery($query,0,1);
            $result = $db->loadObject();
            $needsIndexing = empty($result);
        }
        return $needsIndexing;
    }

    /**
     * Index the subform fields, to create the necessary db records.
     *
     * @param string $context
     * @param Table $item
     * @param bool $isNew
     * @param array $data
     *
     * @throws ReflectionException
     * @throws Exception
     * @since 1.0.0
     */
    public function onContentAfterSave($context, $item, $isNew, $data = []): void
    {

        // In the front-end editing the context is different
        if ($context === 'com_content.form') {
            $context = 'com_content.article';
        } elseif ($context === 'com_contact.form') {
            $context = 'com_contact.contact';
        }

        /** @var ConfigContextCollection $contextConfigCollection */
        $contextConfigCollection = ObjectManager::getInstance()->getObject(ConfigContextCollection::class);

        // Exhaust all the possible ways to get the id. We do that because 3rd party components maybe not pass the Table here or do not use the 'id' as pk.
        $itemId = method_exists($item, 'getId') ? $item->getId() : ($item->id ?: 0);

        // Check that we are dealing with supported contexts (e.g. com_content.article), the item is saved and there are fields assigned.
        if ($contextConfigCollection->getByNameAttribute($context) === null
            || empty($itemId) || !isset($data['com_fields']) || empty($data['com_fields'])) {
            return;
        }

        foreach ($data['com_fields'] as $fieldName => $field) {
            // If is array, it is possibly subform.
            if (is_array($field)) {
                $fieldObject = new SubFormField($context, $field, $fieldName);
                /** @var Indexer $indexer */
                $indexer = ObjectManager::getInstance()->getObject(Indexer::class);
                $indexer->deleteItemFieldValues($itemId);
                $indexer->index($itemId, $fieldObject);
            }
        }
    }
}