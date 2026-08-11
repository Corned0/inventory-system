
Item
├── Model                       - php artisan make:model [modelname] --m
├── Migration                   - auto create by [--m]
├── Seeder
├── StoreItemTypeRequest        - php artisan make:request ItemType/StoreItemTypeRequest
├── UpdateItemTypeRequest       - php artisan make:request ItemType/UpdateItemTypeRequest
├── ItemTypeResource            - php artisan make:resource ItemTypeResource
├── ItemTypeController          - php artisan make:controller Inventory/ItemTypeController --api [--api to auto create basic functions]
├── Routes
├── Pest tests                  - php artisan pest:test [name]
└── Factory                     - php artisan make:factory ItemTypeFactory --model=ItemType

run test                        - php artisan test --filter=[class name][include filter for specific class)