<?php

namespace Tests\Feature;

use App\Jobs\ImportRowsJob;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FileUploadControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[DataProvider('uploadFormAccessProvider')]
    public function test_upload_form_access(bool $isAuthorized, int $expectedStatus): void
    {
        $request = $isAuthorized ? $this->actingAs($this->user) : $this;

        $response = $request->get(route('upload.form'));

        $response->assertStatus($expectedStatus);
    }

    public static function uploadFormAccessProvider(): array
    {
        return [
            'authorized user can access upload form' => [
                'isAuthorized' => true,
                'expectedStatus' => 200,
            ],
            'unauthorized user cannot access upload form' => [
                'isAuthorized' => false,
                'expectedStatus' => 401,
            ],
        ];
    }

    #[DataProvider('progressDataProvider')]
    public function test_progress_endpoint(
        int $progress,
        int $totalRows,
        bool $importStatus,
        array $expectedJson
    ): void {
        Redis::shouldReceive('get')
            ->with("import_progress:{$this->user->id}")
            ->andReturn($progress);

        Redis::shouldReceive('get')
            ->with("import_total_rows:{$this->user->id}")
            ->andReturn($totalRows);

        Redis::shouldReceive('get')
            ->with("import_status:{$this->user->id}")
            ->andReturn($importStatus);

        $response = $this->actingAs($this->user)->get('/upload/progress');

        $response->assertStatus(200)
            ->assertJson($expectedJson);
    }

    public static function progressDataProvider(): array
    {
        return [
            'returns correct progress data' => [
                'progress' => 50,
                'totalRows' => 100,
                'importStatus' => true,
                'expectedJson' => [
                    'progress' => 50.0,
                    'isImporting' => true,
                ],
            ],
            'returns zero progress' => [
                'progress' => 0,
                'totalRows' => 100,
                'importStatus' => false,
                'expectedJson' => [
                    'progress' => 0.0,
                    'isImporting' => false,
                ],
            ],
            'returns completed progress' => [
                'progress' => 100,
                'totalRows' => 100,
                'importStatus' => false,
                'expectedJson' => [
                    'progress' => 100.0,
                    'isImporting' => false,
                ],
            ],
        ];
    }

    #[DataProvider('fileUploadProvider')]
    public function test_file_upload(
        ?string $mimeType,
        int $size,
        bool $shouldPass,
        ?string $errorMessage = null
    ): void {
        Storage::fake('local');
        Queue::fake();

        $file = $mimeType ?
            UploadedFile::fake()->create(
                'test.xlsx',
                $size,
                $mimeType
            ) :
            null;

        $this->mock(FileUploadService::class, function ($mock) {
            $mock->shouldReceive('storeFile')
                ->andReturn('temp/test.xlsx');
        });

        $response = $this->actingAs($this->user)
            ->from(route('upload.form'))
            ->post(route('upload.handle'), [
                'file' => $file
            ]);

        if ($shouldPass) {
            $response->assertRedirect(route('upload.form'))
                ->assertSessionHas('success', 'File uploaded and processed successfully');

            Queue::assertPushed(ImportRowsJob::class);
        } else {
            $response->assertRedirect()
                ->assertSessionHasErrors(['file' => $errorMessage]);
        }
    }




    public static function fileUploadProvider(): array
    {
        return [
            'successful xlsx upload' => [
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'size' => 1000,
                'shouldPass' => true,
            ],
            'file too large' => [
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'size' => 3000,
                'shouldPass' => false,
                'errorMessage' => 'Размер файла не должен превышать 2 МБ.',
            ],
            'wrong mime type' => [
                'mimeType' => 'text/plain',
                'size' => 1000,
                'shouldPass' => false,
                'errorMessage' => 'Файл должен быть формата Excel (xlsx).',
            ],
            'no file provided' => [
                'mimeType' => null,
                'size' => 0,
                'shouldPass' => false,
                'errorMessage' => 'Пожалуйста, выберите файл для загрузки.',
            ],
        ];
    }
}
