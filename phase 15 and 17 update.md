# Inventory System — Phase 15 & Phase 17 Completion Plan

## Objective

Complete **Phase 15 — Serial/Lot Management** and **Phase 17 — BOM / Components** in the existing Laravel inventory API.

Do **not** start Phase 18 (Assembly / Disassembly) until both Phase 15 and Phase 17 are fully implemented, tested, and passing.

The implementation must work with the repository's existing architecture and conventions. Do not rewrite working modules unnecessarily.

---

# General Implementation Rules

Before modifying anything:

1. Inspect the existing repository structure.
2. Inspect the existing models, migrations, enums, requests, resources, controllers, services, routes, factories, seeders, and Pest tests related to:

   * Items
   * Item Types
   * Inventory Transactions
   * Inventory Balances
   * Receiving
   * Put-Away
   * Lots
   * Serials
   * Asset Instances
   * BOMs
   * BOM Components
3. Reuse existing patterns and naming conventions.
4. Do not create duplicate services, models, enums, or validation rules if equivalent implementations already exist.
5. Do not remove existing functionality unless it is demonstrably incorrect and the replacement preserves backward compatibility where possible.
6. Use database transactions for all inventory state-changing operations.
7. Inventory ledger records must remain immutable after completion.
8. Do not use floating-point arithmetic for inventory quantities or monetary values. Preserve decimal precision from the database.
9. Use PHP backed enums where the existing project already uses enums.
10. Use Form Requests for HTTP validation, but also enforce domain invariants inside services/actions because inventory operations may be invoked internally.
11. Use `lockForUpdate()` where concurrent inventory/serial/lot modifications can cause race conditions.
12. Do not use JSON fields for core inventory state.
13. Add Pest tests for every new business rule.
14. Run the complete relevant test suite after implementation.
15. Do not proceed to Phase 18 until all Phase 15 and Phase 17 acceptance criteria pass.

---

# PHASE 15 — SERIAL / LOT MANAGEMENT

## Phase 15 Goal

Make lot-tracked and serial-tracked inventory enforce the item's configured tracking policy consistently throughout the inventory lifecycle.

The system already contains basic:

* `inventory_lots`
* `inventory_serials`
* `ItemType.tracking_type`
* `lot_id`
* `serial_id`
* inventory transactions

The goal is to complete and harden this functionality rather than replace it.

---

# 15.1 Inspect Tracking Configuration

Inspect the existing `ItemType` implementation.

The supported tracking modes are:

```text
none
lot
serial
```

Confirm how `tracking_type` is represented.

Do not introduce another tracking enum if an existing enum already exists.

The rules must be:

### tracking_type = none

The transaction must NOT contain:

```text
lot_id
serial_id
```

Reject either value.

### tracking_type = lot

The transaction must:

```text
lot_id = required
serial_id = prohibited
```

The lot must belong to the same item as the transaction.

### tracking_type = serial

The transaction must:

```text
serial_id = required
lot_id = prohibited
```

The serial must belong to the same item as the transaction.

Serialized transactions normally represent exactly one physical serialized unit.

Therefore:

```text
quantity = 1
```

must be enforced for serialized inventory transactions unless the existing architecture explicitly supports another safe model.

Do not silently allow quantity > 1 for one serial number.

---

# 15.2 Lot Validation

Review:

```text
InventoryLot
StoreInventoryLotRequest
UpdateInventoryLotRequest
InventoryLotController
InventoryLotResource
```

Implement/verify the following rules.

## Lot number

A lot number must be unique per item.

The database already has:

```text
unique(item_id, lot_number)
```

Keep this constraint.

HTTP validation should also provide a useful validation error.

## Lot dates

If both dates exist:

```text
expiration_date >= manufactured_date
```

must be enforced.

A lot with:

```text
manufactured_date = 2026-01-10
expiration_date = 2025-01-10
```

must be rejected.

## Item ownership

A lot belongs permanently to its item.

Do not allow an existing lot to be changed from:

```text
item A
```

to:

```text
item B
```

if that would break existing inventory transactions.

Prefer making `item_id` immutable after creation.

If the current update endpoint permits changing `item_id`, change the validation/service behavior accordingly.

