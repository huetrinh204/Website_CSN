<?php

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use PHPUnit\Framework\TestCase;

/**
 * @since 1.16.0
 * @covers \Bluecoder\Component\Jfilters\Administrator\Model\SortingRule
 */
class SortingRuleTest extends TestCase
{
    /**
     * @var Input|null
     * @since 1.16.0
     */
    protected ?Input $input = null;

    /**
     * @var SortingRule|null
     * @since 1.16.0
     */
    protected ?sortingRule $sortingRule =  null;
    protected function setUp(): void
    {
        $this->input = Factory::getApplication()->getInput();
        $this->sortingRule = new SortingRule();
    }

    /**
     * Test if it's active by setting the same sort field and the same input (expecting true)
     * Omitting the sorting direction, defaults to 'ASC'
     *
     * @return void
     * @throws \Exception
     * @since 1.16.0
     */
    public function testIsActiveWithSameInputField()
    {
        $this->sortingRule->setSortField('l.start_date');
        $this->sortingRule->setSortDirection('asc');
        $this->input->set('o', 'start_date');
        // Skip the declaration of order, since "ASC" is the default one.
        $this->assertTrue($this->sortingRule->isActive());
    }

    /**
     * Test if it's active by setting the same sort field and the same input.
     * Set the sorting direction of the SR to 'DESC', but do not set it in the input
     *
     * @return void
     * @throws \Exception
     * @since 1.16.0
     */
    public function testIsActiveWithSameInputFieldDesc()
    {
        $this->sortingRule->setSortField('l.start_date');
        $this->sortingRule->setSortDirection('desc');
        $this->input->set('o', 'start_date');
        // Skip the declaration of order, since "ASC" is the default one.
        $this->assertFalse($this->sortingRule->isActive());
    }

    /**
     * Test if it's active by setting different sort field and the input (expecting false)
     *
     * @return void
     * @throws \Exception
     * @since 1.16.0
     */
    public function testIsActiveOtherInputField()
    {
        $this->sortingRule->setSortField('l.start_date');
        $this->sortingRule->setSortDirection('asc');
        $this->input->set('o', 'rating');
        // Skip the declaration of order, since "ASC" is the default one.
        $this->assertFalse($this->sortingRule->isActive());
    }

    public function testGetLabel()
    {
        $this->sortingRule->setSortField('l.start_date');
        $this->sortingRule->setSortDirection('asc');
        $label = $this->sortingRule->getLabel();
        $expectedLabel = 'Created Date COM_JFILTERS_ASC_LABEL';
        $this->assertEquals($expectedLabel, $label);
    }

}