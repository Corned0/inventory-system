# Inventory API

Inventory management API for units of measure, categories, item types, dynamic attributes, attribute options, and inventory items.

## Base URL

```text
/api/auth/inventory
```

All endpoints require authentication and should be called with the authenticated user's credentials.

---

# 1. Units of Measure

Units define how inventory quantities are measured.

## List Units

```http
GET /units
```

Example:

```http
GET /api/auth/inventory/units
```

## Create Unit

```http
POST /units
```

Request:

```json
{
    "code": "PC",
    "name": "Piece",
    "symbol": "pc",
    "decimal_places": 0
}
```

## Get Unit

```http
GET /units/{unit}
```

Example:

```http
GET /api/auth/inventory/units/1
```

## Update Unit

```http
PATCH /units/{unit}
```

Request:

```json
{
    "name": "Pieces",
    "symbol": "pcs"
}
```

## Activate Unit

```http
POST /units/{unit}/activate
```

## Deactivate Unit

```http
POST /units/{unit}/deactivate
```

---

# 2. Item Categories

Categories organize inventory items hierarchically.

Example:

```text
IT Equipment
├── Computers
│   ├── Desktop Computers
│   └── Laptop Computers
└── Networking Equipment
```

## List Categories

```http
GET /categories
```

## Create Category

```http
POST /categories
```

Request:

```json
{
    "parent_id": null,
    "code": "IT",
    "name": "IT Equipment",
    "description": "Information technology equipment."
}
```

Child category:

```json
{
    "parent_id": 1,
    "code": "IT-COMPUTERS",
    "name": "Computers",
    "description": "Desktop and laptop computers."
}
```

## Get Category

```http
GET /categories/{category}
```

## Update Category

```http
PATCH /categories/{category}
```

Example:

```json
{
    "name": "Computer Equipment"
}
```

A category cannot be assigned to itself or one of its descendants.

## Delete Category

```http
DELETE /categories/{category}
```

## Category Tree

Returns the hierarchical category structure.

```http
GET /categories/tree
```

## Activate Category

```http
POST /categories/{category}/activate
```

## Deactivate Category

```http
POST /categories/{category}/deactivate
```

---

# 3. Item Types

Item types determine how an item behaves.

Examples:

```text
COMPUTER
OFFICE_SUPPLY
FURNITURE
BUILDING
LAND
```

## Tracking Types

```text
none
lot
serial
```

Example:

```json
{
    "code": "COMPUTER",
    "name": "Computer Equipment",
    "description": "Computer equipment.",
    "tracking_type": "serial",
    "is_asset": true,
    "is_composite": true,
    "is_active": true
}
```

## List Item Types

```http
GET /item-types
```

## Create Item Type

```http
POST /item-types
```

Example:

```json
{
    "code": "COMPUTER",
    "name": "Computer Equipment",
    "description": "Computer equipment.",
    "tracking_type": "serial",
    "is_asset": true,
    "is_composite": true
}
```

## Get Item Type

```http
GET /item-types/{itemType}
```

## Update Item Type

```http
PATCH /item-types/{itemType}
```

## Activate Item Type

```http
POST /item-types/{itemType}/activate
```

## Deactivate Item Type

```http
POST /item-types/{itemType}/deactivate
```

---

# 4. Attribute Definitions

Attribute definitions describe configurable fields that can be attached to item types.

Examples:

```text
Brand
Model
Processor
RAM
Storage
Operating System
Color
Material
```

## Data Types

Supported attribute data types:

```text
text
textarea
integer
decimal
boolean
date
datetime
select
multiselect
```

## List Attributes

```http
GET /attributes
```

## Create Attribute

```http
POST /attributes
```

Example:

```json
{
    "code": "brand",
    "name": "Brand",
    "data_type": "select",
    "description": "Equipment manufacturer.",
    "is_required": true,
    "is_active": true,
    "sort_order": 1,
    "validation_rules": null,
    "default_value": null
}
```

## Get Attribute

```http
GET /attributes/{attribute}
```

## Update Attribute

```http
PATCH /attributes/{attribute}
```

Example:

