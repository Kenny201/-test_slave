<?php

namespace App\Repositories;

use App\Models\Row;
use Illuminate\Support\Collection;

class RowRepository
{
    public function getRowsGroupedByDate(): Collection
    {
        return Row::query()
            ->select('date', 'id', 'name')
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('date');
    }
}
