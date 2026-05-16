<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PlannedMealRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlannedMealRepository::class)]
#[ORM\Table(name: 'planned_meal')]
#[ORM\Index(name: 'idx_planned_meal_scheduled_for', columns: ['scheduled_for'])]
#[ORM\HasLifecycleCallbacks]
class PlannedMeal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date_immutable', unique: true)]
    private \DateTimeImmutable $scheduledFor;

    #[ORM\ManyToOne(targetEntity: Recipe::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Recipe $recipe;

    #[ORM\Column(type: 'smallint')]
    private int $servings;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(\DateTimeImmutable $scheduledFor, Recipe $recipe, ?int $servings = null)
    {
        $this->scheduledFor = $scheduledFor;
        $this->recipe = $recipe;
        $this->servings = $servings ?? $recipe->getServings();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getScheduledFor(): \DateTimeImmutable { return $this->scheduledFor; }
    public function setScheduledFor(\DateTimeImmutable $scheduledFor): self { $this->scheduledFor = $scheduledFor; return $this; }

    public function getRecipe(): Recipe { return $this->recipe; }
    public function setRecipe(Recipe $recipe): self { $this->recipe = $recipe; return $this; }

    public function getServings(): int { return $this->servings; }
    public function setServings(int $servings): self { $this->servings = $servings; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
