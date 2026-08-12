<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    public function index(): JsonResponse
    {
        $locations = Location::query()
            ->with('warehouse')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => LocationResource::collection(
                $locations->items(),
            ),
            'meta' => [
                'current_page' => $locations->currentPage(),
                'per_page' => $locations->perPage(),
                'total' => $locations->total(),
                'last_page' => $locations->lastPage(),
            ],
        ]);
    }

    public function tree(Warehouse $warehouse): JsonResponse
    {
        $locations = $warehouse->locations()
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => LocationResource::collection($locations),
        ]);
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $this->validateParent(
            $data['warehouse_id'],
            $data['parent_id'] ?? null,
        );

        $this->validateCodeUniqueness(
            $data['warehouse_id'],
            $data['code'],
        );

        $location = Location::query()->create($data);

        return response()->json([
            'data' => new LocationResource(
                $location->refresh(),
            ),
            'message' => 'Location created successfully.',
        ], 201);
    }

    public function show(Location $location): JsonResponse
    {
        return response()->json([
            'data' => new LocationResource(
                $location->load([
                    'warehouse',
                    'parent',
                ])
            ),
        ]);
    }

    public function update(
        UpdateLocationRequest $request,
        Location $location
    ): JsonResponse {
        $data = $request->validated();

        $warehouseId = $data['warehouse_id']
            ?? $location->warehouse_id;

        $parentId = array_key_exists('parent_id', $data)
            ? $data['parent_id']
            : $location->parent_id;

        $code = $data['code']
            ?? $location->code;

        $this->validateParent(
            $warehouseId,
            $parentId,
            $location,
        );

        $this->validateCodeUniqueness(
            $warehouseId,
            $code,
            $location,
        );

        $this->validateNoCircularReference(
            $location,
            $parentId,
        );

        DB::transaction(function () use ($location, $data): void {
            $location->update($data);
        });

        return response()->json([
            'data' => new LocationResource(
                $location->refresh()
            ),
            'message' => 'Location updated successfully.',
        ]);
    }

    private function validateParent(
        int $warehouseId,
        ?int $parentId,
        ?Location $location = null,
    ): void {
        if ($parentId === null) {
            return;
        }

        if ($location !== null && $parentId === $location->id) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'A location cannot be its own parent.',
                ],
            ]);
        }

        $parent = Location::query()->find($parentId);

        if ($parent === null) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'The selected parent location does not exist.',
                ],
            ]);
        }

        if ($parent->warehouse_id !== $warehouseId) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'The parent location must belong to the same warehouse.',
                ],
            ]);
        }
    }

    private function validateCodeUniqueness(
        int $warehouseId,
        string $code,
        ?Location $location = null,
    ): void {
        $exists = Location::query()
            ->where('warehouse_id', $warehouseId)
            ->where('code', $code)
            ->when(
                $location !== null,
                fn (Builder $query): Builder => $query->where(
                    'id',
                    '!=',
                    $location->id,
                ),
            )
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'code' => [
                    'The code has already been taken in this warehouse.',
                ],
            ]);
        }
    }

    private function validateNoCircularReference(
        Location $location,
        ?int $parentId,
    ): void {
        if ($parentId === null) {
            return;
        }

        $visited = [];

        $current = Location::query()
            ->select([
                'id',
                'parent_id',
            ])
            ->find($parentId);

        while ($current !== null) {
            if ($current->id === $location->id) {
                throw ValidationException::withMessages([
                    'parent_id' => [
                        'The selected parent would create a circular hierarchy.',
                    ],
                ]);
            }

            if (isset($visited[$current->id])) {
                throw ValidationException::withMessages([
                    'parent_id' => [
                        'The existing location hierarchy contains a circular reference.',
                    ],
                ]);
            }

            $visited[$current->id] = true;

            if ($current->parent_id === null) {
                break;
            }

            $current = Location::query()
                ->select([
                    'id',
                    'parent_id',
                ])
                ->find($current->parent_id);
        }
    }
}
