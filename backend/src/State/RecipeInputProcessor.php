<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\RecipeIngredientInput;
use App\ApiResource\RecipeInput;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<RecipeInput, Recipe>
 */
final class RecipeInputProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Recipe
    {
        if (!$data instanceof RecipeInput) {
            throw new \LogicException(sprintf('Expected %s, got %s', RecipeInput::class, get_debug_type($data)));
        }

        // For PATCH, only keys actually present in the JSON payload should be applied.
        // The Request is exposed via $context['request'].
        $request = $context['request'] ?? null;
        $payloadKeys = [];
        if ($request !== null) {
            $decoded = json_decode((string) $request->getContent(), true);
            if (is_array($decoded)) {
                $payloadKeys = array_keys($decoded);
            }
        }

        $isPatch = $operation instanceof Patch;

        if ($isPatch) {
            $id = $uriVariables['id'] ?? null;
            $recipe = $id !== null ? $this->em->find(Recipe::class, $id) : null;
            if ($recipe === null) {
                throw new NotFoundHttpException('Recipe not found');
            }
        } else {
            $recipe = new Recipe();
        }

        $shouldApply = static fn(string $key): bool => !$isPatch || in_array($key, $payloadKeys, true);

        if ($shouldApply('name') && $data->name !== null) {
            $recipe->setName($data->name);
        }
        if ($shouldApply('instructions') && $data->instructions !== null) {
            $recipe->setInstructions($data->instructions);
        }
        if ($shouldApply('servings') && $data->servings !== null) {
            $recipe->setServings($data->servings);
        }
        if ($shouldApply('prepTimeMin')) {
            $recipe->setPrepTimeMin($data->prepTimeMin);
        }
        if ($shouldApply('cookTimeMin')) {
            $recipe->setCookTimeMin($data->cookTimeMin);
        }
        if ($shouldApply('tags')) {
            $this->replaceTags($recipe, $data->tags ?? []);
        }
        if ($shouldApply('recipeIngredients')) {
            $this->replaceRecipeIngredients($recipe, $data->recipeIngredients ?? []);
        }

        if (!$isPatch) {
            $this->em->persist($recipe);
        }
        $this->em->flush();

        return $recipe;
    }

    /**
     * @param string[] $tagIris
     */
    private function replaceTags(Recipe $recipe, array $tagIris): void
    {
        foreach ($recipe->getTags() as $tag) {
            $recipe->removeTag($tag);
        }
        foreach ($tagIris as $iri) {
            $tag = $this->iriConverter->getResourceFromIri($iri);
            if (!$tag instanceof Tag) {
                throw new \InvalidArgumentException(sprintf('IRI "%s" did not resolve to a Tag', $iri));
            }
            $recipe->addTag($tag);
        }
    }

    /**
     * Diff-and-apply: update existing rows in place, drop the ones missing
     * from the new list, and create the new ones. This avoids identity-map
     * collisions on the composite PK when a (recipe, ingredient) pair is
     * "replaced" within the same UnitOfWork.
     *
     * @param RecipeIngredientInput[] $items
     */
    private function replaceRecipeIngredients(Recipe $recipe, array $items): void
    {
        /** @var array<int, RecipeIngredient> $existing keyed by ingredient id */
        $existing = [];
        foreach ($recipe->getRecipeIngredients() as $ri) {
            $existing[$ri->getIngredient()->getId()] = $ri;
        }

        $keep = [];
        foreach ($items as $item) {
            if (!$item instanceof RecipeIngredientInput) {
                throw new \LogicException('recipeIngredients items must be RecipeIngredientInput');
            }
            $ingredient = $this->iriConverter->getResourceFromIri($item->ingredient);
            if (!$ingredient instanceof Ingredient) {
                throw new \InvalidArgumentException(sprintf('IRI "%s" did not resolve to an Ingredient', $item->ingredient));
            }
            $ingredientId = $ingredient->getId();
            $keep[$ingredientId] = true;

            if (isset($existing[$ingredientId])) {
                $existing[$ingredientId]->setQuantity($item->quantity);
                $existing[$ingredientId]->setUnit($item->unit);
            } else {
                $ri = new RecipeIngredient($recipe, $ingredient, $item->quantity, $item->unit);
                $recipe->addRecipeIngredient($ri);
            }
        }

        foreach ($existing as $ingredientId => $ri) {
            if (!isset($keep[$ingredientId])) {
                $recipe->removeRecipeIngredient($ri);
            }
        }
    }
}
