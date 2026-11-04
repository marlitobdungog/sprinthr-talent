<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Services\BucketApiException;
use App\Services\ResumeStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ResumeController extends Controller
{
    public function index(Request $request): Response
    {
        $candidate = $request->user()->candidateProfile();
        $resume = $candidate->resume()->first();

        return Inertia::render('resume/index', [
            'resume' => $resume ? [
                'id' => $resume->id,
                'filename' => $resume->original_filename,
                'mime_type' => $resume->mime_type,
                'size_bytes' => $resume->size_bytes,
                'version' => $resume->version,
                'parse_status' => $resume->parse_status,
                'uploaded_at' => $resume->uploaded_at->toDateString(),
            ] : null,
        ]);
    }

    public function store(Request $request, ResumeStorage $resumeStorage): RedirectResponse
    {
        $data = $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,docx', 'max:10240'],
        ]);

        $candidate = $request->user()->candidateProfile();
        $file = $data['resume'];
        $diskName = config('talent.resume_disk');
        $originalFilename = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $originalFilename = preg_replace('/[\x00-\x1F\x7F]/', '', $originalFilename) ?? '';
        $originalFilename = Str::limit(trim($originalFilename), 240, '');
        $originalFilename = $originalFilename !== '' ? $originalFilename : 'resume.'.$file->extension();

        try {
            $objectKey = $resumeStorage->store($file, (string) $candidate->id, $diskName, $originalFilename);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['resume' => 'The file could not be stored. Please try again.']);
        }

        if (! $objectKey) {
            return back()->withErrors(['resume' => 'The file could not be stored. Please try again.']);
        }

        $resume = $candidate->resume()->first();
        $previousKey = $resume?->private_object_key;
        $previousDisk = $resume?->storage_disk;
        $resume ??= new Resume;
        $resume->fill([
            'storage_disk' => $diskName,
            'private_object_key' => $objectKey,
            'original_filename' => $originalFilename,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size_bytes' => $file->getSize(),
            'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
            'version' => ($resume->version ?? 0) + 1,
            'parse_status' => 'awaiting_parser',
            'uploaded_at' => now(),
        ]);
        try {
            $candidate->resume()->save($resume);
        } catch (Throwable $exception) {
            try {
                $resumeStorage->delete($diskName, $objectKey);
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }

            throw $exception;
        }

        if ($previousKey && ($previousKey !== $objectKey || $previousDisk !== $diskName)) {
            try {
                $resumeStorage->delete($previousDisk ?? $diskName, $previousKey);
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }
        }

        $candidate->increment('profile_version');

        return back()->with('success', 'Resume stored privately. Profile details can be edited while extraction is being connected.');
    }

    public function download(Request $request, ResumeStorage $resumeStorage): mixed
    {
        $candidate = $request->user()->candidateProfile();
        $resume = $candidate->resume()->firstOrFail();

        if ($resume->storage_disk === ResumeStorage::BUCKET_API_DISK) {
            try {
                return redirect()->away($resumeStorage->downloadUrl($resume));
            } catch (Throwable $exception) {
                if ($exception instanceof BucketApiException && $exception->statusCode === 404) {
                    abort(404);
                }

                report($exception);

                abort(503, 'Your resume is temporarily unavailable. Please try again.');
            }
        }

        abort_unless(Storage::disk($resume->storage_disk)->exists($resume->private_object_key), 404);

        return Storage::disk($resume->storage_disk)->download($resume->private_object_key, $resume->original_filename, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, ResumeStorage $resumeStorage): RedirectResponse
    {
        $candidate = $request->user()->candidateProfile();
        $resume = $candidate->resume()->first();

        if ($resume) {
            $resumeStorage->delete($resume->storage_disk, $resume->private_object_key);
            $resume->delete();
            $candidate->increment('profile_version');
        }

        return back()->with('success', 'Resume deleted.');
    }
}
