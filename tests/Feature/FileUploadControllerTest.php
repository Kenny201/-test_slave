<?php

namespace Tests\Feature;

use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FileUploadControllerTest extends TestCase
{
    /** Табличные данные для тестов. */
    public static function uploadDataProvider(): array
    {
        return [
            'successful_upload' => [
                'user' => 'create',
                'fileType' => 'xlsx',
                'expectedStatus' => 200,
                'expectedJson' => ['message' => 'File uploaded and processed successfully'],
                'mockParse' => ['parse' => [['date' => '23.12.2024', 'name' => 'Test Name', 'id' => 1]]],
                'expectedException' => null
            ],

            'unauthorized_user' => [
                'user' => null,
                'fileType' => 'xlsx',
                'expectedStatus' => 401,
                'expectedJson' => [],
                'mockParse' => null,
                'expectedException' => null
            ],

            'invalid_file_type' => [
                'user' => 'create',
                'fileType' => 'txt',
                'expectedStatus' => 422,
                'expectedJson' => ['message' => 'The file field must be a file of type: xlsx.'],
                'mockParse' => null,
                'expectedException' => null
            ],

            'parsing_error' => [
                'user' => 'create',
                'fileType' => 'xlsx',
                'expectedStatus' => 422,
                'expectedJson' => ['message' => 'Error parsing the file'],
                'mockParse' => new Exception('Error parsing the file'),
                'expectedException' => 'Exception'
            ],

            'missing_file' => [
                'user' => 'create',
                'fileType' => null,
                'expectedStatus' => 422,
                'expectedJson' => ['message' => 'The file field is required.'],
                'mockParse' => null,
                'expectedException' => null
            ]
        ];
    }

    #[DataProvider('uploadDataProvider')]
    public function test_file_upload($user, $fileType, $expectedStatus, $expectedJson, $mockParse, $expectedException)
    {
        $userInstance = $this->getUserInstance($user);

        $file = $this->createFile($fileType);

        $this->mockFileParser($mockParse, $expectedException);

        $response = $this->sendFileUploadRequest($userInstance, $file);
        $this->assertResponse($response, $expectedStatus, $expectedJson);
    }

    /** Получить или создать пользователя. */
    private function getUserInstance(?string $user): ?User
    {
        if ($user === 'create') {
            return User::factory()->create();
        }
        return null;
    }

    /** Создать файл в зависимости от типа. */
    private function createFile(?string $fileType): ?UploadedFile
    {
        return $fileType ? UploadedFile::fake()->create("file.$fileType", 1024) : null;
    }

    /** Мокаем сервис парсера. */
    private function mockFileParser(mixed $mockParse, ?string $expectedException): void
    {
        if ($mockParse) {
            $fileParserMock = Mockery::mock('App\Services\FileParsers\FileParserInterface');
            if ($expectedException) {
                $fileParserMock->shouldReceive('parse')->once()->andThrow($mockParse);
            } else {
                $fileParserMock->shouldReceive('parse')->once()->andReturn($mockParse['parse']);
            }

            $this->app->instance('App\Services\FileParsers\FileParserInterface', $fileParserMock);
        }
    }

    /** Отправить запрос на загрузку файла. */
    private function sendFileUploadRequest(?User $userInstance, ?UploadedFile $file): TestResponse
    {
        return $userInstance
            ? $this->actingAs($userInstance)->postJson('upload', $file ? ['file' => $file] : [])
            : $this->postJson('upload', $file ? ['file' => $file] : []);
    }

    /** Проверка статуса и JSON ответа. */
    private function assertResponse(TestResponse $response, int $expectedStatus, ?array $expectedJson): void
    {
        $response->assertStatus($expectedStatus);

        if ($expectedJson) {
            $response->assertJson($expectedJson);
        }
    }
}

