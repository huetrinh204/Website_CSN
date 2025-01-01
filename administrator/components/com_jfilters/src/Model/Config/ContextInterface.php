<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Section\ItemInterface;

/**
 * It defines the Context config
 *
 * Interface ContextInterface
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config
 */
interface ContextInterface extends ConfigInterface
{
    /**
     * @param ItemInterface $item
     * @return ContextInterface
     */
    public function setItem(ItemInterface $item) : ContextInterface;

    /**
     * @return ItemInterface
     */
    public function getItem() :ItemInterface ;

    /**
     * Set the alias of that Config
     *
     * @param string $alias
     * @return ContextInterface
     */
    public function setAlias(string $alias) : ContextInterface;

    /**
     * Get the alias of that Config
     *
     * @return null|string
     */
    public function getAlias();

    /**
     * Set the typeId of that Config
     *
     * @param int $typeId
     * @return ContextInterface
     */
    public function setTypeId(int $typeId) : ContextInterface;

    /**
     * Get the typeId of that Config
     *
     * @return null|int
     */
    public function getTypeId() : ?int;

    /**
     * Generate the config Item from the xml
     *
     * @param \SimpleXMLElement $data
     * @return ItemInterface
     */
    public function generateConfigItem(\SimpleXMLElement $data): ItemInterface;
}
