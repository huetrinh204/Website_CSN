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
 * Interface DefinitionInterface
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Section
 */
interface DefinitionInterface extends SectionInterface
{
    /**
     * @param Field\Id $id
     * @return DefinitionInterface
     * @since 1.0.0
     */
    public function setId(Field\Id $id): DefinitionInterface;

    /**
     * @return null|Field\Id
     * @since 1.0.0
     */
    public function getId();

    /**
     * @param Field $title
     * @return DefinitionInterface
     * @since 1.0.0
     */
    public function setTitle(Field $title): DefinitionInterface;

    /**
     * @return null|Field
     * @since 1.0.0
     */
    public function getTitle();

    /**
     * @param Field\Type $type
     * @return DefinitionInterface
     * @since 1.0.0
     */
    public function setType(Field\Type $type): DefinitionInterface;

    /**
     * @return null|Field\Type
     * @since 1.0.0
     */
    public function getType();

    /**
     * @param Field $context
     * @return DefinitionInterface
     */
    public function setContext(Field $context): DefinitionInterface;

    /**
     * @return null|Field
     * @since 1.0.0
     */
    public function getContext();

    /**
     * @param   null|Field  $condition
     *
     * @return DefinitionInterface
     * @since 1.5.3
     */
    public function setCondition(?Field $condition): DefinitionInterface;

    /**
     * @return null|Field
     * @since 1.5.3
     */
    public function getCondition();

    /**
     * @param Field $language
     * @return DefinitionInterface
     * @since 1.0.0
     */
    public function setLanguage(Field $language): DefinitionInterface;

    /**
     * @return null|Field
     * @since 1.0.0
     */
    public function getLanguage();

    /**
     * @param Field $params
     * @return DefinitionInterface
     * @since 1.0.0
     */
    public function setParams(Field $params): DefinitionInterface;

    /**
     * @return null|Field
     * @since 1.0.0
     */
    public function getParams();
}
