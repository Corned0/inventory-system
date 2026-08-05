# Inventory Management System — Backend Implementation Plan

## 1. Project Objective

Build a Laravel-based Inventory Management API capable of managing:

* Item master data
* Dynamic item attributes
* Item types and categories
* Warehouses and hierarchical storage locations
* Inventory quantities
* Serialized items
* Lot/batch-tracked items
* Receiving
* Put-away
* Inventory movements
* Item/component assemblies
* Asset instances
* Issuance
* Transfers
* Returns
* Stock counting
* Adjustments
* Disposal
* Suppliers
* Purchase orders
* Inventory audit history
* Configurable workflows
* Inventory reporting

The system should support both simple consumables and complex assets such as:

```text
Desktop PC
├── CPU
├── RAM × 2
├── SSD
├── GPU
└── Power Supply
```

---

# 2. Core Architecture

The system should distinguish between:

```text
Item Definition
        ↓
Inventory Stock
        ↓
Physical Instance
        ↓
Component / Assembly
        ↓
Inventory Transactions
```

### Item Definition

Defines what an item is.

Example:

```text
Dell Precision 3680
```

### Inventory Stock

Represents quantities of non-serialized items.

```text
100 units
```

### Physical Instance

Represents an individually tracked physical item.

```text
PC-000123
Serial Number: ABC123
```

### Component / Assembly

Represents relationships between physical items.

```text
PC-000123
├── RAM-001
├── RAM-002
├── SSD-001
└── GPU-001
```

### Inventory Transaction

Represents every stock movement.

```text
RECEIVE
ISSUE
TRANSFER
ADJUSTMENT
RETURN
DISPOSAL
```

The transaction ledger should be the authoritative history of inventory movement.

---

# 3. Recommended Implementation Order

```text
01. Foundation
02. Units of Measure
03. Categories
04. Item Types
05. Item Attribute Definitions
06. Items
07. Warehouses
08. Locations
09. Suppliers
10. Inventory Tracking
11. Inventory Ledger
12. Inventory Balances
13. Receiving
14. Put-Away
15. Serial / Lot Management
16. Asset Instances
17. BOM / Components
18. Assembly / Disassembly
19. Inventory Reservations
20. Issuance
21. Transfers
22. Returns
23. Stock Counting
24. Adjustments
25. Disposal
26. Purchase Orders
27. Approval Workflows
28. Dynamic Configuration
29. Notifications
30. Reporting
31. Audit Logs
32. Performance / Optimization
33. API Documentation
34. Testing
```

---

# 4. Phase 1 — Foundation

## Purpose

Establish common infrastructure before implementing inventory logic.

## Models

```text
User
Role
Permission
Department
Employee
```

If the existing application already has users/employees, reuse those models.

## Common Data Types

Use PHP enums for fixed domain values.

Example:

```php
enum ItemTrackingType: string
{
    case NONE = 'none';
    case LOT = 'lot';
    case SERIAL = 'serial';
}
```

Other enums:

```text
InventoryTransactionType
InventoryStatus
ReceivingStatus
IssueStatus
TransferStatus
AdjustmentType
LocationType
```

## Common API Response

Success:

```json
{
    "success": true,
    "message": "Operation completed successfully.",
    "data": {}
}
```

Paginated:

```json
{
    "success": true,
    "data": [],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 100,
        "last_page": 5
    }
}
```

Validation error:

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "name": [
            "The name field is required."
        ]
    }
}
```

---

# 5. Phase 2 — Units of Measure

## Model

```text
UnitOfMeasure
```

## Table

```text
units_of_measure

id
code
name
symbol
decimal_places
is_active
created_at
updated_at
```

Examples:

```text
PC   Piece
BOX  Box
REAM Ream
KG   Kilogram
L    Liter
SET  Set
```

## Functions

```text
list()
create()
show()
update()
activate()
deactivate()
```

## API

```text
GET    /api/inventory/units
POST   /api/inventory/units
GET    /api/inventory/units/{id}
PATCH  /api/inventory/units/{id}
POST   /api/inventory/units/{id}/activate
POST   /api/inventory/units/{id}/deactivate
```

## Return

```json
{
    "id": 1,
    "code": "PC",
    "name": "Piece",
    "symbol": "pc",
    "decimal_places": 0,
    "is_active": true
}
```

---

# 6. Phase 3 — Categories

## Model

```text
ItemCategory
```

## Table

```text
item_categories

