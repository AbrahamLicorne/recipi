<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\Table(name: 'recipe')]
#[ORM\HasLifecycleCallbacks]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $name;

    #[ORM\Column(type: 'text')]
    private string $instructions = '';

    #[ORM\Column(type: 'smallint', options: ['default' => 2])]
    private int $servings = 2;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $prepTimeMin = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $cookTimeMin = null;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, RecipeIngredient> */
    #[ORM\OneToMany(targetEntity: RecipeIngredient::class, mappedBy: 'recipe', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $recipeIngredients;

    /** @var Collection<int, Tag> */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'recipes')]
    #[ORM\JoinTable(name: 'recipe_tag')]
    private Collection $tags;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->recipeIngredients = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getInstructions(): string { return $this->instructions; }
    public function setInstructions(string $instructions): self { $this->instructions = $instructions; return $this; }

    public function getServings(): int { return $this->servings; }
    public function setServings(int $servings): self { $this->servings = $servings; return $this; }

    public function getPrepTimeMin(): ?int { return $this->prepTimeMin; }
    public function setPrepTimeMin(?int $prepTimeMin): self { $this->prepTimeMin = $prepTimeMin; return $this; }

    public function getCookTimeMin(): ?int { return $this->cookTimeMin; }
    public function setCookTimeMin(?int $cookTimeMin): self { $this->cookTimeMin = $cookTimeMin; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** @return Collection<int, RecipeIngredient> */
    public function getRecipeIngredients(): Collection { return $this->recipeIngredients; }

    public function addRecipeIngredient(RecipeIngredient $ri): self
    {
        if (!$this->recipeIngredients->contains($ri)) {
            $this->recipeIngredients->add($ri);
            $ri->setRecipe($this);
        }
        return $this;
    }

    public function removeRecipeIngredient(RecipeIngredient $ri): self
    {
        $this->recipeIngredients->removeElement($ri);
        return $this;
    }

    /** @return Collection<int, Tag> */
    public function getTags(): Collection { return $this->tags; }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);
        return $this;
    }
}
