<?php

class Upload
{
    public static function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }

    public static function moveImage(
        array $file,
        string $targetDirectory,
        array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp']
    ): array {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload failed.');
        }

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Invalid uploaded file.');
        }

        $mimeType = mime_content_type($tmp) ?: '';
        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new RuntimeException('Unsupported file type.');
        }
      
        self::ensureDirectory($targetDirectory);

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'bin',
            };
        }

        $storedName = Util::uuid() . '.' . $extension;
        $destination = rtrim($targetDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $storedName;
        $isUploaded = move_uploaded_file($tmp, $destination);
   
        if (!$isUploaded) {
            throw new RuntimeException('Unable to move uploaded file.');
        }

        return [
            'original_name' => $file['name'] ?? '',
            'stored_name' => $storedName,
            'file_extension' => $extension,
            'physical_path' => $destination,
            'size' => (int) ($file['size'] ?? 0),
            'mime_type' => $mimeType,
        ];
    }
}