id
parent_id
code
name
description
is_active
created_at
updated_at
```

Support nested categories.

Example:

```text
IT Equipment
├── Computers
├── Printers
└── Networking

Office Supplies
├── Paper
├── Writing Materials
└── Folders
```

## Functions

```text
list()
tree()
create()
show()
update()
delete()
activate()
deactivate()
```

## API

```text
GET    /api/inventory/categories
GET    /api/inventory/categories/tree
POST   /api/inventory/categories
GET    /api/inventory/categories/{id}
PATCH  /api/inventory/categories/{id}
DELETE /api/inventory/categories/{id}
```

---

# 7. Phase 4 — Item Types

Item types determine how an item behaves.

## Model

```text
ItemType
```

## Table

```text
item_types

id
code
name
description
tracking_type
is_asset
is_composite
is_active
created_at
updated_at
```

## Data Types

```text
tracking_type:

none
lot
serial
```

Example:

```json
{
    "code": "COMPUTER",
    "name": "Computer Equipment",
    "tracking_type": "serial",
    "is_asset": true,
    "is_composite": true
}
```

Another:

```json
{
    "code": "OFFICE_SUPPLY",
    "name": "Office Supply",
    "tracking_type": "none",
    "is_asset": false,
    "is_composite": false
}
```

## Functions

```text
list()
create()
show()
update()
activate()
deactivate()
```

---

# 8. Phase 5 — Dynamic Item Attributes

This is where configurable fields are implemented.

## Models

```text
AttributeDefinition
AttributeOption
ItemTypeAttribute
ItemAttributeValue
```

## Attribute Definition

```text
attribute_definitions

id
code
name
data_type
description
is_required
is_active
sort_order
validation_rules
default_value
created_at
updated_at
```

## Data Types

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

## Attribute Options

```text
attribute_options

id
attribute_definition_id
value
label
sort_order
is_active
```

## Item Type Attributes

```text
item_type_attributes

id
item_type_id
attribute_definition_id
is_required
sort_order
```

This determines which attributes are available to each item type.

## Example

Computer:

```text
Brand
Model
Processor
RAM
Storage
Operating System
```

Furniture:

```text
Brand
Material
Color
Dimensions
```

## API

```text
GET    /api/inventory/attributes
POST   /api/inventory/attributes
GET    /api/inventory/attributes/{id}
PATCH  /api/inventory/attributes/{id}

POST   /api/inventory/item-types/{id}/attributes
DELETE /api/inventory/item-types/{id}/attributes/{attributeId}
```

## Dynamic Form Metadata

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

---

# 9. Phase 6 — Items

## Model

```text
Item
```

## Table

```text
items

