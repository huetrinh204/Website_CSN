<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Section;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\SectionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;

/**
 * Class Definition
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Section
 */
class Definition extends Section implements SectionInterface, DefinitionInterface
{
    /**
     * @var Field\Id
     */
    protected $id;

    /**
     * @var Field
     */
    protected $title;

    /**
     * @var Field
     */
    protected $context;

    /**
     * @var Field
     */
    protected $condition;

    /**
     * @var Field
     */
    protected $language;

    /**
     * @var Field
     */
    protected $params;

    /**
     * @var Field\Type|null
     */
    protected ?Field\Type $type;

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        $class = parent::getClass();
        if (!empty($class) && !is_subclass_of($class, FilterInterface::class)) {
            throw new \UnexpectedValueException(sprintf('The class attribute: \'%s\' ,of the Definition config section, does not implement the \'FilterInterface\'', $class));
        }
        return $class;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setId(Field\Id $id): DefinitionInterface
    {
        $this->id = $id;
        $this->fields[] = $this->id;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle(Field $title): DefinitionInterface
    {
        $this->title = $title;
        $this->fields[] = $this->title;
        return $this;
    }

    public function setType(Field\Type $type): DefinitionInterface
    {
        $this->type = $type;
        $this->fields[] = $this->type;
        return $this;
    }

    /**
     * @return null|Field\Type
     * @since 1.0.0
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     */
    public function setContext(Field $context): DefinitionInterface
    {
        $this->context = $context;
        $this->fields[] = $this->context;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCondition(?Field $condition): DefinitionInterface
    {
        $this->condition = $condition;
        if($this->condition) {
            $this->fields[] = $this->condition;
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * {@inheritDoc}
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * {@inheritDoc}
     */
    public function setLanguage(Field $language): DefinitionInterface
    {
        $this->language = $language;
        $this->fields[] = $this->language;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setParams(Field $params): DefinitionInterface
    {
        $this->params = $params;
        $this->fields[] = $this->params;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParams()
    {
        return $this->params;
    }
}