## Lot deletion

Do not allow deleting a lot that is referenced by inventory transactions or balances.

Follow the existing database foreign-key strategy.

Do not introduce cascading deletion that can destroy inventory history.

---

# 15.3 Serial Validation

Review:

```text
InventorySerial
StoreInventorySerialRequest
UpdateInventorySerialRequest
InventorySerialController
InventorySerialResource
```

Implement/verify:

## Serial number uniqueness

Serial numbers must be unique per item.

Keep the database constraint:

```text
unique(item_id, serial_number)
```

Do not make serial numbers globally unique unless the existing domain explicitly requires that.

## Item ownership

A serial belongs permanently to its item.

Do not allow:

```text
item_id
```

to be changed after the serial is referenced by inventory transactions.

Prefer making `item_id` immutable.

## Status

Serial status must not be arbitrarily changed through the generic serial update endpoint.

For example, do not allow an API user to manually change:

```text
available
issued
disposed
```

without an inventory operation.

Serial lifecycle changes must happen through inventory domain operations.

If `UpdateInventorySerialRequest` already prohibits status updates, preserve that behavior.

---

# 15.4 Serial Physical Location

Determine whether the existing `inventory_serials` model already tracks physical location.

If it does not, add:

```text
current_warehouse_id nullable
current_location_id nullable
```

to `inventory_serials`.

Use foreign keys referencing:

```text
warehouses
locations
```

with restrictive delete behavior consistent with the existing inventory schema.

Add appropriate indexes.

Before adding duplicated location state, inspect `asset_instances`.

If an inventory serial is also an asset serial and the architecture already treats `asset_instances.current_warehouse_id/current_location_id` as the authoritative physical location, do not create conflicting sources of truth.

The final implementation must have one clear authority for the serial's physical location.

Document the chosen authority in code/comments where appropriate.

---

# 15.5 Location / Warehouse Integrity

Every inventory transaction has:

```text
warehouse_id
location_id
```

where location may be nullable.

If `location_id` is provided, verify:

```text
locations.warehouse_id == transaction.warehouse_id
```

Reject:

```text
warehouse A
location belonging to warehouse B
```

This validation must exist both at the HTTP boundary and in the domain/service layer where necessary.

---

# 15.6 Lot / Serial Transaction Consistency

For every inventory transaction:

## Lot

If `lot_id` exists:

```text
inventory_lots.item_id == transaction.item_id
```

must be true.

Otherwise reject the transaction.

## Serial

If `serial_id` exists:

```text
inventory_serials.item_id == transaction.item_id
```

must be true.

Otherwise reject the transaction.

Do not rely solely on foreign keys because foreign keys cannot enforce this cross-record business relationship.

---

# 15.7 Tracking Policy Enforcement Service

Create or extend a domain service/action responsible for validating inventory tracking requirements.

Use the existing architecture if a suitable service already exists.

Possible responsibility:

```text
InventoryTrackingService
```

or an equivalent existing service.

It should validate:

```text
item tracking type
lot_id
serial_id
quantity
item ownership
```

Example rules:

```text
NONE:
    lot_id = null
    serial_id = null

LOT:
    lot_id != null
    serial_id = null
    lot.item_id == item.id

SERIAL:
    serial_id != null
    lot_id = null
    serial.item_id == item.id
    quantity == 1
```

This logic must be reusable by:

* receiving
* put-away
* inventory transactions
* returns
* transfers
* issues
* adjustments
* disposal
* future assembly/disassembly

Do not implement slightly different tracking validation in every controller.

---

# 15.8 Serialized Inventory State Transitions

Inspect:

```text
InventoryBalanceService
InventoryTransactionController
Receiving services
PutAway services
Transfer logic
Issue logic
Return logic
Adjustment logic
Disposal logic
```

Integrate serial state changes into the existing inventory transaction lifecycle.

The operation must be atomic.

For example:

```text
BEGIN TRANSACTION

lock serial

validate serial state

create inventory ledger transaction

update inventory balance

update serial status/location

COMMIT
```

If anything fails:

```text
ROLLBACK
```

---

# 15.9 Serial Lifecycle Rules

