<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Section;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Section;

/**
 * Class Item
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Section
 */
class Item extends Section implements ItemInterface
{
    /**
     * @var Field
     */
    protected $id;

    /**
     * @var Field
     */
    protected $title;

    /**
     * @var Field
     */
    protected $desciption;

    /**
     * @var Field
     */
    protected $state;

    /**
     * @var Field
     */
    protected $access;

    /**
     * @var Field
     */
    protected $language;

    /**
     * @var Field
     */
    protected $publishStartDate;

    /**
     * @var Field
     */
    protected $publishEndDate;

    /**
     * @var Field
     */
    protected $startDate;

    /**
     * @var Field
     */
    protected $endDate;

    /**
     * @var Field
     */
    protected $listPrice;

    /**
     * @var Field
     */
    protected $salePrice;

    /**
     * @var null|Field
     */
    protected $ordering;

    /**
     * @var null|Field
     * @since 1.16.0
     */
    protected $modifiedDate;


    /**
     * @param Field $id
     * @return ItemInterface
     */
    public function setId(Field $id): ItemInterface
    {
        $this->id = $id;
        $this->fields[] = $this->id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Field $title
     * @return ItemInterface
     */
    public function setTitle(Field $title): ItemInterface
    {
        $this->title = $title;
        $this->fields[] = $this->title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param Field $desciption
     * @return ItemInterface
     */
    public function setDesciption(Field $desciption): ItemInterface
    {
        $this->desciption = $desciption;
        $this->fields[] = $this->desciption;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDesciption()
    {
        return $this->desciption;
    }

    /**
     * @param Field $state
     * @return ItemInterface
     */
    public function setState(Field $state): ItemInterface
    {
        $this->state = $state;
        $this->fields[] = $this->state;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param Field $access
     * @return ItemInterface
     */
    public function setAccess(Field $access): ItemInterface
    {
        $this->access = $access;
        $this->fields[] = $this->access;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param Field $language
     * @return ItemInterface
     */
    public function setLanguage(Field $language): ItemInterface
    {
        $this->language = $language;
        $this->fields[] = $this->language;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return null|Field
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * @param Field $ordering
     * @return ItemInterface
     */
    public function setOrdering(Field $ordering): ItemInterface
    {
        $this->ordering = $ordering;
        $this->fields[] = $this->ordering;
        return $this;
    }

    public function setModifiedDate(Field $modifiedDate): ItemInterface
    {
        $this->modifiedDate = $modifiedDate;
        $this->fields[] = $this->ordering;
        return $this;
    }

    public function getModifiedDate(): ?Field
    {
       return $this->modifiedDate;
    }


    /**
     * @param Field $publishStartDate
     * @return ItemInterface
     */
    public function setPublishStartDate(Field $publishStartDate): ItemInterface
    {
        $this->publishStartDate = $publishStartDate;
        $this->fields[] = $this->publishStartDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublishStartDate()
    {
        return $this->publishStartDate;
    }

    /**
     * @param Field $publishEndDate
     * @return ItemInterface
     */
    public function setPublishEndDate(Field $publishEndDate): ItemInterface
    {
        $this->publishEndDate = $publishEndDate;
        $this->fields[] = $this->publishEndDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublishEndDate()
    {
        return $this->publishEndDate;
    }

    /**
     * @param Field $startDate
     * @return ItemInterface
     */
    public function setStartDate(Field $startDate): ItemInterface
    {
        $this->startDate = $startDate;
        $this->fields[] = $this->startDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param Field $endDate
     * @return ItemInterface
     */
    public function setEndDate(Field $endDate): ItemInterface
    {
        $this->endDate = $endDate;
        $this->fields[] = $this->endDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param Field $listPrice
     * @return ItemInterface
     */
    public function setListPrice(Field $listPrice): ItemInterface
    {
        $this->listPrice = $listPrice;
        $this->fields[] = $this->listPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getListPrice()
    {
        return $this->listPrice;
    }

    /**
     * @param Field $salePrice
     * @return ItemInterface
     */
    public function setSalePrice(Field $salePrice): ItemInterface
    {
        $this->salePrice = $salePrice;
        $this->fields[] = $this->salePrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSalePrice()
    {
        return $this->salePrice;
    }

}
