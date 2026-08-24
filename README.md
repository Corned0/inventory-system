next: # Phase 17 — Actual Component Assembly

EVENT FLOW:
    Receiving
    ↓
    Accept
        ↓
    Complete
        ↓
    InventoryTransactionService
        ↓
    InventoryBalanceService
        ↓
    inventory_transactions
        +
    inventory_balances

Inspection should transition:
    draft → received → under_inspection