Use the existing `InventorySerialStatus` enum if present.

Do not create a second status system.

Define and enforce appropriate transitions based on the existing statuses.

At minimum, verify the following scenarios.

## Receiving

For a new serialized item:

```text
serial becomes available
serial gets warehouse/location
inventory quantity increases
transaction is recorded
```

## Put-away

If put-away changes physical location:

```text
serial.current_location_id
```

must move to the destination.

## Transfer

For serialized inventory:

```text
source serial location
    ->
destination serial location
```

must occur atomically.

Do not permit a serial to exist simultaneously in two physical locations.

## Issue

When issued:

```text
serial status changes appropriately
```

and inventory decreases.

The exact final status must follow the existing enum/domain terminology.

## Return

When a serialized item is returned:

```text
serial status/location
inventory balance
ledger transaction
```

must remain consistent.

## Disposal

Disposed serials must no longer be available for normal inventory operations.

---

# 15.10 Serialized Inventory Concurrency

Use row locking for serial operations:

```php
InventorySerial::query()
    ->whereKey($serialId)
    ->lockForUpdate()
    ->firstOrFail();
```

Do not allow two concurrent transactions to issue or transfer the same serial.

Add a regression test demonstrating that an unavailable/issued/disposed serial cannot be issued again.

---

# 15.11 Inventory Balance Interaction

Inspect the existing `inventory_balances` schema.

It currently uses the natural key:

```text
item_id
warehouse_id
location_id
lot_id
```

and does not contain `serial_id`.

Do NOT blindly add `serial_id`.

Determine whether the intended design is:

```text
serial state = individual physical identity
inventory balance = aggregate quantity
```

If that is the architecture, keep the balance aggregate and ensure:

```text
serialized inventory quantity
=
number of available serialized units at the location
```

where appropriate.

The serial record remains the source of truth for the identity/location of each serialized unit.

Add tests ensuring inventory balances cannot diverge from serial state through normal operations.

If the current architecture fundamentally requires serial-specific balance rows, document the reason and implement the necessary migration instead of maintaining two contradictory models.

---

# 15.12 Inventory Transaction Request

Update:

```text
StoreInventoryTransactionRequest
```

so it validates tracking requirements.

However, do NOT put all business logic exclusively in the request.

The service/action must also validate tracking because internal code may bypass HTTP validation.

Also validate:

```text
location belongs to warehouse
lot belongs to item
serial belongs to item
```

Use appropriate Laravel validation mechanisms and domain exceptions consistent with the project.

---

# 15.13 Inventory Transaction Controller

Review:

```text
InventoryTransactionController
```

The controller should remain thin.

It should delegate inventory state changes to the domain/service layer.

Avoid putting serial lifecycle logic directly in the controller.

If the current generic transaction endpoint is retained, make sure it cannot bypass tracking rules.

---

# 15.14 API Resources

Update resources so clients can see relevant lot/serial state.

Serial resource should expose appropriate fields such as:

```text
id
item_id
serial_number
status
current_warehouse_id
current_location_id
created_at
updated_at
```

Lot resource should expose:

```text
id
item_id
lot_number
manufactured_date
expiration_date
created_at
updated_at
```

Follow the repository's existing API response/resource conventions.

Do not expose unnecessary internal fields.

---

# 15.15 Phase 15 Tests

Add/complete Pest tests.

At minimum:

## Lot tests

* create lot
* duplicate lot number for same item fails
* same lot number for different items succeeds
* expiration before manufacture fails
* valid manufacture/expiration dates succeed
* item_id cannot be changed incorrectly
* referenced lot cannot be deleted
* lot resource returns expected fields

## Serial tests

* create serial
* duplicate serial for same item fails
* same serial number for different items succeeds
* item_id cannot be changed incorrectly
* direct status manipulation is rejected
* serial resource returns expected fields
* serial location is maintained correctly

## Tracking tests

### None

```text
lot_id provided -> fail
serial_id provided -> fail
```

### Lot

```text
missing lot_id -> fail
serial_id provided -> fail
wrong item's lot -> fail
correct lot -> succeed
```

### Serial

