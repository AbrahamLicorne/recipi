<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
#[ORM\Table(name: 'ingredient')]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $name;

    #[ORM\Column(length: 32)]
    private string $defaultUnit;

    /** @var Collection<int, RecipeIngredient> */
    #[ORM\OneToMany(targetEntity: RecipeIngredient::class, mappedBy: 'ingredient')]
    private Collection $recipeIngredients;

    public function __construct(string $name, string $defaultUnit)
    {
        $this->name = $name;
        $this->defaultUnit = $defaultUnit;
        $this->recipeIngredients = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDefaultUnit(): string { return $this->defaultUnit; }
    public function setDefaultUnit(string $defaultUnit): self { $this->defaultUnit = $defaultUnit; return $this; }

    /** @return Collection<int, RecipeIngredient> */
    public function getRecipeIngredients(): Collection { return $this->recipeIngredients; }
}
