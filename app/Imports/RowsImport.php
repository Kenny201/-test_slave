<?php

namespace App\Imports;

use App\Models\Row;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;

class RowsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithValidation, SkipsOnFailure, WithChunkReading,
                            WithSkipDuplicates, WithEvents
{

    public function __construct(
        protected ?string $redisKey,
        protected int $userId,
        public array $errors = []
    ) {}

    public function model(array $row): ?Row
    {

        $this->incrementRedisKey();

        return new Row([
            'id' => $row['id'],
            'name' => $row['name'],
            'date' => Carbon::createFromFormat('d.m.Y', $row['date']),
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'numeric', 'gt:0', Rule::unique('rows', 'id')],
            'name' => ['required', 'regex:/^[a-zA-Z\s]+$/'],
            'date' => ['required', 'date_format:d.m.Y'],
        ];
    }

    public function logErrors(): void
    {
        if (!empty($this->errors)) {
            $file = fopen(base_path('result.txt'), 'w');
            foreach ($this->errors as $error) {
                fwrite($file, $error.PHP_EOL);
            }
            fclose($file);
        }
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: ".implode(', ', $failure->errors());
        }
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function incrementRedisKey(): void
    {
        if ($this->redisKey) {
            Redis::incr($this->redisKey);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $this->storeFinalRowCount($event);
            },
        ];
    }

    private function storeFinalRowCount(AfterImport $event): void
    {
        $reader = $event->getReader();
        $totalRowCount = array_sum($reader->getTotalRows()) - count($reader->getTotalRows());

        Redis::set("import_final_count:{$this->userId}", max(0, $totalRowCount));
    }
}
