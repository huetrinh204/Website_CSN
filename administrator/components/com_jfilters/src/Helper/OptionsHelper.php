<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Helper;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Clear;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection as OptionCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered\Nested;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\CollectionFactory;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionFactory;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Bluecoder\Component\Jfilters\Site\Model\ResultsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\SiteMenu;

/**
 * Class OptionsHelper
 *
 * Operates on filter's options
 *
 */
class OptionsHelper
{
    /**
     * @var OptionsHelper
     */
    protected static $instance;

    /**
     * @since 1.0.0
     * @var ResultsModel
     */
    protected $componentModel;

    /**
     * @since 1.0.0
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * Cache the selected options per filter
     *
     * @var array
     */
    protected $selectedOptions = [];

    /**
     * @return OptionsHelper
     * @since 1.0.0
     */
    public static function getInstance(): OptionsHelper
    {
        if(self::$instance === null) {
            self::$instance = new OptionsHelper();
        }

        return self::$instance;
    }

    /**
     * Get the children Option OptionCollection, based on a parent Option value.
     *
     * @param OptionCollection $optionCollection
     * @param int $root_item_id
     * @return OptionCollection|null
     * @since 1.0.0
     */
    public function getChildren(OptionCollection $optionCollection, int $root_item_id)
    {
        if (empty($root_item_id)) {
            return $optionCollection;
        }
        // we use static to avoid useless recursions
        static $rootFound = false;
        $optionCollectionChildren = null;
        /** @var OptionInterface $rootOption */
        $rootOption = $optionCollection->getByAttribute('value', $root_item_id);

        // we found the root
        if ($rootOption != null && $rootOption->isNested()) {
            $rootFound = true;
            return $optionCollectionChildren = $rootOption->getChildren();
        }

        /** @var OptionInterface $option */
        foreach ($optionCollection as $option) {
            // recurse into the sub-trees
            if ($rootOption && $rootOption->isNested() && $option->getChildren() !== null) {
                $optionCollectionChildren = $this->getChildren($option->getChildren(), $root_item_id);
                if ($rootFound === true) {
                    break;
                }
            }
        }

        return $optionCollectionChildren;
    }

