next: # 18. Phase 15 — Asset Instances

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