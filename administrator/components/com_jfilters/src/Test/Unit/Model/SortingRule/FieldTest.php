<?php

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\SortingRule;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule\Field;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    /**
     * @var Field|null
     * @since 1.16.0
     */
    protected ?Field $field = null;

    /**
     * @var FilterCollection|(FilterCollection&object&\PHPUnit\Framework\MockObject\MockObject)|(FilterCollection&\PHPUnit\Framework\MockObject\MockObject)|(object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     * @since 1.16.0
     */
    protected $filterCollection;

    protected function setUp(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        /** @var  ComponentConfig $componentConfig */
        $componentConfig = ObjectManager::getInstance()->getObject(ComponentConfig::class);
        $contextsXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Unit/config/contextXmls/contexts.xml');
        $configReader = new ContextsXMLConfigReader($componentConfig, $contextsXML);
        $contextCollection = new ContextConfigCollection($configReader, $loggerMock);
        $this->filterCollection = $this->createMock(FilterCollection::class);
        $this->field = new Field($contextCollection, $this->filterCollection);
    }

    /**
     * Filters are just integer numbers (indicating the filter id).
     * Fields are strings.
     *
     * @return void
     * @since 1.16.0
     */
    public function testGetIsFilter()
    {
        $this->field->setField('15');
        $this->assertTrue($this->field->getIsFilter());

        $this->field->setField('someField');
        $this->assertFalse($this->field->getIsFilter());
    }

    public function testGetFieldNameWithFilter()
    {
        $this->field->setField('10');
        /* If we have a filter, it should just return the filter id. I.e. The exact field as passed */
        $this->assertEquals('10', $this->field->getFieldName());
    }

    /**
     * Should return the respective db column as declared in our 'contexts.xml' see: setup()
     *
     * @return void
     * @throws \Exception
     * @since 1.16.0
     */
    public function testGetFieldNameWithContextField()
    {
        $this->field->setField('{context}.ordering');
        $this->assertEquals('ordering', $this->field->getFieldName());
        $this->field->setField('{context}.modified_date');
        $this->assertEquals('modified', $this->field->getFieldName());
        $this->field->setField('{context}.publish_start_date');
        $this->assertEquals('publish_up', $this->field->getFieldName());
    }

    /**
     * Do note: The strings will be non-translated in the ConsoleApplication
     * The translation strings can be found in: \Bluecoder\Component\Jfilters\Administrator\Model\SortingRule\Field::$labels
     * @return void
     * @throws \Exception
     * @since 1.16.0
     */
    public function testGetLabelForField()
    {
        $this->field->setField('relevance');
        $this->assertEquals('COM_JFILTERS_SORT_FIELD_RELEVANCE', $this->field->getLabel());

        $this->field->setField('l.title');
        $this->assertEquals('JGLOBAL_TITLE', $this->field->getLabel());

        $this->field->setField('l.start_date');
        $this->assertEquals('JGLOBAL_FIELD_CREATED_LABEL', $this->field->getLabel());

        $this->field->setField('{context}.modified_date');
        $this->assertEquals('JGLOBAL_FIELD_MODIFIED_LABEL', $this->field->getLabel());
    }

    /**
     * When we are dealing with filters the label will be fetched from the filter's label.
     *
     * @return void
     * @throws \Exception
     * @since 1.16.0
     */
    public function testGetLabelForFilter()
    {
        $filterId = 10;
        $filterLabel = "My Label";
        $filter = $this->createMock(Filter::class);
        $filter->expects($this->any())->method('getLabel')->willReturn($filterLabel);
        $this->filterCollection->expects($this->any())->method('getByAttribute')->with('id', $filterId)->willReturn($filter);
        $this->field->setField($filterId);
        $this->assertEquals($filterLabel, $this->field->getLabel());
    }

    /**
     * Should return the db table of the context, as defined in the `contexts.xml`
     *
     * @return void
     * @throws \Exception
     * @since 1.16.0
     */
    public function testGetDbTableNameForFieldWithContext()
    {
        $this->field->setField('{context}.ordering');
        /*
         * Since we have no menu item in the ConsoleApplication, the context will fall back to the default ("com_content.article" )
         */
        $this->assertEquals('#__content', $this->field->getDbTableName());
    }

    public function testGetDbTableNameForFieldWithTableAlias()
    {
        $this->field->setField('l.title');
        $this->assertEquals('l', $this->field->getDbTableName());
    }

    public function testGetDbTableNameForFilter()
    {
        // When it is int number it is a filter
        $this->field->setField('20');
        $this->assertEquals('', $this->field->getDbTableName());
    }
}