```json
{
    "name": "Manufacturer",
    "description": "Manufacturer of the equipment."
}
```

## Activate Attribute

```http
POST /attributes/{attribute}/activate
```

## Deactivate Attribute

```http
POST /attributes/{attribute}/deactivate
```

---

# 5. Attribute Options

Attribute options are used by `select` and `multiselect` attributes.

For example, the `brand` attribute can have:

```text
Dell
HP
Lenovo
Acer
```

## List Attribute Options

Options are scoped to an attribute.

```http
GET /attributes/{attribute}/options
```

Example:

```http
GET /api/auth/inventory/attributes/1/options
```

## Create Attribute Option

```http
POST /attributes/{attribute}/options
```

Request:

```json
{
    "value": "dell",
    "label": "Dell",
    "sort_order": 1,
    "is_active": true
}
```

## Get Attribute Option

```http
GET /attribute-options/{option}
```

## Update Attribute Option

```http
PATCH /attribute-options/{option}
```

Example:

```json
{
    "label": "Dell Technologies"
}
```

## Delete Attribute Option

```http
DELETE /attribute-options/{option}
```

## Activate Attribute Option

```http
POST /attribute-options/{option}/activate
```

## Deactivate Attribute Option

```http
POST /attribute-options/{option}/deactivate
```

---

# 6. Item Type Attributes

Item type attributes determine which dynamic attributes are available for a particular item type.

For example:

```text
COMPUTER

Brand
Model
Processor
RAM
Storage
Operating System
```

while:

```text
FURNITURE

Brand
Material
Color
Dimensions
```

The same attribute definition can therefore be reused by multiple item types.

## List Item Type Attributes

```http
GET /item-types/{itemType}/attributes
```

Example:

```http
GET /api/auth/inventory/item-types/1/attributes
```

## Assign Attribute to Item Type

```http
POST /item-types/{itemType}/attributes
```

Request:

```json
{
    "attribute_definition_id": 1,
    "is_required": true,
    "sort_order": 1
}
```

## Update Item Type Attribute

```http
PATCH /item-types/{itemType}/attributes/{itemTypeAttribute}
```

Example:

```json
{
    "is_required": false,
    "sort_order": 2
}
```

## Remove Attribute from Item Type

```http
DELETE /item-types/{itemType}/attributes/{itemTypeAttribute}
```

---

# 7. Dynamic Form Metadata

The metadata endpoint returns the attributes required to construct an item's dynamic form.

```http
GET /item-types/{itemType}/attributes/metadata
```

Example:

```http
GET /api/auth/inventory/item-types/1/attributes/metadata
```

Example response:

```json
{
    "item_type": {
        "id": 1,
        "code": "COMPUTER"
    },
    "attributes": [
        {
            "id": 1,
            "code": "brand",
            "name": "Brand",
            "data_type": "select",
            "required": true,
            "options": [
                {
                    "value": "dell",
                    "label": "Dell"
                },
                {
                    "value": "hp",
                    "label": "HP"
                }
            ]
        },
        {
            "id": 2,
            "code": "ram",
            "name": "RAM",
            "data_type": "integer",
            "required": false
        }
    ]
}
```

The frontend can use this response to dynamically construct the item form.

---

# 8. Items

Items are the actual inventory records.

The `items` table contains only the fixed/common fields.

Dynamic attributes are stored separately.

## List Items

```http
GET /items
```

## Create Item

```http
POST /items
```

Example:

```json
{
    "name": "Dell Precision 3680",
    "item_type_id": 1,
    "category_id": 2,
    "unit_of_measure_id": 1,
    "barcode": "123456789",
    "reorder_level": 1,
    "reorder_quantity": 2,
    "attributes": {
        "brand": "Dell",
        "model": "Precision 3680",
        "processor": "Intel i7-14700",
        "ram": 32
    }
}
```

The `item_code` is generated by the backend.

Example:

```text
ITM-000001
```

Do not send `item_code` when creating an item.

## Get Item

```http
GET /items/{item}
```

Example response:

