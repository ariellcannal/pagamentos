<?php

declare(strict_types=1);

namespace App\Support;

final class Bootstrap
{
    private static bool $loaded = false;

    public static function loadEnv(string $testsRoot): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = $testsRoot . DIRECTORY_SEPARATOR . '.env';
        $fallback = $testsRoot . DIRECTORY_SEPARATOR . '.env.example';
        $source = file_exists($envFile) ? $envFile : $fallback;

        if (!file_exists($source)) {
            self::$loaded = true;
            return;
        }

        $lines = file($source, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$k, $v] = explode('=', $line, 2);
            $key = trim($k);
            $val = trim($v);
            if ($val !== '' && (($val[0] === '"' && substr($val, -1) === '"') || ($val[0] === "'" && substr($val, -1) === "'"))) {
                $val = substr($val, 1, -1);
            }

            if (getenv($key) === false) {
                putenv($key . '=' . $val);
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }

        self::$loaded = true;
    }

    public static function assertDevelopmentOnly(): void
    {
        $appEnv = strtolower((string) (getenv('APP_ENV') ?: getenv('ENVIRONMENT') ?: 'production'));
        if ($appEnv !== 'development') {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'erro' => 'A aplicacao de sandbox so pode funcionar em DEVELOPMENT.',
            ], JSON_UNESCAPED_UNICODE);
            exit(1);
        }
    }
}
