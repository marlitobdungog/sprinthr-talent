<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class BucketApiClient
{
    public function uploadResume(UploadedFile $file, string $candidateId, string $originalFilename): string
    {
        $extension = $file->extension();
        $originalBasename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $safeBasename = preg_replace('/[^A-Za-z0-9._-]+/', '_', $originalBasename) ?: 'resume';
        $safeBasename = Str::limit($safeBasename, 166, '');
        $filename = Str::uuid().'_'.$safeBasename.'.'.$extension;
        $expectedPrefix = 'resumes/'.$candidateId.'/';
        $fileContents = file_get_contents($file->getRealPath());

        if ($fileContents === false) {
            throw new RuntimeException('The uploaded resume could not be read.');
        }

        $response = $this->request()->post($this->url('/upload'), [
            'filename' => $filename,
            'file_base64' => base64_encode($fileContents),
            'provider' => 'r2',
            'path' => 'resumes/'.$candidateId,
            'bucket' => $this->bucket(),
            'content_type' => $file->getMimeType() ?: 'application/octet-stream',
        ]);

        $this->ensureSuccess($response, [201]);
        $objectKey = $response->json('blob_name');

        if (! is_string($objectKey) || ! str_starts_with($objectKey, $expectedPrefix)) {
            throw new BucketApiException('The storage service returned an invalid resume object key.', $response->status());
        }

        return $objectKey;
    }

    public function temporaryDownloadUrl(string $objectKey): string
    {
        $response = $this->request()->get($this->url('/download-url/'.self::encodePath($objectKey)), [
            'provider' => 'r2',
            'bucket' => $this->bucket(),
            'expiration' => 1,
        ]);

        $this->ensureSuccess($response, [200]);
        $downloadUrl = $response->json('download_url');

        if (! is_string($downloadUrl) || ! filter_var($downloadUrl, FILTER_VALIDATE_URL) || parse_url($downloadUrl, PHP_URL_SCHEME) !== 'https') {
            throw new BucketApiException('The storage service returned an invalid download URL.', $response->status());
        }

        return $downloadUrl;
    }

    public function delete(string $objectKey): void
    {
        $this->assertR2DeleteEnabled();

        $response = $this->request()->delete($this->url('/delete-r2/'.self::encodePath($objectKey), [
            'bucket' => $this->bucket(),
        ]));

        if ($response->status() === 404 && $response->json('error') === 'File not found') {
            return;
        }

        $this->ensureSuccess($response, [200]);
    }

    private function assertR2DeleteEnabled(): void
    {
        $health = $this->request()->get($this->url('/health'));

        if (! $health->successful() || $health->json('r2_delete_enabled') !== true) {
            throw new BucketApiException('R2 deletion is not enabled on the Bucket API deployment.', $health->status());
        }
    }

    private function request(): PendingRequest
    {
        $key = (string) config('services.bucket_api.key');

        if (trim($key) === '') {
            throw new RuntimeException('Bucket API credentials are not configured.');
        }

        return Http::asJson()
            ->acceptJson()
            ->withHeader('X-API-Key', $key)
            ->timeout(60)
            ->connectTimeout(10)
            ->withoutRedirecting();
    }

    private function bucket(): string
    {
        $bucket = trim((string) config('services.bucket_api.bucket'));

        if ($bucket === '') {
            throw new RuntimeException('The resume storage bucket is not configured.');
        }

        return $bucket;
    }

    private function url(string $path, array $query = []): string
    {
        $baseUrl = rtrim((string) config('services.bucket_api.url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('The Bucket API URL is not configured.');
        }

        return $baseUrl.$path.($query === [] ? '' : '?'.http_build_query($query));
    }

    private function ensureSuccess(Response $response, array $acceptedStatuses): void
    {
        if (! in_array($response->status(), $acceptedStatuses, true)) {
            throw new BucketApiException('The Bucket API request failed.', $response->status());
        }
    }

    private static function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', trim($path, '/'))));
    }
}
