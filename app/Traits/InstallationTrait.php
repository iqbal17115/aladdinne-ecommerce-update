<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

/**
 * License-verification helpers for the addon marketplace.
 *
 * Local copy of Abedin\WebInstaller\Traits\InstallationTrait (joynala/web-installer)
 * so the app no longer depends on that package — installation itself is handled
 * by the native installer (App\Http\Controllers\Install).
 */
trait InstallationTrait
{
    private function decrypt($encryptedCode, $key): string|bool
    {
        $data = base64_decode($encryptedCode);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encryptedData = substr($data, $ivLength);

        return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
    }

    public function verifyCode(array $data, $url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return curl_error($ch);
        }

        curl_close($ch);

        return $response;
    }

    public function makeJsonToPhpFile($path, $sourceCode): void
    {
        $relativePath = $this->validateRestorePath($path);
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        $content = $this->buildValidatedRestoreContent($sourceCode, $extension);
        $fullPath = base_path($relativePath);

        File::ensureDirectoryExists(dirname($fullPath));
        File::put($fullPath, $content);
    }

    /**
     * Only allow restore writes to explicitly approved application paths.
     * This prevents license verification responses from writing to arbitrary files.
     */
    protected function validateRestorePath(string $path): string
    {
        $normalizedPath = trim($path);

        if ($normalizedPath === '') {
            throw new InvalidArgumentException('Restore path cannot be empty.');
        }

        if (str_contains($normalizedPath, "\0") || str_contains($normalizedPath, '\\')) {
            throw new InvalidArgumentException('Restore path contains invalid characters.');
        }

        if (str_starts_with($normalizedPath, '/') || preg_match('/^[A-Za-z]:\//', $normalizedPath)) {
            throw new InvalidArgumentException('Absolute restore paths are not allowed.');
        }

        if (
            str_contains($normalizedPath, '../') ||
            str_contains($normalizedPath, './') ||
            str_contains($normalizedPath, '/..') ||
            $normalizedPath === '..' ||
            $normalizedPath === '.'
        ) {
            throw new InvalidArgumentException('Directory traversal is not allowed in restore paths.');
        }

        if (! preg_match('/^[A-Za-z0-9_\/\.-]+$/', $normalizedPath)) {
            throw new InvalidArgumentException('Restore path contains unsafe characters.');
        }

        $fileName = basename($normalizedPath);
        if (! preg_match('/^[A-Za-z0-9_.-]+$/', $fileName) || str_starts_with($fileName, '.')) {
            throw new InvalidArgumentException('Restore file name is not allowed.');
        }

        $allowedExtensions = config('installer.restore.allowed_extensions', ['php']);
        $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));
        if ($extension === '' || ! in_array($extension, $allowedExtensions, true)) {
            throw new InvalidArgumentException('Restore file type is not allowed.');
        }

        $allowedDirectories = config('installer.restore.allowed_directories', []);
        foreach ($allowedDirectories as $directory) {
            $directory = trim($directory, '/');
            if ($directory !== '' && ($normalizedPath === $directory || str_starts_with($normalizedPath, $directory.'/'))) {
                return $normalizedPath;
            }
        }

        throw new InvalidArgumentException('Restore path is outside the approved directories.');
    }

    /**
     * The restore payload must be a JSON encoded array of source lines.
     * We rebuild the file content from validated strings instead of writing raw input directly.
     */
    protected function buildValidatedRestoreContent(string $sourceCode, string $extension): string
    {
        if (str_contains($sourceCode, "\0")) {
            throw new InvalidArgumentException('Restore content contains invalid binary data.');
        }

        $decoded = json_decode($sourceCode, true);
        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Restore content must be a valid JSON array.');
        }

        $content = '';
        foreach ($decoded as $line) {
            if (! is_string($line) || str_contains($line, "\0")) {
                throw new InvalidArgumentException('Restore content contains invalid lines.');
            }

            $content .= $line;
        }

        if ($content === '') {
            throw new InvalidArgumentException('Restore content cannot be empty.');
        }

        if ($extension === 'php') {
            if (! str_starts_with(ltrim($content), '<?php')) {
                throw new RuntimeException('Restore PHP files must start with an opening PHP tag.');
            }

            if (@token_get_all($content) === false) {
                throw new RuntimeException('Restore PHP content could not be parsed safely.');
            }
        }

        if ($extension === 'json') {
            json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Restore JSON content is invalid.');
            }
        }

        return $content;
    }
}
