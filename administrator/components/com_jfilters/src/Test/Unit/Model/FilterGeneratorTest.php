<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextColfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\Definition;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator\Declarative;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator\Dynamic;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\TableInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers FilterGenerator
 */
class FilterGeneratorTest extends TestCase
{
    /**
     * @var FilterGenerator
     */
    protected $model;

    protected $fieldMockId;

    protected $fieldMockTitle;

    protected $fieldMockContext;

    protected $configDefinitionMock;

    protected $dynamicGeneratorMock;

    protected $declarativeGeneratorMock;

    protected $contextConfigCollectionMock;

    protected $dynamicFilterCollectionMock;

    /**
     * @var Filter|(Filter&object&\PHPUnit\Framework\MockObject\MockObject)|(Filter&\PHPUnit\Framework\MockObject\MockObject)|(object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filterConfigMock;

    /**
     * Setup entry function
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fieldMockId      = $this->createMock(Field::class);
        $this->fieldMockTitle   = $this->createMock(Field::class);
        $this->fieldMockContext = $this->createMock(Field::class);

        $this->configDefinitionMock = $this->createMock(Definition::class);
        $this->configDefinitionMock->expects($this->any())->method('getId')->willReturn($this->fieldMockId);
        $this->configDefinitionMock->expects($this->any())->method('getTitle')->willReturn($this->fieldMockTitle);
        $this->configDefinitionMock->expects($this->any())->method('getContext')->willReturn($this->fieldMockContext);

        $this->dynamicGeneratorMock        = $this->createMock(Dynamic::class);
        $this->declarativeGeneratorMock    = $this->createMock(Declarative::class);
        $this->contextConfigCollectionMock = $this->createMock(ContextColfigCollection::class);
        $this->dynamicFilterCollectionMock = $this->createMock(DynamicFilterCollection::class);
        $this->dynamicFilterCollectionMock->expects($this->any())->method('getByAttribute')->willReturn([]);

        $this->filterConfigMock = $this->createMock(Filter::class);
        $this->filterConfigMock->expects($this->any())->method('getDefinition')->willReturn($this->configDefinitionMock);
        $this->filterConfigMock->expects($this->any())->method('getName')->willReturn('something');

        $tableMock = $this->createMock(TableInterface::class);
        $tableMock->expects($this->any())->method('load')->willReturn(false);
        $filterConfigCollectionMock = $this->createMock(Collection::class);
        $filterConfigCollectionMock->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([$this->filterConfigMock]));
        $objectManagerMock = $this->createMock(ObjectManager::class);

        $objectManagerMock->expects($this->any())->method('createObject')->with($this->logicalOr(
            Dynamic::class, Declarative::class))->will($this->returnCallback(array($this, 'getObjectManagerObject')));

        $objectManagerMock->expects($this->any())->method('getObject')->with($this->logicalOr(
            Dynamic::class, Declarative::class,
            ContextColfigCollection::class, DynamicFilterCollection::class))->will($this->returnCallback(array(
            $this,
            'getObjectManagerObject'
        )));

        $resourceModelMock = $this->createMock(AdminModel::class);
        $resourceModelMock->expects($this->any())->method('getTable')->willReturn($tableMock);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $this->model = new FilterGenerator($objectManagerMock, $filterConfigCollectionMock, $resourceModelMock,
            $loggerMock);
    }

    /**
     * Callback used by the ObjectManager::createObject();
     *
     * @param $key
     *
     * @return mixed
     */
    public function getObjectManagerObject($key)
    {
        if ($key == Dynamic::class) {
            $mock = $this->dynamicGeneratorMock;
        } else {
            if ($key == Declarative::class) {
                $mock = $this->declarativeGeneratorMock;
            } elseif ($key == ContextColfigCollection::class) {
                $mock = $this->contextConfigCollectionMock;
            }
            elseif ($key == DynamicFilterCollection::class) {
                $mock = $this->dynamicFilterCollectionMock;
            }
        }

        return $mock;
    }

