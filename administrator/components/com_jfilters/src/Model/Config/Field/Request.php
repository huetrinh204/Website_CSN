<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;

/**
 * Class Request for field of type request. See filters.xml
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Field
 */
class Request extends Field
{
    /**
     * @var string
     * @since 1.0.0
     */
    protected $extension;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $view;

    /**
     * Return the extension of the field
     *
     * @return string
     * @throws \RuntimeException
     * @since 1.0.0
     */
    public function getExtension(): string
    {
        if ($this->extension === null) {
            throw new \RuntimeException('No extension is specified for a field. Check the filters.xml');
        }
        return $this->extension;
    }

    /**
     * @param string $extension
     * @return $this
     * @since 1.0.0
     */
    public function setExtension(string $extension) : Request
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * Return the view of the field
     *
     * @return string
     * @throws \RuntimeException
     * @since 1.0.0
     */
    public function getView(): string
    {
        if ($this->view === null) {
            throw new \RuntimeException('No view is specified for a field. Check the filters.xml');
        }
        return $this->view;
    }

    /**
     * @param string $view
     * @return $this
     * @since 1.0.0
     */
    public function setView(string $view) : Request
    {
        $this->view = $view;
        return $this;
    }
}
