<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

/**
 * Class FiltersField- used for displaying the published filters
 *
 */
class FiltersField extends JfiltersgroupedlistField
{
    /**
     * Method to get the field options.
     *
     * @return array
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getGroups()
    {
        // Get the filters
        $objectManager = ObjectManager::getInstance();
        $filterCollectionOrig = $objectManager->getObject(FilterCollection::class);
        /** @var  FilterCollection $filterCollection */
        $filterCollection = clone $filterCollectionOrig;
        $filterCollection->clear();
        $filterCollection->addCondition('filter.state', [1, 2]);

        // We only want the root filters as primary filters in the menu param.
        if($this->fieldname == 'primary_filtr') {
            $filterCollection->addCondition('filter.root', 1);
        }

        $currentFilterId = 0;
        $input = Factory::getApplication()->getInput();

        // We are in the filter edit page.
        if ($input->getCmd('option') == 'com_jfilters' && $input->getCmd('view') == 'filter' &&  $input->getInt('id')) {
            $currentFilterId = $input->getInt('id');
        }

        $options = [];
        /** @var \Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface $filter */
        foreach ($filterCollection as $filter) {
            // Exclude the current filter, if exists
            if ($filter->getId() == $currentFilterId || ($this->__get('data-includeTreeFilters') && $filter->getConfig()->getValue()->getIsTree())) {
                continue;
            }
            $additionalText = '';

            // If there is more than 1 filters with the same name, add the language and id.
            $filtersWithTheSameName = $filterCollection->getByAttribute('name', $filter->getName(), false);
            if (count($filtersWithTheSameName) > 1) {
                $languageString = '';
                if (Multilanguage::isEnabled()) {
                    $language = $filter->getLanguage() == '*' ? Text::_('JALL') : $filter->getLanguage();
                    $languageString = utf8_strtolower(Text::_('JFIELD_LANGUAGE_LABEL')) . ':' . $language . ', ';
                }
                $id = utf8_strtolower(Text::_('JGLOBAL_FIELD_ID_LABEL')) . ':' . $filter->getId();
                $additionalText = ' (' . $languageString . $id . ')';
            }

            $optionTmp = new \stdClass();
            $optionTmp->value = $filter->getId();
            $optionTmp->text = $filter->getLabel() . $additionalText;

            if ($edition = $this->__get('data-dynamicallyAddedOptionsEdition')) {
                $optionTmp->edition = $edition;
            }
            $options[] = $optionTmp;
        }

        $groups = parent::getGroups();
        $groupName = 0;
        array_walk($groups, function ($value, $key) use (&$groupName) {
            if (!empty($key) && !is_int($key)) {
                $groupName = Text::_('COM_JFILTERS_FILTERS_LABEL');
                return;
            } else {
                $groupName = (int) $key + 1;
            }
        });

        if (empty($groupName) || is_int($groupName)) {
            $groups = array_values($groups);
        }
        $groups[$groupName] = $options;
        $this->adjustProOptions($groups);

        return $groups;
    }
}
