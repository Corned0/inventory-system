error, no update from controller but suggested to create tests for update

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