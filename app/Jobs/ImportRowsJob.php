<?php

namespace App\Jobs;

use App\Events\AllRowsCreated;
use App\Imports\RowsImport;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class ImportRowsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;

    public function __construct(
        protected string $filePath,
        protected string $redisKey,
        protected int $userId
    ) {}

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        Log::info("Starting the import job.");

        Redis::set($this->redisKey, 0);
        Redis::set("import_status:{$this->userId}", true);

        $import = new RowsImport($this->redisKey, $this->userId);

        Log::info("Before Excel import.");

        $rowsCollection = Excel::toCollection($import, $this->filePath);
        $totalRows = $rowsCollection->first()->count();

        Redis::set("import_total_rows:{$this->userId}", $totalRows);
        Excel::import($import, $this->filePath);
        Redis::set("import_progress:{$this->userId}", $totalRows);

        $rowCount = Redis::get("import_final_count:{$this->userId}") ?? 0;

        event(new AllRowsCreated($rowCount, $this->userId));

        Log::info("After Excel import.");

        $import->logErrors();

        Log::info("Import job completed.");

        Redis::set("import_status:{$this->userId}", false);
    }
}
