<?php

namespace App\Http\Controllers;

use App\Models\Resume;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,docx', 'max:10240'],
        ]);

        $candidate = $request->user()->candidateProfile();
        $file = $data['resume'];
        $diskName = config('talent.resume_disk');
        $disk = Storage::disk($diskName);
        $filename = Str::uuid().'.'.$file->extension();
        $originalFilename = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $originalFilename = preg_replace('/[\x00-\x1F\x7F]/', '', $originalFilename) ?? '';
        $originalFilename = Str::limit(trim($originalFilename), 240, '');
        $originalFilename = $originalFilename !== '' ? $originalFilename : 'resume.'.$file->extension();
        $objectKey = $file->storeAs('resumes/'.$candidate->id, $filename, $diskName);

        if (! $objectKey) {
            return back()->withErrors(['resume' => 'The file could not be stored. Please try again.']);
        }

        $resume = $candidate->resume()->first();
        $previousKey = $resume?->private_object_key;
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
            $disk->delete($objectKey);

            throw $exception;
        }

        if ($previousKey && $previousKey !== $objectKey) {
            $disk->delete($previousKey);
        }

        $candidate->increment('profile_version');

        return back()->with('success', 'Resume stored privately. Profile details can be edited while extraction is being connected.');
    }

    public function download(Request $request): mixed
    {
        $candidate = $request->user()->candidateProfile();
        $resume = $candidate->resume()->firstOrFail();

        abort_unless(Storage::disk($resume->storage_disk)->exists($resume->private_object_key), 404);

        return Storage::disk($resume->storage_disk)->download($resume->private_object_key, $resume->original_filename, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $candidate = $request->user()->candidateProfile();
        $resume = $candidate->resume()->first();

        if ($resume) {
            Storage::disk($resume->storage_disk)->delete($resume->private_object_key);
            $resume->delete();
            $candidate->increment('profile_version');
        }

        return back()->with('success', 'Resume deleted.');
    }
}
