<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

class SubFormField
{
    /**
     * @var string
     * @since 1.0.0
     */
    protected $context;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var int
     * @since 1.16.5
     */
    protected $id;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $subfields;

    /**
     * SubFormField constructor.
     * We must provide either 'name' OR 'id'
     *
     * @param string $context
     * @param array $subfields
     * @param int|null $id
     * @param string|null $name
     * @since 1.0.0
     */
    public function __construct(string $context, array $subfields, ?string $name = null, ?int $id = null)
    {
        if (empty($id) && empty($name)) {
            throw new \RuntimeException('`SubFormField` cannot be created when both the $name and the $id are empty');
        }
        $this->context = $context;
        $this->id = $id;
        $this->name = $name;
        $this->subfields = $subfields;
    }

    /**
     * @return string
     * @since 1.0.0
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @return string|null
     * @since 1.0.0
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return int|null
     * @since 1.16.5
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array
     * @since 1.0.0
     */
    public function getSubfields(): array
    {
        return $this->subfields;
    }
}