    /**
     * Creates the full tree from a Nested Options Collection
     *
     * @param   Nested|array  $options
     * @param int $from
     * @param string $strPadStart
     *
     * @return Nested
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getFullTree(&$options, int $from = 0, string $strPadStart = '-') : Nested
    {
        static $level = 0;

        if ($options instanceof Nested) {
            // Do not proceed if there are no options
            if($options->getSize() == 0) {
                return $options;
            }
            $options = array_values($options->getItems());
            // Always reset the level in the initial call. It may holds a value from previous calls.
            $level = 0;
        }

        for ($key = $from; $key < count($options); $key++) {
            /** @var OptionInterface $option */
            $option = $options[$key];
            if ($option->getParentOption() === null) {
                $level = 0;
            }
            if ($option->getChildren() !== null) {
                $level++;
                $childOptions = $option->getChildren()->getItems();

                //
                if($strPadStart) {
                    /** @var  OptionInterface[] $childOptions */
                    $childOptions = array_map(
                        function ($option) use ($level, $strPadStart) {
                            $option->setLabel(str_repeat($strPadStart.' ', (int)$level) . $option->getLabel());

                            return $option;
                        },
                        $childOptions
                    );
                }
                array_splice($options, $key + 1, 0, $childOptions);
                $key++;

                return $this->getFullTree($options, $key, $strPadStart);
            }
        }

        /** @var CollectionFactory $collectionFactory */
        $collectionFactory = ObjectManager::getInstance()->getObject(CollectionFactory::class);
        $collection = $collectionFactory->create(OptionCollection\Filtered\Nested::class);

        if (reset($options)) {
            $collection->setFilterItem($option->getParentFilter());

            // Set the language condition, so that it will not get cleared if it is set in another stage.
            $this->setOptionsLanguage($collection);
        }

        foreach ($options as $option) {
            $collection->addItem($option);
        }
        return $collection;
    }

    /**
     * Set language to the options
     *
     * @param OptionCollection $optionCollection
     * @return OptionsHelper
     * @throws \Exception
     * @since 1.0.0
     */
    public function setOptionsLanguage(OptionCollection $optionCollection): OptionsHelper
    {
        $language = Factory::getApplication()->getLanguage()->getTag();
        if (!empty($language) && Multilanguage::isEnabled()) {
            $optionCollection->addLanguageCondition([$language, '*']);
        }
        return $this;
    }

    /**
     * Set the search query as condition in the collection
     *
     * @param FilterInterface $filter
     * @param string $context
     * @return OptionsHelper
     * @throws \Exception
     * @since 1.0.0
     */
    public function setSearchQueryResults(FilterInterface $filter, string $context): OptionsHelper
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        // There is query or a Smart Search filter
        if ((in_array($input->getCmd('option'), ['com_finder', 'com_jfilters']) && $input->getString('q')) || $input->get('t')) {
            $options = $filter->getOptions();
            if (method_exists($options, 'setQueryCondition')) {
                $options->setQueryCondition($context);
            }
        }
        return $this;
    }

    /**
     * Set the itemId to the options' urls/links
     *
     * @param OptionCollection|null $optionCollection
     * @param int $itemId
     * @return OptionsHelper
     * @throws \Exception
     * @since 1.0.0
     */
    public function setOptionsItemId(?OptionCollection $optionCollection, int $itemId = 0): OptionsHelper
    {
        if (!$optionCollection || !$itemId) {
            return $this;
        }
        try {
            /** @var SiteMenu $menus */
            $menu = Factory::getApplication()->getMenu();
            if (!$menu || !$menu->getItem($itemId)) {
                return $this;
            }
        } catch (\Exception $exception) {
            // suck it. No menu.
        }

        array_map(function (OptionInterface $option) use ($itemId) {
            $option->getLink(false)->setVar('Itemid', (int)$itemId);
        }, $optionCollection->getItems());
        return $this;
    }

    /**
     * Returns a clear Option object
     *
     * @param FilterInterface $filter
     * @param int $itemId
     *
     * @return OptionInterface
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getClearOption(FilterInterface $filter, int $itemId = 0): OptionInterface
    {
        if ($this->optionFactory === null) {
            $this->optionFactory = ObjectManager::getInstance()->getObject(OptionFactory::class);
        }

        $option = $this->optionFactory->create([], Clear::class);

        $option->setParentFilter($filter);
        $option->setValue('');
        $option->setLabel('MOD_JFILTERS_FILTER_CLEAR');

        // Set the item id to the url
        if ($itemId) {
            $option->getLink()->setVar('Itemid', $itemId);
        }

        return $option;
    }

    /**
     * Returns an array with the selected options.
     *
     * @param   FilterInterface  $filter
     *
     * @return array
     * @throws \Exception
     * @since 1.0.0
     */
    public function getSelectedOptions(FilterInterface $filter) : array
    {
        if (!isset($this->selectedOptions[$filter->getId()])) {
            $this->selectedOptions[$filter->getId()] = [];
            if ($filter->getRequest()) {
                // We need the language strings of the filtering mod. Used as separators
                Factory::getApplication()->getLanguage()->load('mod_jfilters_filters');
                Factory::getApplication()->getLanguage()->load('mod_jfilters_selections');
                if ($filter->getDisplay() === 'calendar') {
                    $dates = $filter->getRequest();
                    $visualDateFormat = $filter->getAttributes()->get('date_format', 'd M, Y');
                    $datesVisible = [];
                    foreach ($dates as $date) {
                        try {
                            $datesVisible[] = HTMLHelper::date($date, $visualDateFormat);
                            $datesVisible = array_filter($datesVisible);
                        } catch (\Exception $exception) {
                            // Suck it. Invalid date
                        }
                    }
                    if ($filter->getIsRange() && $datesVisible) {
                        // Load the lang from the filtering module. We use it for the range separator string
                        Factory::getApplication()->getLanguage()->load('mod_jfilters_filters');
                    } else {
                        // Single date
                        $datesVisible = [reset($datesVisible)];
                    }

                    $rangeSeparator = Text::_('MOD_JFILTERS_FILTER_DATE_RANGE_SEPARATOR') ?: '-';
                    $rangeSeparator = ' ' . trim($rangeSeparator) . ' ';
                    $optionLabel = (implode($rangeSeparator, $datesVisible));
                } elseif ($filter->getIsRange()) {
                    $request = $filter->getRequest();
                    if (isset($request[0]) && !isset($request[1])) {
                        $optionLabel = Text::sprintf('MOD_JFILTERS_SELECTIONS_OVER_VALUE', $request[0]);
                    }elseif(!isset($request[0]) && isset($request[1])) {
                        $optionLabel = Text::sprintf('MOD_JFILTERS_SELECTIONS_UNDER_VALUE', $request[1]);
                    }else {
                        $optionLabel = Text::sprintf('MOD_JFILTERS_SELECTIONS_BETWEEN_VALUES', $request[0], $request[1]);
                    }

                } // Selectable filters
                else {
                    $options = $filter->getOptions()->getSelected();
                }

                // We have to create a new option using the prev. generated $optionLabel
                if (!isset($options) && !empty($optionLabel)) {
                    if ($this->optionFactory === null) {
                        $this->optionFactory = ObjectManager::getInstance()->getObject(OptionFactory::class);
                    }
                    $option = $this->optionFactory->create();
                    $option->setParentFilter($filter);
                    $option->setLabel($optionLabel);
                    $option->setIsLabelResolved(true);
                    $options = [$option];
                }

                $this->selectedOptions[$filter->getId()] = $options;
            }
        }
        return $this->selectedOptions[$filter->getId()];
    }
}
