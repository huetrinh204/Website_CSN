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
use Bluecoder\Component\Jfilters\Administrator\Model\Config\SectionInterface;

/**
 * This tries to simulate the object of type \Joomla\Component\Finder\Administrator\Indexer\Result
 *
 * Interface ItemInterface
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Section
 */
interface ItemInterface extends SectionInterface
{
    /**
     * @param Field $id
     * @return ItemInterface
     */
    public function setId(Field $id):ItemInterface;

    /**
     * @return null|\Bluecoder\Component\Jfilters\Administrator\Model\Config\Field
     */
    public function getId();

    /**
     * @param Field $title
     * @return ItemInterface
     */
    public function setTitle(Field $title): ItemInterface;

    /**
     * @return null|\Bluecoder\Component\Jfilters\Administrator\Model\Config\Field
     */
    public function getTitle();

    /**
     * @param Field $desciption
     * @return ItemInterface
     */
    public function setDesciption(Field $desciption): ItemInterface;

    /**
     * @return null|Field
     */
    public function getDesciption();

    /**
     * @param Field $state
     * @return ItemInterface
     */
    public function setState(Field $state): ItemInterface;

    /**
     * @return null|Field
     */
    public function getState();

    /**
     * @param Field $access
     * @return ItemInterface
     */
    public function setAccess(Field $access): ItemInterface;

    /**
     * @return null|Field
     */
    public function getAccess();

    /**
     * @param Field $language
     * @return ItemInterface
     */
    public function setLanguage(Field $language): ItemInterface;

    /**
     * @return null|Field
     */
    public function getLanguage();

    /**
     * @param Field $ordering
     * @return ItemInterface
     */
    public function setOrdering(Field $ordering): ItemInterface;

    /**
     * @return null|Field
     */
    public function getOrdering();


    /**
     * @param Field $modifiedDate
     * @return ItemInterface
     * @since 1.16.0
     */
    public function setModifiedDate(Field $modifiedDate): ItemInterface;

    /**
     * @return null|Field
     * @since 1.16.0
     */
    public function getModifiedDate() : ?Field;

    /**
     * @param Field $publishStartDate
     * @return ItemInterface
     */
    public function setPublishStartDate(Field $publishStartDate): ItemInterface;

    /**
     * @return null|Field
     */
    public function getPublishStartDate();

    /**
     * @param Field $publishEndDate
     * @return ItemInterface
     */
    public function setPublishEndDate(Field $publishEndDate): ItemInterface;

    /**
     * @return null|Field
     */
    public function getPublishEndDate();

    /**
     * @param Field $startDate
     * @return ItemInterface
     */
    public function setStartDate(Field $startDate): ItemInterface;

    /**
     * @return null|Field
     */
    public function getStartDate();

    /**
     * @param Field $endDate
     * @return ItemInterface
     */
    public function setEndDate(Field $endDate): ItemInterface;

    /**
     * @return null|Field
     */
    public function getEndDate();

    /**
     * @param Field $listPrice
     * @return ItemInterface
     */
    public function setListPrice(Field $listPrice): ItemInterface;

    /**
     * @return null|Field
     */
    public function getListPrice();

    /**
     * @param Field $salePrice
     * @return ItemInterface
     */
    public function setSalePrice(Field $salePrice): ItemInterface;

    /**
     * @return null|Field
     */
    public function getSalePrice();
}