id
item_code
barcode
name
description
category_id
item_type_id
unit_of_measure_id
reorder_level
reorder_quantity
is_active
created_at
updated_at
deleted_at
```

Do not put dynamic attributes directly into this table.

## Functions

```text
list()
create()
show()
update()
archive()
restore()
activate()
deactivate()
getAttributes()
updateAttributes()
```

## Create Item Request

```json
{
    "name": "Dell Precision 3680",
    "item_type_id": 1,
    "category_id": 2,
    "unit_of_measure_id": 1,
    "barcode": "123456789",
    "reorder_level": 1,
    "attributes": {
        "brand": "Dell",
        "model": "Precision 3680",
        "processor": "Intel i7-14700",
        "ram": 32
    }
}
```

## Return

```json
{
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
```

---

# 10. Phase 7 — Warehouses

## Model

```text
Warehouse
```

## Table

```text
warehouses

id
code
name
description
address
is_active
created_at
updated_at
```

## API

```text
GET    /api/inventory/warehouses
POST   /api/inventory/warehouses
GET    /api/inventory/warehouses/{id}
PATCH  /api/inventory/warehouses/{id}
```

---

# 11. Phase 8 — Storage Locations

Locations should be hierarchical.

## Model

```text
Location
```

## Table

```text
locations

id
warehouse_id
parent_id
code
name
location_type
is_active
created_at
updated_at
```

## Location Types

```text
warehouse
building
room
rack
shelf
bin
```

Example:

```text
Main Warehouse
└── Building A
    └── Room 101
        └── Rack A
            └── Shelf 01
                └── Bin 01
```

## API

```text
GET    /api/inventory/locations
GET    /api/inventory/warehouses/{id}/locations/tree
POST   /api/inventory/locations
GET    /api/inventory/locations/{id}
PATCH  /api/inventory/locations/{id}
```

---

# 12. Phase 9 — Suppliers

## Model

```text
Supplier
```

## Table

```text
suppliers

id
supplier_code
name
contact_person
email
phone
address
tax_number
is_active
created_at
updated_at
```

## API

```text
GET    /api/inventory/suppliers
POST   /api/inventory/suppliers
GET    /api/inventory/suppliers/{id}
PATCH  /api/inventory/suppliers/{id}
```

---

# 13. Phase 10 — Inventory Tracking

Before receiving inventory, implement the tracking domain.

## Models

```text
InventoryLot
InventorySerial
```

## Lot

```text
inventory_lots

id
item_id
lot_number
manufactured_date
expiration_date
created_at
updated_at
```

## Serial

```text
inventory_serials

id
item_id
serial_number
status
created_at
updated_at
```

Serial status:

```text
available
reserved
issued
installed
repair
damaged
lost
disposed
```

---

# 14. Phase 11 — Inventory Ledger

This is the heart of the system.

## Model

```text
InventoryTransaction
```

## Table

```text
inventory_transactions

id
transaction_number
transaction_type
item_id
warehouse_id
location_id
lot_id
serial_id
quantity
unit_cost
reference_type
reference_id
transaction_date
performed_by
remarks
created_at
```

## Transaction Types

```text
receipt
put_away
issue
transfer_out
transfer_in
return
adjustment_in
adjustment_out
disposal
assembly
disassembly
stock_count
```

## Critical Rule

Do not allow arbitrary direct updates to inventory quantity.

Bad:

```text
inventory.quantity = 100;
```

Good:

```text
InventoryTransaction
        ↓
InventoryBalance
```

Every change must have a transaction.

---

# 15. Phase 12 — Inventory Balances

## Model

```text
InventoryBalance
```

## Table

```text
inventory_balances

id
item_id
warehouse_id
location_id
lot_id
quantity
reserved_quantity
available_quantity
created_at
updated_at
```

## Functions

```text
getStock()
getAvailableStock()
getStockByWarehouse()
getStockByLocation()
getStockByLot()
getStockCard()
```

## API

```text
GET /api/inventory/stock
GET /api/inventory/items/{item}/stock
GET /api/inventory/items/{item}/stock-card
GET /api/inventory/warehouses/{warehouse}/stock
GET /api/inventory/locations/{location}/stock
```

## Example

```json
{
    "item": {
        "id": 20,
        "code": "ITM-000020",
        "name": "A4 Bond Paper"
    },
    "stock": {
        "quantity": 500,
        "reserved_quantity": 50,
        "available_quantity": 450
    },
    "locations": [
        {
            "location_id": 10,
            "location": "Rack A / Shelf 01",
            "quantity": 300
        },
        {
            "location_id": 11,
            "location": "Rack A / Shelf 02",
            "quantity": 200
        }
    ]
}
```

---

# 16. Phase 13 — Receiving

Receiving is the first major inventory operation.

## Models

```text
Receiving
ReceivingItem
ReceivingItemLot
ReceivingItemSerial
```

## Receiving

```text
receivings

id
receiving_number
supplier_id
purchase_order_id
warehouse_id
received_date
status
received_by
remarks
created_at
updated_at
```

## Receiving Items

```text
receiving_items

id
receiving_id
item_id
ordered_quantity
received_quantity
accepted_quantity
rejected_quantity
unit_cost
```

## Status

```text
draft
received
under_inspection
accepted
partially_accepted
rejected
completed
cancelled
```

## API

```text
GET    /api/inventory/receivings
POST   /api/inventory/receivings
GET    /api/inventory/receivings/{id}
PATCH  /api/inventory/receivings/{id}

POST   /api/inventory/receivings/{id}/inspect
POST   /api/inventory/receivings/{id}/accept
POST   /api/inventory/receivings/{id}/reject
POST   /api/inventory/receivings/{id}/complete
```

---

# 17. Phase 14 — Put-Away

Accepted inventory needs to be placed into a storage location.

## Model

```text
PutAway
PutAwayItem
```

## Table

```text
put_aways

id
put_away_number
receiving_id
warehouse_id
status
performed_by
created_at
updated_at
```

```text
put_away_items

id
put_away_id
receiving_item_id
item_id
location_id
lot_id
serial_id
quantity
```

## API

```text
POST /api/inventory/receivings/{id}/put-away
GET  /api/inventory/put-aways/{id}
POST /api/inventory/put-aways/{id}/complete
```

Completing put-away creates inventory transactions.

---

# 18. Phase 15 — Asset Instances

For serialized assets, create physical instances.

## Models

```text
AssetInstance
```

## Table

```text
asset_instances

id
item_id
asset_number
serial_number
status
current_warehouse_id
current_location_id
acquired_at
acquisition_cost
warranty_start
warranty_end
created_at
updated_at
```

Example:

```json
{
    "asset_number": "AST-000123",
    "item": "Dell Precision 3680",
    "serial_number": "ABC123456",
    "status": "available",
    "location": {
        "warehouse": "Main Warehouse",
        "location": "Rack A / Shelf 01"
    }
}
```

---

# 19. Phase 16 — BOM / Component Definitions

Support composite items.

## Models

```text
ItemBom
ItemBomComponent
```

## Tables

```text
item_boms

id
item_id
name
version
is_active
created_at
updated_at
```

```text
item_bom_components

id
bom_id
component_item_id
quantity
is_required
```

## Example

```text
Dell Precision 3680

BOM v1
├── CPU × 1
├── RAM × 2
├── SSD × 1
├── GPU × 1
└── PSU × 1
```

## API

```text
GET    /api/inventory/items/{item}/boms
POST   /api/inventory/items/{item}/boms
GET    /api/inventory/boms/{id}
PATCH  /api/inventory/boms/{id}

POST   /api/inventory/boms/{id}/components
PATCH  /api/inventory/boms/{id}/components/{component}
DELETE /api/inventory/boms/{id}/components/{component}
```

---

# 20. Phase 17 — Actual Component Assembly

BOM defines the expected structure.

Actual component relationships represent physical reality.

## Model

```text
AssetComponent
```

## Table

```text
asset_components

id
parent_asset_id
component_asset_id
quantity
installed_at
removed_at
installation_location
status
installed_by
removed_by
created_at
updated_at
```

## Example

```text
PC-000123
├── RAM-000001
├── RAM-000002
├── SSD-000001
└── GPU-000001
```

## API

```text
GET  /api/inventory/assets/{asset}/components
POST /api/inventory/assets/{asset}/components
POST /api/inventory/assets/{asset}/components/{component}/remove
```

---

# 21. Phase 18 — Assembly / Disassembly

## Functions

```text
assemble()
disassemble()
replaceComponent()
getComponentHistory()
```

## Assembly Request

```json
{
    "parent_asset_id": 100,
    "components": [
        {
            "asset_id": 101
        },
        {
            "asset_id": 102
        },
        {
            "asset_id": 103
        }
    ]
}
```

## Result

```json
{
    "asset": {
        "asset_number": "AST-000100"
    },
    "components": [
        {
            "asset_number": "AST-000101",
            "status": "installed"
        },
        {
            "asset_number": "AST-000102",
            "status": "installed"
        }
    ]
}
```

---

# 22. Phase 19 — Reservations

Prevent two departments from requesting the same stock.

## Model

```text
InventoryReservation
```

## Table

```text
inventory_reservations

id
item_id
warehouse_id
location_id
lot_id
quantity
reserved_quantity
reference_type
reference_id
status
expires_at
created_by
created_at
```

## Status

```text
active
released
fulfilled
expired
cancelled
```

---

# 23. Phase 20 — Issuance

## Models

```text
IssueRequest
IssueRequestItem
Issuance
IssuanceItem
```

## Issue Request

```text
issue_requests

id
request_number
requester_id
department_id
purpose
requested_date
status
created_by
created_at
updated_at
```

## Status

```text
draft
pending_approval
approved
rejected
ready_for_release
released
cancelled
```

## API

```text
POST /api/inventory/issue-requests
GET  /api/inventory/issue-requests
GET  /api/inventory/issue-requests/{id}

POST /api/inventory/issue-requests/{id}/submit
POST /api/inventory/issue-requests/{id}/approve
POST /api/inventory/issue-requests/{id}/reject
POST /api/inventory/issue-requests/{id}/release
```

Releasing an item creates an inventory transaction.

---

# 24. Phase 21 — Transfers

## Models

```text
StockTransfer
StockTransferItem
```

## Table

```text
stock_transfers

id
transfer_number
source_warehouse_id
destination_warehouse_id
source_location_id
destination_location_id
requested_by
status
transfer_date
remarks
created_at
updated_at
```

## Status

```text
draft
pending_approval
approved
in_transit
completed
cancelled
```

## API

```text
POST /api/inventory/transfers
GET  /api/inventory/transfers
GET  /api/inventory/transfers/{id}

POST /api/inventory/transfers/{id}/submit
POST /api/inventory/transfers/{id}/approve
POST /api/inventory/transfers/{id}/dispatch
POST /api/inventory/transfers/{id}/receive
POST /api/inventory/transfers/{id}/cancel
```

Transfer creates:

```text
TRANSFER_OUT
TRANSFER_IN
```

---

# 25. Phase 22 — Returns

Support both:

```text
Employee → Warehouse
Supplier → Warehouse
Warehouse → Supplier
```

## Models

```text
InventoryReturn
InventoryReturnItem
```

## API

```text
POST /api/inventory/returns
GET  /api/inventory/returns
GET  /api/inventory/returns/{id}

POST /api/inventory/returns/{id}/inspect
POST /api/inventory/returns/{id}/accept
POST /api/inventory/returns/{id}/reject
POST /api/inventory/returns/{id}/complete
```

---

# 26. Phase 23 — Stock Counting

## Models

```text
StockCount
StockCountItem
```

## Table

```text
stock_counts

id
count_number
warehouse_id
location_id
count_date
status
counted_by
approved_by
created_at
updated_at
```

```text
stock_count_items

id
stock_count_id
item_id
lot_id
serial_id
system_quantity
counted_quantity
variance
remarks
```

## API

```text
POST /api/inventory/stock-counts
GET  /api/inventory/stock-counts
GET  /api/inventory/stock-counts/{id}

POST /api/inventory/stock-counts/{id}/start
POST /api/inventory/stock-counts/{id}/submit
POST /api/inventory/stock-counts/{id}/approve
POST /api/inventory/stock-counts/{id}/complete
```

Never directly overwrite stock.

Variance creates an adjustment transaction.

---

# 27. Phase 24 — Inventory Adjustments

## Model

```text
InventoryAdjustment
InventoryAdjustmentItem
```

## Reasons

```text
damaged
lost
found
count_variance
data_correction
opening_balance
other
```

## API

```text
POST /api/inventory/adjustments
GET  /api/inventory/adjustments
GET  /api/inventory/adjustments/{id}

POST /api/inventory/adjustments/{id}/submit
POST /api/inventory/adjustments/{id}/approve
POST /api/inventory/adjustments/{id}/complete
```

Adjustment completion creates:

```text
ADJUSTMENT_IN
```

or:

```text
ADJUSTMENT_OUT
```

---

# 28. Phase 25 — Disposal

## Models

```text
Disposal
DisposalItem
```

## Reasons

```text
damaged
obsolete
beyond_repair
lost
expired
unserviceable
```

## API

```text
POST /api/inventory/disposals
GET  /api/inventory/disposals
GET  /api/inventory/disposals/{id}

POST /api/inventory/disposals/{id}/submit
POST /api/inventory/disposals/{id}/approve
POST /api/inventory/disposals/{id}/complete
```

Completing disposal creates:

```text
DISPOSAL
```

transaction.

---

# 29. Phase 26 — Purchase Orders

If procurement is part of the system, implement this after receiving.

## Models

```text
PurchaseOrder
PurchaseOrderItem
```

## Purchase Order

```text
purchase_orders

id
po_number
supplier_id
order_date
expected_date
status
subtotal
tax
total
created_by
approved_by
created_at
updated_at
```

## Items

```text
purchase_order_items

id
purchase_order_id
item_id
quantity
unit_cost
received_quantity
remaining_quantity
```

## Status

```text
draft
pending_approval
approved
partially_received
received
cancelled
```

---

# 30. Phase 27 — Approval Workflows

Don't hard-code every approval process if multiple departments will use the system.

Potential models:

```text
ApprovalWorkflow
ApprovalStep
ApprovalRequest
ApprovalAction
```

Example:

```text
Issue Request
    ↓
Supervisor
    ↓
Department Head
    ↓
Warehouse
```

Later:

```text
Purchase Order
    ↓
Division Chief
    ↓
Budget
    ↓
Procurement
```

---

# 31. Phase 28 — Dynamic Configuration

Once the core inventory engine works, implement configurable behavior.

Configuration areas:

```text
Item Fields
Item Types
Categories
Units
Locations
Transaction Reasons
Statuses
Numbering
Approval Rules
```

Do not make core inventory behavior dynamically configurable unless there is a strong requirement.

For example, don't allow administrators to arbitrarily create a new inventory transaction type that the backend does not understand.

---

# 32. Phase 29 — Number Generation

Create a centralized document-numbering service.

## Model

```text
DocumentSequence
```

## Table

```text
document_sequences

id
document_type
prefix
year
current_number
padding
```

Examples:

```text
ITM-000001
RCV-2026-000001
ISS-2026-000001
TRF-2026-000001
AST-2026-000001
ADJ-2026-000001
```

Use database locking to prevent duplicate numbers.

---

# 33. Phase 30 — Audit Logging

Every important domain operation should be auditable.

## Model

```text
AuditLog
```

## Table

```text
audit_logs

id
user_id
action
entity_type
entity_id
old_values
new_values
ip_address
user_agent
created_at
```

Examples:

```text
ITEM_CREATED
ITEM_UPDATED

RECEIVING_CREATED
RECEIVING_COMPLETED

STOCK_ISSUED
STOCK_TRANSFERRED
STOCK_ADJUSTED

ASSET_ASSEMBLED
COMPONENT_REMOVED
```

---

# 34. Phase 31 — Reporting APIs

Reports should query the ledger and balance tables rather than maintaining separate manually updated report tables.

## Inventory Reports

```text
GET /api/inventory/reports/stock-summary
GET /api/inventory/reports/stock-card
GET /api/inventory/reports/movements
GET /api/inventory/reports/low-stock
GET /api/inventory/reports/inventory-aging
GET /api/inventory/reports/inventory-valuation
```

## Receiving

```text
GET /api/inventory/reports/receiving
```

## Issuance

```text
GET /api/inventory/reports/issuance
```

## Transfers

```text
GET /api/inventory/reports/transfers
```

## Assets

```text
GET /api/inventory/reports/assets
GET /api/inventory/reports/assets/{id}/history
GET /api/inventory/reports/assets/{id}/components
```

---

# 35. Standard Query Parameters

All list endpoints should support a consistent query format.

```text
?page=1
&per_page=20
&search=dell
&sort=name
&direction=asc
&status=active
&category_id=1
&item_type_id=2
```

Example:

```text
GET /api/inventory/items
    ?search=dell
    &item_type_id=1
    &page=1
    &per_page=20
```

---

# 36. Standard Laravel Layering

Avoid putting domain logic inside controllers.

Recommended:

```text
app/
├── Domain/
│   └── Inventory/
│       ├── Actions/
│       ├── DTOs/
│       ├── Enums/
│       ├── Exceptions/
│       ├── Models/
│       ├── Services/
│       └── ValueObjects/
│
├── Http/
│   ├── Controllers/
│   │   └── Inventory/
│   ├── Requests/
│   │   └── Inventory/
│   └── Resources/
│       └── Inventory/
│
└── ...
```

---

# 37. Recommended Actions

Major state-changing operations should be represented as explicit actions.

```text
CreateItemAction
UpdateItemAction

ReceiveInventoryAction
InspectReceivingAction
AcceptReceivingAction
PutAwayInventoryAction

CreateAssetInstanceAction

AssembleAssetAction
DisassembleAssetAction
ReplaceAssetComponentAction

ReserveInventoryAction
ReleaseReservationAction

IssueInventoryAction
TransferInventoryAction

ReturnInventoryAction
AdjustInventoryAction
CompleteStockCountAction
DisposeInventoryAction
```

This makes business operations explicit and testable.

---

# 38. Database Transaction Requirements

Operations that modify inventory must use database transactions.

Example:

```text
DB::transaction()

    validate stock

    create domain document

    create inventory transaction

    update inventory balance

    update serial / lot status

    create audit log

commit
```

Never perform only:

```text
inventory_balance.quantity -= 10;
```

without recording why the quantity changed.

---

# 39. Inventory Invariants

These rules should be enforced by the domain layer.

### Rule 1

Stock cannot become negative unless negative inventory is explicitly supported.

### Rule 2

Serialized items have a maximum quantity of one per serial number.

### Rule 3

A serial number cannot exist twice for the same item.

### Rule 4

An issued serial cannot simultaneously be available.

### Rule 5

A component cannot be installed into two parent assets simultaneously.

### Rule 6

A disposed asset cannot be issued.

### Rule 7

A cancelled receiving cannot create stock.

### Rule 8

A completed transaction cannot be edited.

### Rule 9

Completed inventory movements should be reversed with compensating transactions rather than modified.

### Rule 10

Every stock quantity change must have an inventory transaction.

---

# 40. API Resource Structure

Use Laravel API Resources.

Example:

```text
ItemResource
WarehouseResource
LocationResource
SupplierResource
ReceivingResource
InventoryBalanceResource
AssetResource
TransferResource
IssueRequestResource
StockCountResource
```

Do not expose raw Eloquent models directly.

---

# 41. Example Item API

```http
GET /api/inventory/items/100
```

Response:

```json
{
    "success": true,
    "data": {
        "id": 100,
        "item_code": "ITM-000100",
        "name": "Dell Precision 3680",
        "description": "Desktop workstation",
        "category": {
            "id": 2,
            "code": "IT",
            "name": "IT Equipment"
        },
        "item_type": {
            "id": 1,
            "code": "COMPUTER",
            "name": "Computer Equipment",
            "tracking_type": "serial",
            "is_asset": true,
            "is_composite": true
        },
        "unit": {
            "id": 1,
            "code": "PC",
            "name": "Piece"
        },
        "attributes": {
            "brand": "Dell",
            "model": "Precision 3680",
            "processor": "Intel i7-14700",
            "ram": 32
        },
        "active": true
    }
}
```

---

# 42. Example Asset API

```http
GET /api/inventory/assets/100
```

Response:

```json
{
    "success": true,
    "data": {
        "id": 100,
        "asset_number": "AST-2026-000100",
        "serial_number": "ABC123456",
        "item": {
            "id": 20,
            "code": "ITM-000020",
            "name": "Dell Precision 3680"
        },
        "status": "assigned",
        "location": {
            "warehouse": "Main Warehouse",
            "location": "Room 101"
        },
        "components": [
            {
                "asset_number": "AST-2026-000101",
                "item": {
                    "name": "32GB DDR5 RAM"
                },
                "status": "installed"
            },
            {
                "asset_number": "AST-2026-000102",
                "item": {
                    "name": "1TB NVMe SSD"
                },
                "status": "installed"
            },
            {
                "asset_number": "AST-2026-000103",
                "item": {
                    "name": "RTX 4060"
                },
                "status": "installed"
            }
        ]
    }
}
```

---

# 43. Example Stock Card API

```http
GET /api/inventory/items/20/stock-card
```

Response:

```json
{
    "success": true,
    "data": {
        "item": {
            "id": 20,
            "code": "ITM-000020",
            "name": "A4 Bond Paper"
        },
        "opening_balance": 100,
        "closing_balance": 150,
        "movements": [
            {
                "date": "2026-08-01",
                "transaction_number": "RCV-2026-000001",
                "type": "receipt",
                "quantity_in": 100,
                "quantity_out": 0,
                "balance": 200
            },
            {
                "date": "2026-08-03",
                "transaction_number": "ISS-2026-000001",
                "type": "issue",
                "quantity_in": 0,
                "quantity_out": 50,
                "balance": 150
            }
        ]
    }
}
```

---

# 44. Recommended API Prefix

Use:

```text
/api/inventory
```

Structure:

```text
/api/inventory
│
├── items
├── item-types
├── categories
├── attributes
├── units
│
├── warehouses
├── locations
│
├── suppliers
│
├── stock
├── transactions
│
├── receivings
├── put-aways
│
├── assets
├── boms
│
├── issue-requests
├── issuances
│
├── transfers
├── returns
├── stock-counts
├── adjustments
├── disposals
│
└── reports
```

---

# 45. Recommended Laravel Route Organization

Use separate route files or route groups.

```php
Route::prefix('inventory')
    ->middleware('auth:sanctum')
    ->group(function () {
        // Master data
        require __DIR__ . '/inventory/master-data.php';

        // Items
        require __DIR__ . '/inventory/items.php';

        // Stock
        require __DIR__ . '/inventory/stock.php';

        // Receiving
        require __DIR__ . '/inventory/receiving.php';

        // Assets
        require __DIR__ . '/inventory/assets.php';

        // Issuance
        require __DIR__ . '/inventory/issuance.php';

        // Transfers
        require __DIR__ . '/inventory/transfers.php';

        // Adjustments
        require __DIR__ . '/inventory/adjustments.php';

        // Reports
        require __DIR__ . '/inventory/reports.php';
    });
```

---

# 46. Testing Strategy

Inventory requires significantly more testing than normal CRUD.

## Unit Tests

Test:

```text
Stock calculation
Quantity validation
Serial validation
Lot validation
Component validation
Reservation calculations
Transaction creation
```

## Feature Tests

Test:

```text
Create receiving
Complete receiving
Put-away
Issue stock
Transfer stock
Return stock
Adjust stock
Dispose stock
Assemble asset
Remove component
Replace component
```

## Important Scenario

Test:

```text
Receive 10
Issue 3
Transfer 2
Return 1
Adjust -1
```

Expected:

```text
10 - 3 - 2 + 1 - 1 = 5
```

The ledger and balance must both produce:

```text
5
```

---

# 47. Performance Considerations

For large inventory datasets:

### Index

```text
items.item_code
items.barcode

inventory_transactions.item_id
inventory_transactions.transaction_date
inventory_transactions.transaction_type

inventory_balances.item_id
inventory_balances.location_id
inventory_balances.warehouse_id

asset_instances.asset_number
asset_instances.serial_number

locations.warehouse_id
locations.parent_id
```

Use composite indexes where query patterns justify them.

Example:

```text
(item_id, warehouse_id, location_id)
```

---

# 48. Avoid These Designs

## Do not store stock only on `items`

Bad:

```text
items
-----
quantity
```

because inventory exists in multiple locations.

---

## Do not overwrite inventory history

Bad:

```text
quantity = 50
```

without a transaction.

---

## Do not make everything dynamic

Core fields should remain relational.

Dynamic fields are for metadata/specifications.

---

## Do not use JSON for the entire inventory model

JSON is appropriate for configurable attribute values or metadata, but not for:

```text
item
warehouse
location
transaction
quantity
serial
lot
```

These should remain relational.

---

## Do not delete completed transactions

Transactions are historical records.

Use reversal/compensating transactions.

---

# 49. Final Domain Model

The resulting system should roughly look like:

```text
                         ┌──────────────┐
                         │ ItemCategory │
                         └──────┬───────┘
                                │
                         ┌──────▼──────┐
                         │    Item     │
                         └──────┬──────┘
                                │
              ┌─────────────────┼─────────────────┐
              │                 │                 │
              ▼                 ▼                 ▼
       Attributes          Inventory          Asset Instances
                                │                 │
                                │                 ▼
                                │          Asset Components
                                │                 │
                                ▼                 │
                       Inventory Balance         │
                                │                 │
                                └────────┬────────┘
                                         ▼
                              Inventory Transactions
                                         │
             ┌──────────────┬────────────┼────────────┬─────────────┐
             ▼              ▼            ▼            ▼             ▼
         Receiving       Issuance     Transfer     Return       Adjustment
             │
             ▼
          Put-away
```

---

# 50. Recommended Build Sequence

The actual Laravel implementation should be performed in this exact order:

```text
PHASE 01
Foundation
        ↓
PHASE 02
Units / Categories / Item Types
        ↓
PHASE 03
Dynamic Attributes
        ↓
PHASE 04
Items
        ↓
PHASE 05
Warehouses / Locations
        ↓
PHASE 06
Suppliers
        ↓
PHASE 07
Lots / Serials
        ↓
PHASE 08
Inventory Transactions
        ↓
PHASE 09
Inventory Balances
        ↓
PHASE 10
Receiving
        ↓
PHASE 11
Put-away
        ↓
PHASE 12
Asset Instances
        ↓
PHASE 13
BOM / Components
        ↓
PHASE 14
Assembly / Disassembly
        ↓
PHASE 15
Reservations
        ↓
PHASE 16
Issuance
        ↓
PHASE 17
Transfers
        ↓
PHASE 18
Returns
        ↓
PHASE 19
Stock Counting
        ↓
PHASE 20
Adjustments
        ↓
PHASE 21
Disposal
        ↓
PHASE 22
Purchase Orders
        ↓
PHASE 23
Approval Workflows
        ↓
PHASE 24
Audit Logs
        ↓
PHASE 25
Reports
        ↓
PHASE 26
Performance Optimization
        ↓
PHASE 27
API Documentation
        ↓
PHASE 28
Full Automated Test Suite
```

## First Development Milestone

Do **not** start with receiving.

The first usable backend milestone should be:

```text
Categories
    ↓
Item Types
    ↓
Units
    ↓
Items
    ↓
Warehouses
    ↓
Locations
    ↓
Inventory Transactions
    ↓
Inventory Balances
```

Then implement a complete vertical slice:

```text
Create Item
    ↓
Receive Item
    ↓
Put Away
    ↓
Check Stock
    ↓
Issue Item
    ↓
Check Stock Again
```

Once this works correctly, use the same transaction engine for transfers, returns, adjustments, disposal, and stock counting.

That approach prevents the most common inventory-system problem: building dozens of CRUD modules first and discovering later that the underlying stock model cannot reliably represent real-world inventory.
