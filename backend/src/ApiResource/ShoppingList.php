<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\State\ShoppingListProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/shopping-list',
            provider: ShoppingListProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                summary: 'Compute shopping list for a date range',
                description: 'Aggregates ingredients from all planned_meals between `from` and `to` (inclusive), scaled by each meal\'s servings.',
                parameters: [
                    new Parameter(name: 'from', in: 'query', description: 'Start date (YYYY-MM-DD). Defaults to today.', required: false, schema: ['type' => 'string', 'format' => 'date']),
                    new Parameter(name: 'to', in: 'query', description: 'End date (YYYY-MM-DD), inclusive. Defaults to `from` + 6 days.', required: false, schema: ['type' => 'string', 'format' => 'date']),
                ],
            ),
        ),
    ],
    normalizationContext: ['groups' => ['shopping_list:read']],
)]
final class ShoppingList
{
    #[Groups(['shopping_list:read'])]
    public string $from = '';

    #[Groups(['shopping_list:read'])]
    public string $to = '';

    /** @var ShoppingListItem[] */
    #[Groups(['shopping_list:read'])]
    public array $items = [];
}