```text
missing serial_id -> fail
lot_id provided -> fail
wrong item's serial -> fail
quantity != 1 -> fail
correct serial -> succeed
```

## Location tests

```text
location belongs to warehouse -> succeed
location belongs to another warehouse -> fail
```

## Lifecycle tests

* receiving serialized item updates serial
* receiving lot-tracked item records lot
* put-away moves serial
* transfer moves serial
* issue updates serial state
* return restores appropriate state
* disposal prevents future use
* same serial cannot be issued twice
* transaction rollback restores serial state
* transaction rollback restores inventory balance
* concurrent serial operations cannot double-issue a serial

---

# PHASE 15 ACCEPTANCE CRITERIA

Phase 15 is complete only when:

* [ ] Lot uniqueness is enforced.
* [ ] Lot date rules are enforced.
* [ ] Lot item ownership is enforced.
* [ ] Lot lifecycle is safe.
* [ ] Serial uniqueness is enforced.
* [ ] Serial item ownership is enforced.
* [ ] Serial status cannot be arbitrarily modified.
* [ ] Serial physical location has one authoritative source of truth.
* [ ] Location/warehouse consistency is enforced.
* [ ] Item tracking policy is enforced.
* [ ] Lot/serial IDs cannot be mixed with incompatible tracking types.
* [ ] Serialized quantity is restricted to one unit where appropriate.
* [ ] Receiving integrates tracking.
* [ ] Put-away integrates tracking.
* [ ] Transfers integrate tracking.
* [ ] Issues integrate tracking.
* [ ] Returns integrate tracking.
* [ ] Disposal integrates tracking.
* [ ] Serial operations are protected against concurrent updates.
* [ ] Inventory balances remain consistent.
* [ ] API resources expose appropriate tracking information.
* [ ] Pest tests cover all critical rules.
* [ ] Existing inventory tests still pass.

Only after all of these pass should Phase 15 be marked complete.

---

# PHASE 17 — BOM / COMPONENTS

## Phase 17 Goal

Complete the Bill of Materials system so composite items can have controlled, versioned BOM definitions and validated component structures.

Do not implement actual assembly/disassembly yet.

Phase 17 should prepare the data/domain layer required by Phase 18.

---

# 17.1 Inspect Existing BOM Implementation

Inspect:

```text
ItemBom
ItemBomComponent
ItemBomService
ItemBomController
ItemBomResource
ItemBomComponentResource
StoreItemBomRequest
UpdateItemBomRequest
StoreItemBomComponentRequest
UpdateItemBomComponentRequest
migrations
routes
tests
```

Also inspect:

```text
Item.is_composite
ItemBom relationships
ItemBomComponent relationships
```

Reuse the existing implementation instead of replacing it.

---

# 17.2 BOM Parent Validation

A BOM must belong to a valid item.

If the project has:

```text
items.is_composite
```

then determine whether only composite items may have BOMs.

If that is the existing domain intention, enforce:

```text
item.is_composite == true
```

when creating a BOM.

Do not silently create BOMs for ordinary non-composite items.

---

# 17.3 Active BOM Rule

An item may have only one active BOM.

The existing service already attempts to enforce this.

Keep the business rule:

```text
one active BOM per item
```

Enforce it safely against concurrent requests.

Use an appropriate database constraint if practical for the database in use.

Because the project uses PostgreSQL, consider a partial unique index such as:

```sql
CREATE UNIQUE INDEX ...
ON item_boms(item_id)
WHERE is_active = true;
```

Only introduce this if compatible with the existing schema and migrations.

The service-level validation should remain useful for friendly errors.

---

# 17.4 BOM Version

BOMs are versioned.

Inspect the existing `version` field and current validation.

Define consistent rules for:

```text
item_id
name
version
is_active
```

At minimum:

* version must be present
* version must be valid according to existing conventions
* duplicate versions for the same item should be handled intentionally
* active BOM uniqueness must remain enforced

Do not arbitrarily redesign the versioning format.

If the existing system expects versions such as:

```text
1
2
3
```

preserve that convention.

---

# 17.5 BOM Immutability / Historical Integrity

A completed assembly in Phase 18 must be reproducible against the BOM version used.

