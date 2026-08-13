<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Inventory\UnitOfMeasureController;
use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Http\Controllers\Inventory\ItemTypeController;
use App\Http\Controllers\Inventory\AttributeDefinitionController;
use App\Http\Controllers\Inventory\AttributeOptionController;
use App\Http\Controllers\Inventory\ItemTypeAttributeController;
use App\Http\Controllers\Inventory\ItemTypeMetadataController;
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Inventory\LocationController;
use App\Http\Controllers\Inventory\SupplierController;
use App\Http\Controllers\Inventory\InventoryTransactionController;
use App\Http\Controllers\Inventory\InventoryLotController;
use App\Http\Controllers\Inventory\InventorySerialController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::apiResource('users', UserController::class);

        Route::prefix('inventory')->group(function () {
            Route::apiResource('units', UnitOfMeasureController::class)->only(['index','store','show','update',]);
            Route::post('/units/{unit}/activate', [UnitOfMeasureController::class, 'activate'])->name('units.activate');
            Route::post('/units/{unit}/deactivate', [UnitOfMeasureController::class, 'deactivate'])->name('units.deactivate');
            
            Route::get('/categories/tree', [ItemCategoryController::class, 'tree'])->name('categories.tree');// 1
            Route::apiResource('categories', ItemCategoryController::class);// 2
            Route::post('/categories/{category}/activate', [ItemCategoryController::class, 'activate'])->name('categories.activate');
            Route::post('/categories/{category}/deactivate', [ItemCategoryController::class, 'deactivate'])->name('categories.deactivate');

            Route::apiResource('item-types', ItemTypeController::class)->parameters(['item-types' => 'itemType',])->only(['index','store','show','update',]);
            Route::post('item-types/{itemType}/activate',[ItemTypeController::class, 'activate'])->name('item-types.activate');
            Route::post('item-types/{itemType}/deactivate',[ItemTypeController::class, 'deactivate'])->name('item-types.deactivate');

            Route::get('item-types/{itemType}/attributes/metadata',[ItemTypeMetadataController::class, 'show'])->name('item-types.attributes.metadata');
            Route::get('item-types/{itemType}/attributes',[ItemTypeAttributeController::class, 'index'])->name('item-type-attributes.index');
            Route::post('item-types/{itemType}/attributes',[ItemTypeAttributeController::class, 'store'])->name('item-type-attributes.store');
            Route::patch('item-types/{itemType}/attributes/{itemTypeAttribute}',[ItemTypeAttributeController::class, 'update'])->name('item-type-attributes.update');
            Route::delete('item-types/{itemType}/attributes/{itemTypeAttribute}',[ItemTypeAttributeController::class, 'destroy'])->name('item-type-attributes.destroy');

            Route::apiResource('attributes', AttributeDefinitionController::class)->only(['index','store','show','update',]);
            Route::post('attributes/{attribute}/activate',[AttributeDefinitionController::class, 'activate'])->name('attributes.activate');
            Route::post('attributes/{attribute}/deactivate',[AttributeDefinitionController::class, 'deactivate'])->name('attributes.deactivate');

            Route::get('attributes/{attribute}/options',[AttributeOptionController::class, 'index'])->name('attribute-options.index');
            Route::post('attributes/{attribute}/options',[AttributeOptionController::class, 'store'])->name('attribute-options.store');
            Route::apiResource('attribute-options', AttributeOptionController::class)->only(['show','update','destroy'])->parameters(['attribute-options' => 'option',]);
            Route::post('attribute-options/{option}/activate',[AttributeOptionController::class, 'activate'])->name('attribute-options.activate');
            Route::post('attribute-options/{option}/deactivate',[AttributeOptionController::class, 'deactivate'])->name('attribute-options.deactivate');

            Route::apiResource('items', ItemController::class);
            Route::post('items/{item}/restore',[ItemController::class, 'restore'])->withTrashed()->name('items.restore');
            Route::post('items/{item}/activate',[ItemController::class, 'activate'])->name('items.activate');
            Route::post('items/{item}/deactivate',[ItemController::class, 'deactivate'])->name('items.deactivate');
            Route::get('items/{item}/attributes',[ItemController::class, 'getAttributes'])->name('items.attributes');
            Route::patch('items/{item}/attributes',[ItemController::class, 'updateAttributes'])->name('items.attributes.update');

            Route::apiResource('warehouses', WarehouseController::class)->only(['index','store','show','update',]);
            Route::get('warehouses/{warehouse}/locations/tree',[LocationController::class, 'tree'],)->name('warehouses.locations.tree');
            Route::apiResource('locations', LocationController::class)->only(['index','store','show','update',]);

            Route::apiResource('suppliers', SupplierController::class)->only(['index','store','show','update',]);
            
            Route::apiResource('inventory-transactions',InventoryTransactionController::class)->only(['index','store','show',]);

            Route::apiResource('inventory-lots', InventoryLotController::class)->parameters(['lots' => 'inventoryLot',])->only(['index','store','show','update',]);

            Route::apiResource('inventory-serials', InventorySerialController::class)->parameters(['serials' => 'inventorySerial', ])->only(['index','store','show','update',]);
        });
                    
    });
});