    /**
     * @covers       FilterGenerator::generate
     *
     * @dataProvider dataProvider
     */
    public function testGenerateCount($isDynamic, $returnCount, $filterConfig)
    {
        //Dynamic filter
        if ($isDynamic) {
            $returned = [];
            for ($i = 0; $i < $returnCount; $i++) {
                $obj = new \stdClass();
                foreach ($filterConfig as $key => $value) {
                    $obj->$key = $value;
                }
                $returned[] = $obj;
            }
            $this->dynamicGeneratorMock->expects($this->any())->method('generate')->willReturn($returned);
        } //Declerative/Static filter
        else {
            $filter            = new \stdClass();
            $filter->parent_id = $filterConfig['parent_id'];
            $filter->name      = $filterConfig['name'];
            $filter->context   = $filterConfig['context'];
            $returned          = [$filter];

            $this->fieldMockId->expects($this->any())->method('getValue')->willReturn((string)$filterConfig['parent_id']);
            $this->fieldMockTitle->expects($this->any())->method('getValue')->willReturn((string)$filterConfig['name']);
            $this->fieldMockContext->expects($this->any())->method('getValue')->willReturn((string)$filterConfig['context']);
            $this->declarativeGeneratorMock->expects($this->any())->method('generate')->willReturn($returned);
        }
        $this->filterConfigMock->expects($this->once())->method('isDynamic')->willReturn($isDynamic);
        $filters = $this->model->generate();
        $this->assertCount($returnCount, $filters);
    }

    /**
     * @covers       FilterGenerator::generate
     *
     * @dataProvider dataProvider
     */
    public function testGenerateValues($isDynamic, $returnCount, $filterConfig)
    {
        // Emulation of dynamic filter
        if ($isDynamic) {
            $returned = [];
            for ($i = 0; $i < $returnCount; $i++) {
                $obj = new \stdClass();
                foreach ($filterConfig as $key => $value) {
                    $obj->$key = $value;
                }
                $returned[] = $obj;
            }
            $this->dynamicGeneratorMock->expects($this->any())->method('generate')->willReturn($returned);
        } // Emulation of declarative filter
        else {
            $filter              = new \stdClass();
            $filter->parent_id   = $filterConfig['parent_id'];
            $filter->name        = $filterConfig['name'];
            $filter->context     = $filterConfig['context'];
            $filter->config_name = $filterConfig['config_name'];
            $returned            = [$filter];

            $this->fieldMockId->expects($this->any())->method('getValue')->willReturn((string)$filterConfig['parent_id']);
            $this->fieldMockTitle->expects($this->any())->method('getValue')->willReturn((string)$filterConfig['name']);
            $this->fieldMockContext->expects($this->any())->method('getValue')->willReturn((string)$filterConfig['context']);
            $this->declarativeGeneratorMock->expects($this->any())->method('generate')->willReturn($returned);
        }
        $this->filterConfigMock->expects($this->once())->method('isDynamic')->willReturn($isDynamic);
        $filters = $this->model->generate();
        $this->assertEquals($filters[0]->parent_id, $filterConfig['parent_id']);
        $this->assertEquals($filters[0]->name, $filterConfig['name']);
        $this->assertEquals($filters[0]->context, $filterConfig['context']);
        $this->assertEquals($filters[0]->config_name, $filterConfig['config_name']);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [
                'dynamic' => false,
                'returns' => 1,
                [
                    'parent_id'   => 1,
                    'name'        => 'someName',
                    'context'     => 'staticContext',
                    'config_name' => 'something',
                    'language'    => '*'
                ]
            ],

            [
                'dynamic' => false,
                'returns' => 1,
                [
                    'parent_id'   => 1,
                    'name'        => 'someName',
                    'context'     => 'staticContext',
                    'config_name' => 'something',
                    'language'    => 'en-GB'
                ]
            ],

            [
                'dynamic' => false,
                'returns' => 1,
                ['parent_id' => 2, 'name' => 'someName2', 'context' => 'staticContext2', 'config_name' => 'something']
            ],
            [
                'dynamic' => true,
                'returns' => 2,
                ['parent_id' => 2, 'name' => 'someName3', 'context' => 'dynamicContext', 'config_name' => 'something']
            ],
        ];
    }
}