Therefore avoid allowing active/historical BOM definitions to be modified in a way that changes historical meaning.

Determine the least disruptive approach compatible with the existing project.

Possible approach:

```text
Draft BOM:
    editable

Active BOM:
    limited modifications

Historical BOM:
    immutable
```

Do not implement an elaborate approval/versioning framework unless the existing project already supports it.

The objective is to prevent historical inventory operations from silently changing meaning.

---

# 17.6 Component Validation

Every BOM component must:

```text
component_item_id
quantity
is_required
```

with valid values.

Enforce:

```text
component_item_id exists
quantity > 0
```

Do not allow:

```text
quantity = 0
quantity < 0
```

Use the appropriate decimal precision.

Do not convert quantities to floating point.

---

# 17.7 Parent Cannot Be Its Own Component

Reject:

```text
BOM item = component item
```

Example:

```text
Desktop PC
    -> Desktop PC
```

must fail.

The existing service already checks direct self-reference.

Keep that validation.

---

# 17.8 Detect Indirect Circular BOMs

The current implementation only detects direct self-reference.

Implement recursive circular-reference detection.

Example:

```text
A
 -> B

B
 -> C

C
 -> A
```

must be rejected.

Also reject:

```text
A
 -> B
B
 -> A
```

The validation must traverse the component graph.

Use an iterative/recursive graph traversal with a visited set.

Do not create an infinite loop.

The algorithm should detect whether adding:

```text
parent -> component
```

would create a path:

```text
component -> ... -> parent
```

If yes, reject the operation.

This check must run when:

* adding a component
* changing a component's `component_item_id`

---

# 17.9 Duplicate Components

A BOM should not contain the same component item more than once unless the existing domain explicitly requires duplicate component rows.

Prefer enforcing:

```text
unique(bom_id, component_item_id)
```

at the database level.

Also provide application-level validation for a useful error response.

If duplicate components are intentionally allowed by the existing model, preserve that behavior and document why.

---

# 17.10 Component Updates

When updating a component:

* validate quantity
* validate component item
* validate circular references
* validate duplicate component
* preserve BOM relationship
* do not allow moving a component between BOMs through a component update unless explicitly supported

If a component must be moved between BOMs, use a dedicated operation rather than silently changing its parent.

---

# 17.11 Component Deletion

Component deletion must respect BOM lifecycle.

Determine whether components can be deleted from:

```text
draft BOM
active BOM
historical BOM
```

The safest default is:

```text
draft -> editable
active -> controlled
historical -> immutable
```

Do not allow deletion if it would invalidate historical inventory operations.

---

# 17.12 BOM Resource Fixes

Inspect the existing `ItemBomResource`.

Verify the item field uses the actual `Item` model attribute.

The repository uses:

```text
item_code
```

for the item code.

Do not reference a nonexistent property such as:

```text
code
```

if the current model does not define it.

Check all BOM resources for similar incorrect property access.

---

# 17.13 BOM API Structure

Preserve the project's API response convention:

```json
{
    "success": true,
    "message": "...",
    "data": {},
    "meta": {}
}
```

Use existing resources.

BOM responses should contain useful information such as:

```text
id
item_id
item
name
version
is_active
components
created_at
updated_at
```

Component responses should expose:

```text
id
bom_id
component_item_id
component_item
quantity
is_required
created_at
updated_at
```

Do not create inconsistent response formats.

---

# 17.14 BOM Service / Domain Layer

Keep BOM business rules in the service/domain layer.

The service should handle:

```text
create BOM
update BOM
activate BOM
deactivate BOM
add component
update component
delete component
```

and enforce:

```text
one active BOM
valid version
component quantity
self-reference
indirect circular reference
duplicate component
historical integrity
```

Controllers should remain thin.

---

# 17.15 Asset Components

Before assuming an asset-component implementation already exists, inspect the repository.

Search for:

```text
AssetComponent
asset_components
AssetComponentService
assets/{asset}/components
```

If these do not exist, implement the missing Phase 17 asset-component functionality only if it is part of the repository's existing Phase 17 design.

The expected conceptual relationship is:

```text
Asset Instance
    |
    +-- Component
            |
            +-- Item
            +-- Serial (optional)
            +-- quantity
```

Do not implement assembly/disassembly here.

Phase 17 should establish the component model required by Phase 18.

If an existing asset component implementation is present, harden and test it instead of duplicating it.

---

# 17.16 Phase 17 Tests

Add/complete Pest tests.

## BOM creation

* create valid BOM
* invalid parent item fails
* non-composite item behavior follows domain rule
* duplicate active BOM fails
* duplicate version behavior is correct
* activation/deactivation works

## BOM updates

* update draft BOM
* activate inactive BOM
* cannot activate when another active BOM exists
* historical BOM cannot be modified if immutable rule applies

## Components

* add valid component
* invalid component item fails
* zero quantity fails
* negative quantity fails
* self-reference fails
* indirect circular reference fails
* duplicate component fails
* update component works
* invalid component update fails
* delete component works where lifecycle permits
* historical component cannot be altered where prohibited

## Graph cases

Test at least:

```text
A -> A
A -> B -> A
A -> B -> C -> A
A -> B -> C
```

The first three must fail.

The last must succeed.

## Resources

* BOM resource returns valid item code
* component resource returns correct component item
* nested relationships do not trigger N+1 queries where practical

## Concurrency

If active BOM uniqueness is protected at the database level:

* concurrent activation/creation cannot produce two active BOMs for one item

---

# PHASE 17 ACCEPTANCE CRITERIA

Phase 17 is complete only when:

* [ ] BOM creation is validated.
* [ ] Composite-item rules are enforced according to the existing domain.
* [ ] Only one active BOM exists per item.
* [ ] BOM version rules are consistent.
* [ ] Historical BOM integrity is protected.
* [ ] Component quantity must be positive.
* [ ] Component item must exist.
* [ ] Parent cannot be its own component.
* [ ] Indirect BOM circular references are detected.
* [ ] Duplicate components are prevented.
* [ ] Component updates are validated.
* [ ] Component deletion respects BOM lifecycle.
* [ ] BOM resources use correct Item model fields.
* [ ] API responses follow project conventions.
* [ ] Asset-component functionality is completed if required by the existing Phase 17 design.
* [ ] Pest tests cover all critical business rules.
* [ ] Existing BOM and inventory tests still pass.

---

# FINAL VERIFICATION

After implementing Phase 15 and Phase 17:

1. Run formatting/linting tools already configured in the repository.
2. Run all Phase 15 tests.
3. Run all Phase 17 tests.
4. Run the complete Pest suite.
5. Run Laravel migrations against a clean test database.
6. Verify migration rollback.
7. Verify API validation responses.
8. Verify database constraints.
9. Verify transaction rollback behavior.
10. Verify no existing inventory tests regress.

Do not mark a phase complete based only on HTTP validation tests.

Business rules must also be enforced at the service/domain layer.

---

# Required Output From Copilot

After implementation, provide:

## 1. Changed Files

List every created/modified file.

Group them by:

```text
Migrations
Models
Enums
Requests
Resources
Controllers
Services / Actions
Routes
Tests
```

## 2. Database Changes

Explain each migration and why it is required.

## 3. Business Rules

Summarize all new Phase 15 and Phase 17 rules.

## 4. Tests

Report:

```text
Phase 15 tests: PASS/FAIL
Phase 17 tests: PASS/FAIL
Full test suite: PASS/FAIL
```

Include the exact test command used.

## 5. Remaining Issues

Do not hide unresolved issues.

If something cannot safely be implemented because the existing architecture is ambiguous, report it instead of inventing behavior.

---

# Strict Boundary

Do NOT implement Phase 18 Assembly / Disassembly yet.

Do NOT implement:

```text
ReserveInventoryAction
ReleaseReservationAction
IssueInventoryAction
TransferInventoryAction
ReturnInventoryAction
AdjustInventoryAction
CompleteStockCountAction
DisposeInventoryAction
```

unless they are already existing functionality required to make Phase 15 work.

Phase 15 and Phase 17 must be completed first.

The next phase after successful completion is:

```text
Phase 18 — Assembly / Disassembly
```