```json
{
    "data": {
        "id": 100,
        "item_code": "ITM-000100",
        "name": "Dell Precision 3680",
        "item_type": {
            "id": 1,
            "code": "COMPUTER",
            "name": "Computer Equipment"
        },
        "category": {
            "id": 2,
            "name": "IT Equipment"
        },
        "unit": {
            "code": "PC",
            "name": "Piece"
        },
        "tracking_type": "serial",
        "attributes": {
            "brand": "Dell",
            "model": "Precision 3680",
            "processor": "Intel i7-14700",
            "ram": 32
        }
    }
}
```

## Update Item

```http
PATCH /items/{item}
```

Example:

```json
{
    "name": "Dell Precision 3680 Workstation",
    "reorder_level": 2
}
```

Dynamic attributes can be updated independently through the attributes endpoint.

## Delete / Archive Item

```http
DELETE /items/{item}
```

Items use soft deletes, so deletion archives the record rather than permanently removing it.

## Restore Item

```http
POST /items/{item}/restore
```

## Activate Item

```http
POST /items/{item}/activate
```

## Deactivate Item

```http
POST /items/{item}/deactivate
```

---

# 9. Item Dynamic Attributes

Dynamic attribute values are stored separately from the `items` table.

For example:

```text
Item
 └── Dell Precision 3680
       │
       ├── brand = Dell
       ├── model = Precision 3680
       ├── processor = Intel i7-14700
       └── ram = 32
```

## Get Item Attributes

```http
GET /items/{item}/attributes
```

Example:

```http
GET /api/auth/inventory/items/100/attributes
```

Example response:

```json
{
    "data": {
        "brand": "Dell",
        "model": "Precision 3680",
        "processor": "Intel i7-14700",
        "ram": 32
    }
}
```

## Update Item Attributes

```http
PATCH /items/{item}/attributes
```

Example:

```json
{
    "attributes": {
        "brand": "HP",
        "model": "Z2 G9",
        "processor": "Intel i7",
        "ram": 64
    }
}
```

The backend validates these values against the item's `ItemType`.

For example, if `ram` is defined as:

```text
data_type = integer
```

then:

```json
{
    "ram": 32
}
```

is valid, while:

```json
{
    "ram": "thirty-two"
}
```

is rejected.

For a `select` attribute, the submitted value must exist in the attribute's active options.

---

# 10. Dynamic Attribute Architecture

The relationship between the entities is:

```text
ItemType
   │
   │
   ▼
ItemTypeAttribute
   │
   │
   ▼
AttributeDefinition
   │
   ├── AttributeOption
   │
   ▼
ItemAttributeValue
   │
   ▼
Item
```

More specifically:

```text
AttributeDefinition
    │
    │ reusable definition
    ▼
ItemTypeAttribute
    │
    │ assignment
    ▼
ItemType
    │
    │ determines available fields
    ▼
Item
    │
    │ stores actual values
    ▼
ItemAttributeValue
```

This means an attribute such as `brand` can be reused:

```text
Computer
 └── Brand

Printer
 └── Brand

Furniture
 └── Brand
```

without creating three separate attribute definitions.

---

# 11. Typical Workflow

## Step 1 — Create an Attribute

```http
POST /attributes
```

```json
{
    "code": "brand",
    "name": "Brand",
    "data_type": "select",
    "is_required": true
}
```

## Step 2 — Add Options

```http
POST /attributes/1/options
```

```json
{
    "value": "dell",
    "label": "Dell"
}
```

```http
POST /attributes/1/options
```

```json
{
    "value": "hp",
    "label": "HP"
}
```

## Step 3 — Assign Attribute to Item Type

```http
POST /item-types/1/attributes
```

```json
{
    "attribute_definition_id": 1,
    "is_required": true,
    "sort_order": 1
}
```

## Step 4 — Request Metadata

```http
GET /item-types/1/attributes/metadata
```

The frontend now knows that the Computer form needs a `brand` field and that it is a required `select`.

## Step 5 — Create the Item

```http
POST /items
```

```json
{
    "name": "Dell Precision 3680",
    "item_type_id": 1,
    "category_id": 2,
    "unit_of_measure_id": 1,
    "attributes": {
        "brand": "dell"
    }
}
```

The backend generates:

```text
ITM-000001
```

