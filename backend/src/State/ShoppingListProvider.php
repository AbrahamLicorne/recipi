<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ShoppingList;
use App\ApiResource\ShoppingListIngredient;
use App\ApiResource\ShoppingListItem;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProviderInterface<ShoppingList>
 */
final class ShoppingListProvider implements ProviderInterface
{
    public function __construct(
        private readonly Connection $db,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ShoppingList
    {
        $request = $this->requestStack->getCurrentRequest();

        $fromParam = $request?->query->get('from');
        $toParam = $request?->query->get('to');

        try {
            $from = $fromParam !== null
                ? new \DateTimeImmutable($fromParam)
                : new \DateTimeImmutable('today');
        } catch (\Exception) {
            throw new BadRequestHttpException(sprintf('Invalid "from" date: %s', $fromParam));
        }

        try {
            $to = $toParam !== null
                ? new \DateTimeImmutable($toParam)
                : $from->modify('+6 days');
        } catch (\Exception) {
            throw new BadRequestHttpException(sprintf('Invalid "to" date: %s', $toParam));
        }

        if ($to < $from) {
            throw new BadRequestHttpException('"to" must be on or after "from"');
        }

        $rows = $this->db->fetchAllAssociative(
            <<<SQL
            SELECT i.id AS ingredient_id,
                   i.name AS ingredient_name,
                   ri.unit,
                   SUM(ri.quantity * pm.servings::numeric / r.servings) AS qty
              FROM planned_meal pm
              JOIN recipe r ON r.id = pm.recipe_id
              JOIN recipe_ingredient ri ON ri.recipe_id = r.id
              JOIN ingredient i ON i.id = ri.ingredient_id
             WHERE pm.scheduled_for BETWEEN :from AND :to
             GROUP BY i.id, i.name, ri.unit
             ORDER BY i.name, ri.unit
            SQL,
            ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]
        );

        $list = new ShoppingList();
        $list->from = $from->format('Y-m-d');
        $list->to = $to->format('Y-m-d');
        $list->items = array_map(static function (array $row): ShoppingListItem {
            $item = new ShoppingListItem();
            $ingredient = new ShoppingListIngredient();
            $ingredient->id = (int) $row['ingredient_id'];
            $ingredient->name = (string) $row['ingredient_name'];
            $item->ingredient = $ingredient;
            $item->quantity = self::trimNumber((string) $row['qty']);
            $item->unit = (string) $row['unit'];
            return $item;
        }, $rows);

        return $list;
    }

    /**
     * "900.0000000000" -> "900", "375.5000" -> "375.5", keeps at least 1 decimal-free value.
     */
    private static function trimNumber(string $value): string
    {
        if (!str_contains($value, '.')) {
            return $value;
        }
        $trimmed = rtrim(rtrim($value, '0'), '.');
        return $trimmed === '' ? '0' : $trimmed;
    }
}
