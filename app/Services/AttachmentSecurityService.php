<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class AttachmentSecurityService
{
    private const ENCRYPTED_PATH_PREFIX = 'enc:';
    private const PREVIEW_ROUTE_NAME = 'api.internal.attachments.preview';

    /**
     * @return array{encrypted_path: string, stored_path: string}
     */
    public function storeEncryptedProfileImage(UploadedFile $file): array
    {
        $directory = (string) config('attachments.profile_directory', 'profile-images');

        return $this->storeEncryptedUploadedFile($file, $directory);
    }

    /**
     * @return array{encrypted_path: string, stored_path: string}
     */
    public function storeEncryptedUploadedFile(UploadedFile $file, string $directory): array
    {
        $normalizedDirectory = trim($directory, '/');
        if ($normalizedDirectory === '') {
            $normalizedDirectory = 'attachments';
        }

        $storedPath = $normalizedDirectory . '/' . Str::uuid() . '.enc';
        $rawContent = file_get_contents($file->getRealPath());
        if (!is_string($rawContent)) {
            throw new RuntimeException('File upload tidak dapat dibaca.');
        }

        $payload = [
            'v' => 1,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'original_name' => $file->getClientOriginalName() ?: basename($storedPath),
            'binary' => base64_encode($rawContent),
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if (!is_string($payloadJson) || $payloadJson === '') {
            throw new RuntimeException('Payload file terenkripsi tidak valid.');
        }

        $encryptedPayload = Crypt::encryptString($payloadJson);
        $stored = Storage::disk('local')->put($storedPath, $encryptedPayload);
        if ($stored !== true) {
            throw new RuntimeException('File terenkripsi gagal disimpan.');
        }

        return [
            'encrypted_path' => $this->encryptPathValue($storedPath),
            'stored_path' => $storedPath,
        ];
    }

    public function generateTemporaryPreviewUrl(?Attachment $attachment, ?int $ttlMinutes = null): ?string
    {
        if (!$attachment instanceof Attachment) {
            return null;
        }

        $path = trim((string) $attachment->path);
        if ($path === '') {
            return null;
        }

        if (!Route::has(self::PREVIEW_ROUTE_NAME)) {
            return null;
        }

        $ttl = $this->resolveTtlMinutes($ttlMinutes);

        return URL::temporarySignedRoute(
            self::PREVIEW_ROUTE_NAME,
            now()->addMinutes($ttl),
            ['attachmentUuid' => $attachment->uuid]
        );
    }

    /**
     * @return array{binary: string, mime_type: string, filename: string}
     */
    public function readAttachmentPayload(Attachment $attachment): array
    {
        $path = trim((string) $attachment->path);
        if ($path === '') {
            throw new RuntimeException('Attachment path tidak tersedia.');
        }

        if ($this->isEncryptedPathValue($path)) {
            return $this->readEncryptedAttachmentPayload($attachment, $path);
        }

        return $this->readLegacyAttachmentPayload($attachment, $path);
    }

    public function buildInlineImageResponse(Attachment $attachment): Response
    {
        $payload = $this->readAttachmentPayload($attachment);
        $ttl = $this->resolveTtlMinutes(null);
        $fileName = $this->sanitizeFileName($payload['filename']);

        return response($payload['binary'], 200, [
            'Content-Type' => $payload['mime_type'],
            'Content-Length' => (string) strlen($payload['binary']),
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Cache-Control' => 'private, max-age=' . ($ttl * 60) . ', must-revalidate',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function readEncryptedAttachmentPayload(Attachment $attachment, string $encryptedPathValue): array
    {
        $storedPath = $this->decryptPathValue($encryptedPathValue);

        if (!Storage::disk('local')->exists($storedPath)) {
            throw new RuntimeException('File attachment terenkripsi tidak ditemukan.');
        }

        $encryptedPayload = Storage::disk('local')->get($storedPath);
        if (!is_string($encryptedPayload) || $encryptedPayload === '') {
            throw new RuntimeException('File attachment terenkripsi tidak dapat dibaca.');
        }

        try {
            $payloadJson = Crypt::decryptString($encryptedPayload);
        } catch (DecryptException $exception) {
            throw new RuntimeException('File attachment terenkripsi tidak valid.');
        }

        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            throw new RuntimeException('Payload attachment terenkripsi tidak valid.');
        }

        $binary = base64_decode((string) ($payload['binary'] ?? ''), true);
        if (!is_string($binary)) {
            throw new RuntimeException('Payload attachment terenkripsi rusak.');
        }

        $mimeType = trim((string) ($payload['mime_type'] ?? ''));
        if ($mimeType === '') {
            $mimeType = $this->guessMimeType($binary);
        }

        $filename = trim((string) ($payload['original_name'] ?? $attachment->name ?? 'attachment.bin'));
        if ($filename === '') {
            $filename = 'attachment.bin';
        }

        return [
            'binary' => $binary,
            'mime_type' => $mimeType,
            'filename' => $filename,
        ];
    }

    private function readLegacyAttachmentPayload(Attachment $attachment, string $legacyPath): array
    {
        if (
            str_starts_with($legacyPath, 'http://') ||
            str_starts_with($legacyPath, 'https://')
        ) {
            throw new RuntimeException('Legacy external attachment URL tidak didukung.');
        }

        $normalizedPath = ltrim($legacyPath, '/');
        if (str_starts_with($normalizedPath, 'storage/')) {
            $normalizedPath = substr($normalizedPath, strlen('storage/'));
        }

        if ($normalizedPath === '') {
            throw new RuntimeException('Legacy attachment path tidak valid.');
        }

        if (!Storage::disk('public')->exists($normalizedPath)) {
            throw new RuntimeException('Legacy attachment file tidak ditemukan.');
        }

        $binary = Storage::disk('public')->get($normalizedPath);
        if (!is_string($binary)) {
            throw new RuntimeException('Legacy attachment file tidak dapat dibaca.');
        }

        $filename = trim((string) ($attachment->name ?: basename($normalizedPath)));
        if ($filename === '') {
            $filename = 'attachment.bin';
        }

        return [
            'binary' => $binary,
            'mime_type' => $this->guessMimeType($binary),
            'filename' => $filename,
        ];
    }

    private function resolveTtlMinutes(?int $ttlMinutes = null): int
    {
        $ttl = $ttlMinutes ?? (int) config('attachments.temporary_url_ttl_minutes', 30);

        return max(1, $ttl);
    }

    private function guessMimeType(string $binary): string
    {
        if (!function_exists('finfo_open')) {
            return 'application/octet-stream';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return 'application/octet-stream';
        }

        $mime = finfo_buffer($finfo, $binary);
        finfo_close($finfo);

        return is_string($mime) && $mime !== '' ? $mime : 'application/octet-stream';
    }

    private function encryptPathValue(string $path): string
    {
        return self::ENCRYPTED_PATH_PREFIX . Crypt::encryptString($path);
    }

    private function decryptPathValue(string $encryptedPathValue): string
    {
        $cipher = substr($encryptedPathValue, strlen(self::ENCRYPTED_PATH_PREFIX));
        if ($cipher === '') {
            throw new RuntimeException('Encrypted attachment path tidak valid.');
        }

        try {
            $decryptedPath = Crypt::decryptString($cipher);
        } catch (DecryptException $exception) {
            throw new RuntimeException('Encrypted attachment path tidak valid.');
        }

        $normalizedPath = trim($decryptedPath);
        if ($normalizedPath === '') {
            throw new RuntimeException('Encrypted attachment path kosong.');
        }

        return $normalizedPath;
    }

    private function isEncryptedPathValue(string $path): bool
    {
        return str_starts_with($path, self::ENCRYPTED_PATH_PREFIX);
    }

    private function sanitizeFileName(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $safeName = Str::slug($name);
        if ($safeName === '') {
            $safeName = 'attachment';
        }

        $safeExtension = Str::slug($extension);
        if ($safeExtension !== '') {
            return $safeName . '.' . $safeExtension;
        }

        return $safeName;
    }
}
