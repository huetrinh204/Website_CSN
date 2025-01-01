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
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;

/**
 * Class Context
 *
 * Context configuration class
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config
 */
class Context implements ContextInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ItemInterface
     */
    protected $item;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var int
     * @since 1.0.0
     */
    protected $typeId;

    /**
     * Context constructor.
     */
    public function __construct()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    public function setName(string $name): ConfigInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSections(): array
    {
        return [
            $this->getItem()
        ];
    }

    public function setItem(ItemInterface $item): ContextInterface
    {
        $this->item = $item;
        return $this;
    }

    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    public function setAlias(string $alias) : ContextInterface
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * If the alias is empty, return the name as alias
     *
     * @return string|null
     */
    public function getAlias()
    {
        return $this->alias?:$this->getName();
    }

    public function setTypeId(int $typeId): ContextInterface
    {
        $this->typeId = $typeId;
        return $this;

    }

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function generateConfigItem(\SimpleXMLElement $data): ItemInterface
    {
        if (!isset($data->item)) {
            throw new \RuntimeException(sprintf('The item section is missing in the configuration of the context %s',
                $data['name']));
        }
        /** @var  ItemInterface $configItem */
        $configItem = $this->objectManager->createObject(ItemInterface::class);
        $itemData = $data->item;

        if (!empty($itemData['dbTable'])) {
            $configItem->setDbTable($itemData['dbTable']);
        }

        $configItem->setFields($itemData);
        return $configItem;
    }
}
