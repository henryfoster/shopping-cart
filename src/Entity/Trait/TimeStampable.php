<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait TimeStampable
{
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function initCreatedAtAndUpdatedAtValues(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->initUpdatedAtValue();
    }

    #[ORM\PreUpdate]
    public function initUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
