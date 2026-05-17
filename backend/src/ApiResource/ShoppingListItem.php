<?php

declare(strict_types=1);

namespace App\ApiResource;

use Symfony\Component\Serializer\Attribute\Groups;

final class ShoppingListItem
{
    #[Groups(['shopping_list:read'])]
    public ShoppingListIngredient $ingredient;

    #[Groups(['shopping_list:read'])]
    public string $quantity = '0';

    #[Groups(['shopping_list:read'])]
    public string $unit = '';
}
