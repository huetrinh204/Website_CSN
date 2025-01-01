<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FieldFactory;

class Dynamic implements DynamicInterface
{
    /**
     * @var FieldFactory
     * @since 1.0.0
     */
    protected $fieldFactory;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var string
     * @since 1.3.0
     */
    protected $dataType = 'string';

    /**
     * We have this true by default.
     *
     * @var bool
     * @since 1.0.0
     */
    protected $generateFilter = true;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $displays;

    /**
     * @var array
     * @since 1.3.0
     */
    protected $sortFieldsAdditional;

    /**
     * Dynamic constructor.
     *
     * @param   FieldFactory  $fieldFactory
     */
    public function __construct(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DynamicInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDataType(): ?string
    {
        return $this->dataType;
    }

    public function setDataType(?string $dataType): DynamicInterface
    {
        $this->dataType = $dataType;

        return $this;
    }

    public function getGenerateFilter(): bool
    {
        return $this->generateFilter;
    }

    public function setGenerateFilter(bool $generateFilter): DynamicInterface
    {
        $this->generateFilter = $generateFilter;

        return $this;
    }

    public function getDisplays(): ?array
    {
        return $this->displays;
    }

    public function setDisplays(array $displays): DynamicInterface
    {
        $this->displays = $displays;

        return $this;
    }

    public function generateDisplays(\SimpleXMLElement $data): DynamicInterface
    {
        $displays = [];
        if ($data->displays) {
            foreach ($data->displays->children() as $nodeName => $node) {
                if ($nodeName != 'display') {
                    continue;
                }
                /** @var Field\Display $display */
                $display = $this->fieldFactory->create($node, Field\Display::class);
                $display->setName($nodeName);
                $display->setValue((string)$node);
                if ($node->attributes()->multiselect) {
                    $display->setMultiselect($node->attributes()->multiselect == 'true');
                }
                if ($node->attributes()->range) {
                    $display->setRange($node->attributes()->range == 'true');
                }
                if ($node->attributes()->edition) {
                    $display->setEdition((string)$node->attributes()->edition);
                }
                $displays[] = $display;
            }
            $this->setDisplays($displays);
        }

        return $this;
    }

    public function setAdditionalSortFields(array $orderFields): DynamicInterface
    {
        $this->sortFieldsAdditional = $orderFields;

        return $this;
    }

    public function getAdditionalSortFields(): ?array
    {
        return $this->sortFieldsAdditional;
    }

    public function generateAdditionalSortFields(\SimpleXMLElement $data): DynamicInterface
    {
        $additionalOrderingFields = [];
        if ($data->additionalSortFields) {
            foreach ($data->additionalSortFields->children() as $nodeName => $node) {
                if ($nodeName != 'additionalSortField') {
                    continue;
                }
                /** @var Field $sortField */
                $sortField = $this->fieldFactory->create($node, Field::class);
                $sortField->setName($node->attributes()->label);
                $sortField->setValue((string)$node);
                if ($node->attributes()->edition) {
                    $sortField->setEdition((string)$node->attributes()->edition);
                }
                $additionalOrderingFields[] = $sortField;
            }
            $this->setAdditionalSortFields($additionalOrderingFields);
        }

        return $this;
    }
}
