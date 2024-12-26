<?php

namespace Tests\Unit;

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadServiceTest extends TestCase
{
    private FileUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FileUploadService();
    }

    public function test_calculate_progress()
    {
        Redis::shouldReceive('get')
            ->with('import_progress:1')
            ->andReturn(50);

        Redis::shouldReceive('get')
            ->with('import_total_rows:1')
            ->andReturn(100);

        $progress = $this->service->getProgress(1);
        $this->assertEquals(50.0, $progress);
    }

    public function test_store_file()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.xlsx');
        $path = $this->service->storeFile($file);

        Storage::disk('local')->assertExists($path);
    }
}
