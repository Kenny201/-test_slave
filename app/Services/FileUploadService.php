<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class FileUploadService
{
    public function getProgress($userId): float
    {
        $processedRows = Redis::get("import_progress:{$userId}") ?? 0;
        $totalRows = Redis::get("import_total_rows:{$userId}") ?? 0;

        return $this->calculateProgress($processedRows, $totalRows);
    }
    protected function calculateProgress($processedRows, $totalRows): float
    {
        return ($totalRows > 0) ? round(($processedRows / $totalRows) * 100, 2) : 0;
    }

    public function storeFile($file): string
    {
        return $file->storeAs('temp', $file->getClientOriginalName());
    }
}
