<?php

namespace App\Services;

use App\Models\Resume;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResumeStorage
{
    public const BUCKET_API_DISK = 'bucket-api-r2';

    public function __construct(private readonly BucketApiClient $bucketApi) {}

    public function store(UploadedFile $file, string $candidateId, string $diskName, string $originalFilename): ?string
    {
        if ($diskName === self::BUCKET_API_DISK) {
            return $this->bucketApi->uploadResume($file, $candidateId, $originalFilename);
        }

        $filename = Str::uuid().'.'.$file->extension();

        return $file->storeAs('resumes/'.$candidateId, $filename, $diskName);
    }

    public function downloadUrl(Resume $resume): string
    {
        return $this->bucketApi->temporaryDownloadUrl($resume->private_object_key);
    }

    public function delete(string $diskName, string $objectKey): void
    {
        if ($diskName === self::BUCKET_API_DISK) {
            $this->bucketApi->delete($objectKey);

            return;
        }

        Storage::disk($diskName)->delete($objectKey);
    }
}
