<?php

declare(strict_types=1);

namespace App\ApiResource;

use Symfony\Component\Serializer\Attribute\Groups;

final class ShoppingListIngredient
{
    #[Groups(['shopping_list:read'])]
    public int $id;

    #[Groups(['shopping_list:read'])]
    public string $name;
}
