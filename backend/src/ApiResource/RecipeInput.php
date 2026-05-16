<?php

declare(strict_types=1);

namespace App\ApiResource;

use Symfony\Component\Validator\Constraints as Assert;

final class RecipeInput
{
    #[Assert\NotBlank(groups: ['recipe:create'])]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotNull(groups: ['recipe:create'])]
    public ?string $instructions = null;

    #[Assert\NotNull(groups: ['recipe:create'])]
    #[Assert\Positive]
    public ?int $servings = null;

    #[Assert\PositiveOrZero]
    public ?int $prepTimeMin = null;

    #[Assert\PositiveOrZero]
    public ?int $cookTimeMin = null;

    /**
     * Array of Tag IRIs (e.g. "/api/tags/3").
     *
     * @var string[]|null
     */
    public ?array $tags = null;

    /**
     * @var RecipeIngredientInput[]|null
     */
    #[Assert\Valid]
    public ?array $recipeIngredients = null;
}
