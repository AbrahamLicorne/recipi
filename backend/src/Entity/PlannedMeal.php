<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use App\Repository\PlannedMealRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlannedMealRepository::class)]
#[ORM\Table(name: 'planned_meal')]
#[ORM\Index(name: 'idx_planned_meal_scheduled_for', columns: ['scheduled_for'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['planned_meal:read']],
    denormalizationContext: ['groups' => ['planned_meal:write']],
    paginationItemsPerPage: 14,
    order: ['scheduledFor' => 'ASC'],
)]
#[ApiFilter(DateFilter::class, properties: ['scheduledFor'])]
#[ApiFilter(OrderFilter::class, properties: ['scheduledFor'])]
class PlannedMeal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['planned_meal:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'date_immutable', unique: true)]
    #[Groups(['planned_meal:read', 'planned_meal:write'])]
    #[Assert\NotNull]
    private \DateTimeImmutable $scheduledFor;

    #[ORM\ManyToOne(targetEntity: Recipe::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Groups(['planned_meal:read', 'planned_meal:write'])]
    #[Assert\NotNull]
    private Recipe $recipe;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['planned_meal:read', 'planned_meal:write'])]
    #[Assert\Positive]
    private int $servings;

    #[ORM\Column(type: 'datetimetz_immutable')]
    #[Groups(['planned_meal:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct(?\DateTimeImmutable $scheduledFor = null, ?Recipe $recipe = null, ?int $servings = null)
    {
        if ($scheduledFor !== null) {
            $this->scheduledFor = $scheduledFor;
        }
        if ($recipe !== null) {
            $this->recipe = $recipe;
            $this->servings = $servings ?? $recipe->getServings();
        }
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        // If servings was not explicitly set, copy from recipe default
        if (!isset($this->servings) && isset($this->recipe)) {
            $this->servings = $this->recipe->getServings();
        }
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
