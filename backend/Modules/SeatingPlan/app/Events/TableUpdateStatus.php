<?php

namespace Modules\SeatingPlan\Events;

use Modules\SeatingPlan\Enums\TableStatus;
use Modules\SeatingPlan\Models\Table;

class TableUpdateStatus
{
    /**
     * Create a new event instance.
     *
     * @param Table $table
     * @param int|null $changedById
     * @param TableStatus $status
     * @param string|null $note
     */
    public function __construct(
        public Table       $table,
        public TableStatus $status,
        public ?int        $changedById = null,
        public ?string     $note = null,
    )
    {
    }
}
