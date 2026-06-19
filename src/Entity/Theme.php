<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'Ce nom de thème est déjà utilisé.')]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $name = null;

    // #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(length: 120, unique: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private int $displayOrder = 0;

    #[ORM\OneToMany(targetEntity: Creation::class, mappedBy: 'theme')]
    private Collection $creations;

    public function __construct()
    {
        $this->creations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

        public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }


    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): static
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    public function getCreations(): Collection
    {
        return $this->creations;
    }
}
