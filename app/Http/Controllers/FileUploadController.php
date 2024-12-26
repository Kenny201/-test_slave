<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Jobs\ImportRowsJob;
use App\Services\FileUploadService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class FileUploadController extends Controller
{
    public function __construct(private readonly FileUploadService $fileUploadService)
    {
    }


    public function show(): Application|Factory|View
    {
        return view('upload');
    }

    public function getProgress(): JsonResponse
    {
        $progress = $this->fileUploadService->getProgress(auth()->id());
        $isImporting = $this->getImportStatus();

        return response()->json([
            'progress' => $progress,
            'isImporting' => $isImporting,
        ]);
    }

    public function upload(FileUploadRequest $request): RedirectResponse
    {
        try {
            $filePath = $this->handleFileUpload($request->file('file'));
            $this->dispatchImportJob($filePath);
            $this->setImportStatus(true);

            return redirect()->route('upload.form')->with('success', 'File uploaded and processed successfully');
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            return redirect()->route('upload.form')->withErrors(['file' => 'Ошибка обработки файла: ' . $e->getMessage()]);
        }
    }

    private function handleFileUpload($file): string
    {
        return $this->fileUploadService->storeFile($file);
    }

    private function dispatchImportJob(string $filePath): void
    {
        ImportRowsJob::dispatch($filePath, 'import_progress:' . auth()->id(), auth()->id());
    }

    private function setImportStatus(bool $status): void
    {
        Redis::set('import_status:' . auth()->id(), $status);
    }

    private function getImportStatus(): bool
    {
        return (bool) Redis::get('import_status:' . auth()->id());
    }
}
