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
use Bluecoder\Component\Jfilters\Administrator\Model\Config\SectionInterface;

/**
 * Interface ValueItemRefInterface
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config
 */
Interface ValueItemRefInterface extends SectionInterface
{
    /**
     * @param \Bluecoder\Component\Jfilters\Administrator\Model\Config\Field $id
     * @return ValueItemRefInterface
     * @since 1.0.0
     */
    public function setItemId(Field $id): ValueItemRefInterface;

    /**
     * @return \Bluecoder\Component\Jfilters\Administrator\Model\Config\Field
     * @since 1.0.0
     */
    public function getItemId(): Field;

    /**
     * @param \Bluecoder\Component\Jfilters\Administrator\Model\Config\Field $value
     * @return ValueItemRefInterface
     * @since 1.0.0
     */
    public function setValueId(Field $value): ValueItemRefInterface;

    /**
     * @return Field
     * @since 1.0.0
     */
    public function getValueId(): Field;

    /**
     * @param \Bluecoder\Component\Jfilters\Administrator\Model\Config\Field $typeId
     * @return ValueItemRefInterface
     * @since 1.0.0
     */
    public function setTypeId(Field $typeId): ValueItemRefInterface;

    /**
     * @return Field|null
     * @since 1.0.0
     */
    public function getTypeId(): ?Field;


}
