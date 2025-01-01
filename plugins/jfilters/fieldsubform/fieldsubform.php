<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as ConfigFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface as FilterConfigInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator\Dynamic;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Class PlgJfiltersFieldsubform
 *
 * Plugin that handles the generation of filters from the subform field.
 */
class PlgJfiltersFieldsubform extends CMSPlugin
{
    /**
     * The filter config name on which the plugin to act.
     *
     * @var string
     */
    protected $configFilterName ='fields/subform';

    /**
     * Method called after the filters are generated but not saved.
     *
     * @param   FilterConfigInterface  $filterConfiguration
     * @param   array                  $generatedFilters
     *
     * @return bool
     * @since 1.0.0
     */
    public function onFiltersAfterGenerate(FilterConfigInterface $filterConfiguration, array &$generatedFilters)
    {
        if ($filterConfiguration->getName() != $this->configFilterName || empty($generatedFilters)) {
            return false;
        }

        $filters = [];
        foreach ($generatedFilters as $subform) {
            $filters = array_merge($filters, $this->getSubformFilters($subform->parent_id));
        }

        $generatedFilters = $this->changeConfigName($filters);

        return true;
    }

    /**
     * Get the filters from the fields used in the subform field.
     *
     * @param   int  $subformFieldId
     *
     * @return mixed
     * @since 1.0.0
     */
    protected function getSubformFilters(int $subformFieldId)
    {
        $filters = [];
        /** @var \Joomla\Database\DatabaseInterface $db */
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('fieldparams')->from('#__fields')->where('id= :id')->bind(':id', $subformFieldId);
        $db->setQuery($query);

        $fieldParams = new Registry($db->loadResult());
        $fields = (array)$fieldParams->get('options');
        $fieldIds = [];

        // Get the ids of the inner fields and generate filters for them.
        if ($fields) {
            foreach ($fields as $field) {
                $fieldIds [] = $field->customfield;
            }

            if ($fieldIds) {
                $filters = $this->generateFieldFilters($fieldIds);
            }
        }

        return $this->changeConfigName($filters);
    }

    /**
     * Generate the filters of the fields.
     *
     * @param $ids
     *
     * @return mixed
     * @throws ReflectionException
     * @since 1.0.0
     */
    protected function generateFieldFilters($ids)
    {
        $ids = ArrayHelper::toInteger($ids);
        $objectManager = ObjectManager::getInstance();

        /** @var ConfigFilterCollection $configFilterCollection */
        $configFilterCollection = $objectManager->getObject(ConfigFilterCollection::class);

        /** @var FilterConfigInterface $fieldsConfigFilter */
        $fieldsConfigFilter = $configFilterCollection->getByNameAttribute('fields');

        if ($fieldsConfigFilter) {
            $fieldsConfigFilter->getDefinition()->getType()->setExcluded('subform');
            $fieldsConfigFilter->getDefinition()->getId()->setIncluded(implode(',', $ids));
            $contextConfigCollection = $objectManager->getObject(Collection::class);
            $dynamicFiltersConfigCollection = $objectManager->getObject(DynamicFilterCollection::class);
            /** @var \Joomla\CMS\MVC\Model\AdminModel $resourceModel */
            $resourceModel = Factory::getApplication()->bootComponent('com_jfilters')->getMVCFactory()
                                       ->createModel('Filter', 'Administrator');
            /** @var Dynamic $dynamicGenerator */
            $dynamicGenerator = $objectManager->getObject(Dynamic::class,
                                                    [
                                                        $fieldsConfigFilter,
                                                        $resourceModel->getTable(),
                                                        $contextConfigCollection,
                                                        $dynamicFiltersConfigCollection
                                                    ]
            );

            // If there is a condition for "only_use_in_subform" remove it. We include also the "only_use_in_subform" fields.
            if($fieldsConfigFilter->getDefinition()->getCondition() && $fieldsConfigFilter->getDefinition()->getCondition()->getDbColumn() == 'only_use_in_subform') {
                $fieldsConfigFilter->getDefinition()->setCondition(null);
            }
            return $dynamicGenerator->generate();
        }
    }

    /**
     * Change the filters config name to match to be of type subform.
     *
     * @param $filters
     *
     * @return mixed
     * @since 1.0.0
     */
    protected function changeConfigName($filters)
    {
        foreach ($filters as $filter)
        {
            $filter->config_name = $this->configFilterName;
        }

        return $filters;
    }
}