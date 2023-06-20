<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Add Activable behavior to an entity.
 */
trait ActivableBoolean
{
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $actif = 1;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_deleted = 0;

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }
}
