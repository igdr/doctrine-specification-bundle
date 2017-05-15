<?php

namespace Igdr\DoctrineSpecificationBundle\Specification;

use Igdr\DoctrineSpecification\SpecificationInterface;

/**
 * Interface BaseSpecInterface
 */
interface BaseSpecInterface extends SpecificationInterface
{
    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId(string $id);

    /**
     * @return $this
     */
    public function applyWhere();

    /**
     * @return $this
     */
    public function applyOrder();
}