and persists the dynamic attribute value separately.

---

# 12. Endpoint Summary

| Method | Endpoint                                                | Purpose                   |
| ------ | ------------------------------------------------------- | ------------------------- |
| GET    | `/units`                                                | List units                |
| POST   | `/units`                                                | Create unit               |
| GET    | `/units/{unit}`                                         | Get unit                  |
| PATCH  | `/units/{unit}`                                         | Update unit               |
| POST   | `/units/{unit}/activate`                                | Activate unit             |
| POST   | `/units/{unit}/deactivate`                              | Deactivate unit           |
| GET    | `/categories`                                           | List categories           |
| POST   | `/categories`                                           | Create category           |
| GET    | `/categories/{category}`                                | Get category              |
| PATCH  | `/categories/{category}`                                | Update category           |
| DELETE | `/categories/{category}`                                | Delete category           |
| GET    | `/categories/tree`                                      | Get category tree         |
| POST   | `/categories/{category}/activate`                       | Activate category         |
| POST   | `/categories/{category}/deactivate`                     | Deactivate category       |
| GET    | `/item-types`                                           | List item types           |
| POST   | `/item-types`                                           | Create item type          |
| GET    | `/item-types/{itemType}`                                | Get item type             |
| PATCH  | `/item-types/{itemType}`                                | Update item type          |
| POST   | `/item-types/{itemType}/activate`                       | Activate item type        |
| POST   | `/item-types/{itemType}/deactivate`                     | Deactivate item type      |
| GET    | `/attributes`                                           | List attributes           |
| POST   | `/attributes`                                           | Create attribute          |
| GET    | `/attributes/{attribute}`                               | Get attribute             |
| PATCH  | `/attributes/{attribute}`                               | Update attribute          |
| POST   | `/attributes/{attribute}/activate`                      | Activate attribute        |
| POST   | `/attributes/{attribute}/deactivate`                    | Deactivate attribute      |
| GET    | `/attributes/{attribute}/options`                       | List options              |
| POST   | `/attributes/{attribute}/options`                       | Create option             |
| GET    | `/attribute-options/{option}`                           | Get option                |
| PATCH  | `/attribute-options/{option}`                           | Update option             |
| DELETE | `/attribute-options/{option}`                           | Delete option             |
| POST   | `/attribute-options/{option}/activate`                  | Activate option           |
| POST   | `/attribute-options/{option}/deactivate`                | Deactivate option         |
| GET    | `/item-types/{itemType}/attributes`                     | List assigned attributes  |
| POST   | `/item-types/{itemType}/attributes`                     | Assign attribute          |
| PATCH  | `/item-types/{itemType}/attributes/{itemTypeAttribute}` | Update assignment         |
| DELETE | `/item-types/{itemType}/attributes/{itemTypeAttribute}` | Remove assignment         |
| GET    | `/item-types/{itemType}/attributes/metadata`            | Get dynamic form metadata |
| GET    | `/items`                                                | List items                |
| POST   | `/items`                                                | Create item               |
| GET    | `/items/{item}`                                         | Get item                  |
| PATCH  | `/items/{item}`                                         | Update item               |
| DELETE | `/items/{item}`                                         | Archive item              |
| POST   | `/items/{item}/restore`                                 | Restore item              |
| POST   | `/items/{item}/activate`                                | Activate item             |
| POST   | `/items/{item}/deactivate`                              | Deactivate item           |
| GET    | `/items/{item}/attributes`                              | Get item attributes       |
| PATCH  | `/items/{item}/attributes`                              | Update item attributes    |

---

# 13. Recommended Frontend Flow

When building the Angular item form:

```text
Select Item Type
       │
       ▼
GET /item-types/{id}/attributes/metadata
       │
       ▼
Build dynamic form
       │
       ├── text
       ├── textarea
       ├── integer
       ├── decimal
       ├── boolean
       ├── date
       ├── datetime
       ├── select
       └── multiselect
       │
       ▼
POST /items
       │
       ▼
Item + dynamic attribute values
```

The frontend should **not hard-code the dynamic fields** for each item type. The metadata endpoint is the source of truth for which fields should be displayed and how they should behave.
