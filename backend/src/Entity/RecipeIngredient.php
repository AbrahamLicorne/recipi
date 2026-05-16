<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RecipeIngredientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RecipeIngredientRepository::class)]
#[ORM\Table(name: 'recipe_ingredient')]
class RecipeIngredient
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'recipeIngredients')]
    #[ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Recipe $recipe;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Ingredient::class, inversedBy: 'recipeIngredients')]
    #[ORM\JoinColumn(name: 'ingredient_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[Groups(['recipe:read'])]
    private Ingredient $ingredient;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 3)]
    #[Groups(['recipe:read'])]
    private string $quantity;

    #[ORM\Column(length: 32)]
    #[Groups(['recipe:read'])]
    private string $unit;

    public function __construct(Recipe $recipe, Ingredient $ingredient, string $quantity, string $unit)
    {
        $this->recipe = $recipe;
        $this->ingredient = $ingredient;
        $this->quantity = $quantity;
        $this->unit = $unit;
    }

    public function getRecipe(): Recipe { return $this->recipe; }
    public function setRecipe(Recipe $recipe): self { $this->recipe = $recipe; return $this; }

    public function getIngredient(): Ingredient { return $this->ingredient; }
    public function setIngredient(Ingredient $ingredient): self { $this->ingredient = $ingredient; return $this; }

    public function getQuantity(): string { return $this->quantity; }
    public function setQuantity(string $quantity): self { $this->quantity = $quantity; return $this; }

    public function getUnit(): string { return $this->unit; }
    public function setUnit(string $unit): self { $this->unit = $unit; return $this; }
}
