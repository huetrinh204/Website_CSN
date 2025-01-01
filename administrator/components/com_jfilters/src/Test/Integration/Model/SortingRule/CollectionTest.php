<?php

namespace Bluecoder\Component\Jfilters\Administrator\Test\Integration\Model\SortingRule;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     * @since 1.16.0
     */
    protected Collection $collection;

    public function setUp(): void
    {
        $this->collection = new Collection();
    }

    public function testGetItemsNoSortingRulesFiltering()
    {
        $items = $this->collection->getItems();

        // When no sorting rule defined, it should return just 1 item and should be the default (either for search or for filtering)
        $this->assertCount(1, $items);
        /** @var SortingRule $defaultItem */
        $defaultItem = reset($items);
        $expectedLabel = Text::_('JGLOBAL_TITLE') . ' ' . Text::_('COM_JFILTERS_' . SortingRule::DEFAULT_SORTING_FILTERING_DIR . '_LABEL');
        $this->assertEquals($expectedLabel, $defaultItem->getLabel());
    }

    public function testGetItemsNoSortingRulesSearch()
    {
        $app = Factory::getApplication();
        $app->getInput()->set('q', 'something');
        $items = $this->collection->getItems();
        //Clear it for the following tests
        $app->getInput()->set('q', '');

        // When no sorting rule defined, it should return just 1 item and should be the default (either for search or for filtering)
        $this->assertCount(1, $items);
        /** @var SortingRule $defaultItem */
        $defaultItem = reset($items);
        $expectedLabel = Text::_('COM_JFILTERS_SORT_FIELD_RELEVANCE') . ' ' . Text::_('COM_JFILTERS_' . SortingRule::DEFAULT_SORTING_SEARCH_DIR . '_LABEL');
        $this->assertEquals($expectedLabel, $defaultItem->getLabel());
    }

    public function testGetItemsSortingRulesIsSearchIsFilter()
    {
        $sortingRuleParams = $this->sortingRulesDataProvider();
        $this->collection->setSortingRuleParams($sortingRuleParams);
        $sortingRules = $this->collection->getItems();

        // No search. I.e. Filtering
        $this->assertCount(2, $sortingRules);
        $this->assertEquals('start_date', $sortingRules[1]->getSortField()->getFieldName());
        $this->assertEquals('DESC', $sortingRules[1]->getSortDirection());

        // Search
        $app = Factory::getApplication();
        $app->getInput()->set('q', 'something');
        $this->collection->setSortingRuleParams($sortingRuleParams);
        $sortingRules = $this->collection->getItems();
        $currentSortingRule = $this->collection->getCurrent();
        $this->assertCount(2, $sortingRules);
        $this->assertEquals(SortingRule::DEFAULT_SORTING_SEARCH_FIELD, $currentSortingRule->getSortField()->getFieldName());
        $this->assertEquals(SortingRule::DEFAULT_SORTING_SEARCH_DIR, $currentSortingRule->getSortDirection());
        // Reset
        $app->getInput()->set('q', '');
    }

    public function testGetItemsConditionContainNotMet()
    {
        $sortingRuleParams = $this->sortingRulesDataProvider();
        // We have 1 valid condition (empty) and 1 invalid (contain)
        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects($this->any())->method('getRequest')->willReturn([]);
        $filter1->expects($this->any())->method('getAlias')->willReturn('filter1');
        // Create the filter collection. This will be called by the ObjectManager inside the SortingRule\Condition
        /** @var FilterCollection $filterCollection */
        $filterCollection = \Bluecoder\Component\Jfilters\Administrator\ObjectManager::getInstance()->getObject(FilterCollection::class);
        $filterCollection->addItem($filter1);
        // The condition in the 2nd rule is invalid and the rule should NOT be returned
        $sortingRuleParams[2]->conditionFilters = 'filter1[]=a&filter1[]=b&filter2[]=a';
        $this->collection->setSortingRuleParams($sortingRuleParams);
        $sortingRules = $this->collection->getItems();
        $this->assertCount(1, $sortingRules);
        $this->assertEquals('title', $sortingRules[0]->getSortField()->getFieldName());
        $this->assertEquals(SortingRule::DEFAULT_SORTING_FILTERING_DIR, $sortingRules[0]->getSortDirection());
    }

    public function testGetItemsConditionContainMet()
    {
        $sortingRuleParams = $this->sortingRulesDataProvider();
        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects($this->any())->method('getRequest')->willReturn(['a', 'b']);
        $filter1->expects($this->any())->method('getAlias')->willReturn('filter1');
        // Create the filter collection. This will be called by the ObjectManager inside the SortingRule\Condition
        /** @var FilterCollection $filterCollection */
        $filterCollection = \Bluecoder\Component\Jfilters\Administrator\ObjectManager::getInstance()->getObject(FilterCollection::class);
        $filterCollection->addItem($filter1);
        // The condition in the 2nd rule is invalid and the rule should NOT be returned
        $sortingRuleParams[2]->conditionFilters = 'filter1[]=a&filter1[]=b&filter2[]=a';
        $this->collection->setSortingRuleParams($sortingRuleParams);
        $sortingRules = $this->collection->getItems();
        $this->assertCount(2, $sortingRules);
        $this->assertEquals('start_date', $sortingRules[1]->getSortField()->getFieldName());
        $this->assertEquals('DESC', $sortingRules[1]->getSortDirection());
    }


    public function testGetItemsConditionNotContainNotMet()
    {
        $sortingRuleParams = $this->sortingRulesDataProvider();
        $sortingRules = $this->collection->getItems();
        // We have 1 valid condition (empty) and 1 invalid (contain)
        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects($this->any())->method('getRequest')->willReturn(['a']);
        $filter1->expects($this->any())->method('getAlias')->willReturn('filter1');
        // Create the filter collection. This will be called by the ObjectManager inside the SortingRule\Condition
        /** @var FilterCollection $filterCollection */
        $filterCollection = \Bluecoder\Component\Jfilters\Administrator\ObjectManager::getInstance()->getObject(FilterCollection::class);
        $filterCollection->addItem($filter1);
        // The condition in the 2nd rule is invalid and the rule should NOT be returned
        $sortingRuleParams[2]->conditionOperator = SortingRule\Condition::OPERATOR_NOT_CONTAIN;
        $sortingRuleParams[2]->conditionFilters = 'filter1[]=a&filter1[]=b&filter2[]=a';
        $this->collection->setSortingRuleParams($sortingRuleParams);
        $sortingRules = $this->collection->getItems();
        $this->assertCount(1, $sortingRules);
        $this->assertEquals('title', $sortingRules[0]->getSortField()->getFieldName());
        $this->assertEquals(SortingRule::DEFAULT_SORTING_FILTERING_DIR, $sortingRules[0]->getSortDirection());
    }

    public function testGetItemsConditionNotContainMet()
    {
        $sortingRuleParams = $this->sortingRulesDataProvider();
        // We have 1 valid condition (empty) and 1 invalid (contain)
        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects($this->any())->method('getRequest')->willReturn(['c','d']);
        $filter1->expects($this->any())->method('getAlias')->willReturn('filter1');
        // Create the filter collection. This will be called by the ObjectManager inside the SortingRule\Condition
        /** @var FilterCollection $filterCollection */
        $filterCollection = \Bluecoder\Component\Jfilters\Administrator\ObjectManager::getInstance()->getObject(FilterCollection::class);
        $filterCollection->addItem($filter1);
        // The condition in the 2nd rule is invalid and the rule should NOT be returned
        $sortingRuleParams[2]->conditionOperator = SortingRule\Condition::OPERATOR_NOT_CONTAIN;
        $sortingRuleParams[2]->conditionFilters = 'filter1[]=a&filter1[]=b&filter2[]=a';
        $this->collection->setSortingRuleParams($sortingRuleParams);
        $sortingRules = $this->collection->getItems();
        $this->assertCount(2, $sortingRules);
        $this->assertEquals('title', $sortingRules[0]->getSortField()->getFieldName());
        $this->assertEquals(SortingRule::DEFAULT_SORTING_FILTERING_DIR, $sortingRules[0]->getSortDirection());
        $this->assertEquals('start_date', $sortingRules[1]->getSortField()->getFieldName());
        $this->assertEquals('DESC', $sortingRules[1]->getSortDirection());
    }

    public function testGetItemsSetInvalidRelevanceForFiltering()
    {
        $sortingRuleParams = $this->sortingRulesDataProvider();
        // We have 1 valid condition (empty) and 1 invalid (contain)
        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects($this->any())->method('getRequest')->willReturn(['c','d']);
        $filter1->expects($this->any())->method('getAlias')->willReturn('filter1');
        // Create the filter collection. This will be called by the ObjectManager inside the SortingRule\Condition
        /** @var FilterCollection $filterCollection */
        $filterCollection = \Bluecoder\Component\Jfilters\Administrator\ObjectManager::getInstance()->getObject(FilterCollection::class);
        $filterCollection->addItem($filter1);
        // We cannot use 'relevance' for filtering. That rule should not be returned in the collection.
        $sortingRuleParams[1]->useOnFiltering = true;
        $this->collection->setSortingRuleParams($sortingRuleParams);
        $sortingRules = $this->collection->getItems();
        $this->assertCount(2, $sortingRules);
    }

    public function sortingRulesDataProvider() : array
    {
        $sortingRule1 = new \stdClass();
        $sortingRule1->sortField = SortingRule::DEFAULT_SORTING_FILTERING_FIELD;
        $sortingRule1->sortDirection = SortingRule::DEFAULT_SORTING_FILTERING_DIR;
        $sortingRule1->useOnSearch = false;
        $sortingRule1->useOnFiltering = true;
        $sortingRule1->conditionOperator = SortingRule\Condition::OPERATOR_CONTAIN;
        $sortingRule1->conditionFilters = '';

        $sortingRule2 = new \stdClass();
        $sortingRule2->sortField = SortingRule::DEFAULT_SORTING_SEARCH_FIELD;
        $sortingRule2->sortDirection = SortingRule::DEFAULT_SORTING_SEARCH_DIR;
        $sortingRule2->useOnSearch = true;
        $sortingRule2->useOnFiltering = false;
        $sortingRule2->conditionOperator = SortingRule\Condition::OPERATOR_CONTAIN;
        $sortingRule2->conditionFilters = '';

        $sortingRule3 = new \stdClass();
        $sortingRule3->sortField = 'l.start_date';
        $sortingRule3->sortDirection = 'DESC';
        $sortingRule3->useOnSearch = true;
        $sortingRule3->useOnFiltering = true;
        $sortingRule3->conditionOperator = SortingRule\Condition::OPERATOR_CONTAIN;
        $sortingRule3->conditionFilters = '';

        return [$sortingRule1, $sortingRule2, $sortingRule3];
    }
}