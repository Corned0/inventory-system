<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case Receipt = 'receipt';
    case PutAway = 'put_away';

    case Issue = 'issue';

    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';

    case Return = 'return';

    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';

    case Disposal = 'disposal';

    case Assembly = 'assembly';
    case Disassembly = 'disassembly';

    case StockCount = 'stock_count';

    public function stockEffect(): int
    {
        return match ($this) {
            self::Receipt,
            self::TransferIn,
            self::Return,
            self::AdjustmentIn,
            self::Disassembly => 1,

            self::Issue,
            self::TransferOut,
            self::AdjustmentOut,
            self::Disposal,
            self::Assembly => -1,

            self::PutAway,
            self::StockCount => 0,
        };
    }

    public function affectsStock(): bool
    {
        return $this->stockEffect() !== 0;
    }

    public function increasesStock(): bool
    {
        return $this->stockEffect() > 0;
    }

    public function decreasesStock(): bool
    {
        return $this->stockEffect() < 0;
    }
}