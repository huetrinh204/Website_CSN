<?php

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\SortingRule;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Filter;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as filterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule\Condition;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use PHPUnit\Framework\TestCase;

class ConditionTest extends TestCase
{
    /**
     * @var Input|null
     * @since 1.16.0
     */
    protected ?Input $input = null;

    /**
     * @var Filter|(Filter&object&\PHPUnit\Framework\MockObject\MockObject)|(Filter&\PHPUnit\Framework\MockObject\MockObject)|(object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     * @since 1.16.0
     */
    protected $filter;

    /**
     * @var Condition|null
     * @since 1.16.0
     */
    protected ?Condition $condition = null;

    protected function setUp(): void
    {
        $this->input = Factory::getApplication()->getInput();
        $this->filter = $this->createMock(Filter::class);
        $filterCollection = $this->createMock(filterCollection::class);
        $filterCollection->expects($this->any())->method('getByAttribute')->willReturn($this->filter);
        $this->condition = new Condition($filterCollection);
    }

    public function testIsValidContainWithCorrectFilterValue()
    {
        // The set filters in the condition exist (true)
        $this->condition->setConditionOperator(Condition::OPERATOR_CONTAIN);
        $this->condition->setConditionFilters('myFilter=blabla');
        $this->filter->expects($this->any())->method('getRequest')->willReturn(['blabla']);
        $this->assertTrue($this->condition->isValid());
    }

    public function testIsValidContainWithInCorrectFilterValue()
    {
        // The set filters in the condition do not exist (false)
        $this->condition->setConditionOperator(Condition::OPERATOR_CONTAIN);
        $this->condition->setConditionFilters('myFilter=blabla');
        $this->filter->expects($this->any())->method('getRequest')->willReturn(['sakis']);
        $this->assertFalse($this->condition->isValid());
    }

    public function testIsValidNotContainWithCorrectFilterValue()
    {
        // The set filters in the condition exist (true)
        $this->condition->setConditionOperator(Condition::OPERATOR_NOT_CONTAIN);
        $this->condition->setConditionFilters('myFilter=blabla');
        $this->filter->expects($this->any())->method('getRequest')->willReturn(['blabla']);
        $this->assertFalse($this->condition->isValid());
    }

    public function testIsValidNotContainWithInDifferentFilterValue()
    {
        // The set filters in the condition do not exist (false)
        $this->condition->setConditionOperator(Condition::OPERATOR_NOT_CONTAIN);
        $this->condition->setConditionFilters('myFilter=blabla');
        $this->filter->expects($this->any())->method('getRequest')->willReturn(['sakis']);
        $this->assertTrue($this->condition->isValid());
    }
}