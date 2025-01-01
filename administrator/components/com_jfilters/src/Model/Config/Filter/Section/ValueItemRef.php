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

/**
 * Class ValueItemRef
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config
 */
class ValueItemRef extends Section implements SectionInterface, ValueItemRefInterface
{

    /**
     * @var Field
     */
    protected $itemId;

    /**
     * @var Field
     */
    protected $value;

    /**
     * @var Field
     * @since 1.0.0
     */
    protected $typeId;

    public function setItemId(Field $itemId): ValueItemRefInterface
    {
        $this->itemId   = $itemId;
        $this->fields[] = $this->itemId;

        return $this;
    }

    public function getItemId(): Field
    {
        return $this->itemId;
    }

    public function setValueId(Field $value): ValueItemRefInterface
    {
        $this->value    = $value;
        $this->fields[] = $this->value;

        return $this;
    }

    public function getValueId(): Field
    {
        return $this->value;
    }

    public function setTypeId(Field $typeId): ValueItemRefInterface
    {
        $this->typeId   = $typeId;
        $this->fields[] = $this->typeId;

        return $this;
    }

    public function getTypeId(): ?Field
    {
        return $this->typeId;
    }
}
