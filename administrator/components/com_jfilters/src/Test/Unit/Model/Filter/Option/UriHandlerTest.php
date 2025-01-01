<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface as FilterConfigInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\NestedInterface as NestedOptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\UriHandler;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\UriHandlerInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule\Collection as SortingRuleCollection;
use Joomla\CMS\Uri\Uri;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UriHandlerTest extends TestCase
{
    /**
     * @var FilterInterface|MockObject
     */
    protected $filterMock;

    /**
     * @var OptionInterface|MockObject
     */
    protected $optionMock;

    /**
     * @var Collection|MockObject
     */
    protected $filterCollectionMock;

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var UriHandler
     */
    protected $uriModel;

    /**
     * @param $filterName
     * @param $value
     *
     * @throws \Exception
     * @since        1.0.0
     * @covers       UriHandler::getBase()
     * @dataProvider dataProviderOptionWithFiltersCollection
     */
    public function testGetBaseRoute($optionVarName, $optionVarValue, $isRoot, $filtersArray)
    {
        $this->filterMock->expects($this->any())->method('getRoot')->willReturn($isRoot);

        // create the uri based on the supposed base route
        Uri::reset();
        $expectedUrl = Uri::getInstance('index.php?option=' . UriHandlerInterface::COMPONENT . '&view=' . UriHandlerInterface::VIEW);
        $filterObjects = [];

        // Create an array with the mocked filters based on the provider's filters
        foreach ($filtersArray as $filter) {
            $filterMock = $this->getMockBuilder(Filter::class)
                               ->setMethodsExcept(['setRequest', 'getRequest'])->disableOriginalConstructor()
                               ->getMock();
            $filterMock->expects($this->any())->method('getId')->willReturn($filter['id']);
            $filterMock->setRequest($filter['request']);
            $optionsCollectionMock = $this->createMock(Filter\Option\Collection\Filtered::class);
            $optionMocks = $this->createOptionMocks($filter['request']);
            $optionsCollectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator($optionMocks));
            $optionsCollectionMock->expects($this->any())->method('getByAttribute')->will($this->returnCallback(function (
                $argument
            ) {
                $optionMocks = $this->createOptionMocks([$argument]);

                return $optionMocks[md5($argument)];
            }));
            $filterMock->expects($this->any())->method('getRequestVarName')->willReturn($filter['name']);
            $filterMock->expects($this->any())->method('getIsMultiSelect')->willReturn($filter['isMultiSelect']);
            $filterMock->expects($this->any())->method('getOptions')->willReturn($optionsCollectionMock);
            $filterObjects [] = $filterMock;

            //root filters do not accept vars from other filters
            if (!$isRoot) {
                $request = $filter['request'];
                // single value for multi-select
                if ($filter['isMultiSelect'] === false) {
                    $request = reset($filter['request']);
                }
                $expectedUrl->setVar($filter['name'], $request);
            }
        }

        $this->filterCollectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator($filterObjects));

        $actualUri = $this->uriModel->getBase($this->optionMock);

        $this->assertEquals($expectedUrl->toString(), $actualUri->toString());
    }

    /**
     * @param   array   $optionValues
     * @param   string  $type
     *
     * @return array
     * @since 1.0.0
     */
    protected function createOptionMocks(array $optionValues, $type = OptionInterface::class)
    {
        $options = [];
        foreach ($optionValues as $optionValue) {
            $optionMock = $this->createMock($type);
            $optionMock->expects($this->any())->method('getValue')->willReturn($optionValue);
            $options [md5($optionValue)] = $optionMock;
        }

        return $options;
    }

    /**
     * @param $filterName
     * @param $value
     *
     * @throws \Exception
     * @since        1.0.0
     * @covers       UriHandler::get()
     * @dataProvider dataProviderOption
     */
    public function testGetMultiSelect($filterName, $value, $request)
    {
        $this->filterMock->expects($this->any())->method('getRequestVarName')->willReturn($filterName);
        $this->filterMock->expects($this->any())->method('getId')->willReturn(1);
        $this->filterMock->setRequest($request);
        $this->filterMock->expects($this->any())->method('getIsMultiSelect')->willReturn(true);
        $this->optionMock->expects($this->any())->method('getValue')->willReturn($value);
        $this->filterCollectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator([]));

        $optionsCollectionMock = $this->createMock(Filter\Option\Collection\Filtered::class);
        $optionMocks = $this->createOptionMocks($request);
        $optionsCollectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator($optionMocks));
        $optionsCollectionMock->expects($this->any())->method('getByAttribute')->will($this->returnCallback(function (
            $argument
        ) {
            $optionMocks = $this->createOptionMocks([$argument]);

            return $optionMocks[md5($argument)];
        }));
        $this->filterMock->expects($this->any())->method('getOptions')->willReturn($optionsCollectionMock);

        $actualUri = $this->uriModel->get($this->optionMock);
        $actualUrl = $actualUri->toString();
        if (is_array($request)) {
            if (!in_array($value, $request)) {
                $request [] = $value;
            } else {
                $position = array_search($value, $request);
                unset($request[$position]);
            }
            $request = array_map('urlencode', $request);
        } else {
            $request = urlencode($request);
        }
        Uri::reset();
        $expectedUrl = Uri::getInstance('index.php?option=' . UriHandlerInterface::COMPONENT . '&view=' . UriHandlerInterface::VIEW);
        $query = $expectedUrl->getQuery(true);
        $query[$filterName] = $request;
        $expectedUrl->setQuery($query);

        $this->assertEquals($expectedUrl->toString(), $actualUrl);
    }

    /**
     * Test the toggleVar param in the UriHandler::get()
     * $toggleVar defines if the multi-select links will have the toggle effect (add/remove the current var)
     *
     * @param $filterName
     * @param $value
     *
     * @throws \Exception
     * @since        1.0.0
     * @covers       UriHandler::get()
     * @dataProvider dataProviderOption
     */
    public function testGetMultiSelectWithoutToggleVar($filterName, $value, $request)
    {
        $this->filterMock->expects($this->any())->method('getRequestVarName')->willReturn($filterName);
        $this->filterMock->expects($this->any())->method('getId')->willReturn(1);
        $this->filterMock->setRequest($request);
        $this->filterMock->expects($this->any())->method('getIsMultiSelect')->willReturn(true);
        $this->optionMock->expects($this->any())->method('getValue')->willReturn($value);
        $this->filterCollectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator([]));

        $optionsCollectionMock = $this->createMock(Filter\Option\Collection\Filtered::class);
        $optionMocks = $this->createOptionMocks($request);
        $optionsCollectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator($optionMocks));
        $optionsCollectionMock->expects($this->any())->method('getByAttribute')->will($this->returnCallback(function (
            $argument
        ) {
            $optionMocks = $this->createOptionMocks([$argument]);

            return $optionMocks[md5($argument)];
        }));
        $this->filterMock->expects($this->any())->method('getOptions')->willReturn($optionsCollectionMock);

        $actualUri = $this->uriModel->get($this->optionMock, false);
        $actualUrl = $actualUri->toString();
        if (is_array($request)) {
            if (!in_array($value, $request)) {
                $request [] = $value;
            }
            $request = array_map('urlencode', $request);
        } else {
            $request = urlencode($request);
        }
        Uri::reset();
        $expectedUrl = Uri::getInstance('index.php?option=' . UriHandlerInterface::COMPONENT . '&view=' . UriHandlerInterface::VIEW);
        $query = $expectedUrl->getQuery(true);
        $query[$filterName] = $request;
        $expectedUrl->setQuery($query);

        $this->assertEquals($expectedUrl->toString(), $actualUrl);
    }


    /**
     * @param $filterName
     * @param $value
     * @param $parent
     * @param $request
     *
     * @throws \Exception
     * @since        1.0.0
     * @covers       UriHandler::get()
     * @dataProvider dataProviderOptionWithParent
     */
    public function testGetMultiSelectWithNestedOptions($filterName, $value, $parent, $request)
    {
        $optionMock = $this->createMock(NestedOptionInterface::class);
        $optionMock->expects($this->any())->method('getParentFilter')->willReturn($this->filterMock);
        $optionMock->expects($this->any())->method('isNested')->willReturn(true);
        $this->filterMock->expects($this->any())->method('getRequestVarName')->willReturn($filterName);
        $this->filterMock->setRequest($request);
        $this->filterMock->expects($this->any())->method('getIsMultiSelect')->willReturn(true);
        $optionMock->expects($this->any())->method('getValue')->willReturn($value);
        $this->filterCollectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator([]));

        $configMock = $this->createMock(FilterConfigInterface::class);
        $configValueMock = $this->createMock(ValueInterface::class);
        $configValueMock->expects($this->any())->method('getIsTree')->willReturn(true);
        $configMock->expects($this->any())->method('getValue')->willReturn($configValueMock);
        $this->filterMock->expects($this->any())->method('getConfig')->willReturn($configMock);
        $parentOption = $this->createMock(NestedOptionInterface::class);
        $parentOption->expects($this->any())->method('getValue')->willReturn($parent['value']);
        $optionMock->expects($this->any())->method('getParentOption')->willReturn($parentOption);

        $optionsCollectionMock = $this->createMock(Filter\Option\Collection::class);
        $optionMocks = $this->createOptionMocks($request);
        $optionsCollectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator($optionMocks));
        $optionsCollectionMock->expects($this->any())->method('getByAttribute')->will($this->returnCallback(function (
            $argument
        ) {
            $optionMocks = $this->createOptionMocks([$argument], NestedOptionInterface::class);

            return $optionMocks[md5($argument)];
        }));
        $this->filterMock->expects($this->any())->method('getOptions')->willReturn($optionsCollectionMock);


        $actualUri = $this->uriModel->get($optionMock);
        $actualUrl = $actualUri->toString();
        if (is_array($request)) {
            if (!in_array($value, $request)) {
                $request [] = $value;
            } else {
                $position = array_search($value, $request);
                unset($request[$position]);
            }
            // remove parent value from request
            if ($parent['value'] && in_array($parent['value'], $request)) {
                $parentPosition = array_search($parent['value'], $request);
                unset($request[$parentPosition]);
            }
            $request = array_map('urlencode', $request);
        } else {
            $request = urlencode($request);
        }
        Uri::reset();
        $expectedUrl = Uri::getInstance('index.php?option=' . UriHandlerInterface::COMPONENT . '&view=' . UriHandlerInterface::VIEW);
        $query = $expectedUrl->getQuery(true);
        $query[$filterName] = $request;
        $expectedUrl->setQuery($query);

        $this->assertEquals($expectedUrl->toString(), $actualUrl);
    }

    /**
     * @param   string  $optionVarName
     * @param   string  $optionVarValue
     * @param   bool    $isRoot
     * @param   array   $filtersArray
     *
     * @covers       UriHandler::get()
     * @dataProvider dataProviderOptionWithFiltersCollection
     *
     * @throws \Exception
     */
    public function testGetRoute($optionVarName, $optionVarValue, $isRoot, $filtersArray)
    {
        $this->filterMock->expects($this->any())->method('getRequestVarName')->willReturn($optionVarName);
        $this->filterMock->expects($this->any())->method('getRoot')->willReturn($isRoot);
        $this->optionMock->expects($this->any())->method('getValue')->willReturn($optionVarValue);

        // create the uri based on the supposed base route
        Uri::reset();
        $expectedUrl = Uri::getInstance('index.php?option=' . UriHandlerInterface::COMPONENT . '&view=' . UriHandlerInterface::VIEW);
        if (is_array($optionVarValue)) {
            $optionVarValue = array_map('urlencode', $optionVarValue);
        } else {
            $optionVarValue = urlencode($optionVarValue);
        }
        $query = $expectedUrl->getQuery(true);

        $filterObjects = [];

        // Create an array with the mocked filters based on the provider's filters
        foreach ($filtersArray as $filter) {
            $filterMock = $this->getMockBuilder(Filter::class)
                               ->setMethodsExcept(['setRequest', 'getRequest'])->disableOriginalConstructor()
                               ->getMock();
            $filterMock->expects($this->any())->method('getId')->willReturn($filter['id']);
            $filterMock->setRequest($filter['request']);
            $optionsCollectionMock = $this->createMock(Filter\Option\Collection\Filtered::class);
            $optionMocks = $this->createOptionMocks($filter['request']);
            $optionsCollectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator($optionMocks));
            $optionsCollectionMock->expects($this->any())->method('getByAttribute')->will($this->returnCallback(function (
                $argument
            ) {
                $optionMocks = $this->createOptionMocks([$argument]);

                return $optionMocks[md5($argument)];
            }));
            $filterMock->expects($this->any())->method('getRequestVarName')->willReturn($filter['name']);
            $filterMock->expects($this->any())->method('getIsMultiSelect')->willReturn($filter['isMultiSelect']);
            $filterMock->expects($this->any())->method('getOptions')->willReturn($optionsCollectionMock);
            $filterObjects [] = $filterMock;

            //root filters do not accept vars from other filters
            if (!$isRoot) {
                $request = $filter['request'];
                // single value for multi-select
                if ($filter['isMultiSelect'] === false) {
                    $request = reset($filter['request']);
                }
                $expectedUrl->setVar($filter['name'], $request);
            }
        }
        $expectedUrl->setVar($optionVarName, $optionVarValue);

        $this->filterCollectionMock->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator($filterObjects));

        $actualUri = $this->uriModel->get($this->optionMock);

        $this->assertEquals($expectedUrl->toString(), $actualUri->toString());
    }

    /**
     * @return array
     */
    public function dataProviderOption()
    {
        /*
         * Each of the array elements represents an option
         * The 1st attribute represents the filter name the 2nd the option's value
         */
        return [
            [
                'name' => 'filter1',
                'value' => '1',
                'request' => ['5', '10', '20']
            ],
            [
                'name' => 'filter2',
                'value' => '#abcd',
                'request' => ['cd']
            ],
            [
                'name' => 'filter3',
                'value' => 'sakis',
                'request' => ['sakis', 'lakis', 'john']
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderOptionWithParent()
    {
        /*
         * Each of the array elements represents an option
         * The 1st attribute (name) represents the filter name the 2nd (value) the option's value
         * The parent represents the parent option
         */
        return [
            // the parent exists in the request. It should be removed in the uri for the option.
            [
                'name' => 'filter1.1',
                'value' => '30',
                'parent' => [
                    'value' => '5',
                ],
                'request' => ['5', '10', '20'],
            ],
            [
                'name' => 'filter2.1',
                'value' => '30',
                'parent' => [
                    'value' => '1',
                ],
                'request' => ['10', '20'],
            ]
        ];
    }

    /**
     * Data provider that represents the Option and the existing filters with their selections/requests
     *
     * @return array
     */
    public function dataProviderOptionWithFiltersCollection()
    {
        /*
         * Each of the array elements represents an option, the 'filters' element represents the existing filters
         * The 3rd attribute represents the values/request of the filter
         */
        return [
            [
                'optionVarName' => 'myVar1',
                'optionVarValue' => 'myValue1',
                'isRootFilter' => false,
                'filters' =>
                    [
                        [
                            'id' => 1,
                            'name' => 'filter1',
                            'type' => 'INT',
                            'request' => ['1', '2', '3'],
                            'isMultiSelect' => true
                        ],
                        [
                            'id' => 2,
                            'name' => 'filter2',
                            'type' => 'INT',
                            'request' => ['3', '4'],
                            'isMultiSelect' => false
                        ],
                        [
                            'id' => 3,
                            'name' => 'filter3',
                            'type' => 'STRING',
                            'request' => ['sakis', 'lakis'],
                            'isMultiSelect' => true
                        ]
                    ]
            ],

            [
                'optionVarName' => 'vAr',
                'optionVarValue' => 'vAl&',
                'isRootFilter' => true,
                'filters' =>
                    [
                        [
                            'id' => 1,
                            'name' => 'filter1',
                            'type' => 'INT',
                            'request' => ['5', '100', '800'],
                            'isMultiSelect' => false
                        ],
                        [
                            'id' => 2,
                            'name' => 'filter2',
                            'type' => 'INT',
                            'request' => ['3', '4'],
                            'isMultiSelect' => true
                        ],
                        [
                            'id' => 3,
                            'name' => 'filter3',
                            'type' => 'STRING',
                            'request' => ['yea', 'no'],
                            'isMultiSelect' => false
                        ]
                    ]
            ],
        ];
    }

    /**
     * Setup basic vars
     */
    protected function setUp(): void
    {
        $this->filterMock = $this->getMockBuilder(Filter::class)
                                 ->setMethodsExcept(['setRequest', 'getRequest'])->disableOriginalConstructor()
                                 ->getMock();
        $this->optionMock = $this->createMock(OptionInterface::class);
        $this->optionMock->expects($this->any())->method('getParentFilter')->willReturn($this->filterMock);
        $this->filterCollectionMock = $this->createMock(Collection::class);
        $this->filterCollectionMock->expects($this->any())->method('getByAttribute')->willReturn($this->filterCollectionMock);
        $sortingRuleCollection = $this->createMock(SortingRuleCollection::class);
        $this->uri = new Uri;
        $this->uriModel = new UriHandler($this->filterCollectionMock, $sortingRuleCollection, $this->uri);
    }
}
