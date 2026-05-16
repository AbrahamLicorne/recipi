<?php

declare(strict_types=1);

namespace App\ApiResource;

use Symfony\Component\Validator\Constraints as Assert;

final class RecipeIngredientInput
{
    #[Assert\NotBlank]
    public string $ingredient = '';

    #[Assert\NotBlank]
    public string $quantity = '';

    #[Assert\NotBlank]
    public string $unit = '';
}
