<?php

declare(strict_types=1);

namespace App\Controllers;

class BaseController
{
    protected object $request;
    protected object $response;

    public function __construct()
    {
        $this->request = new class {
            public function getJSON(bool $assoc = false): array
            {
                $body = file_get_contents('php://input') ?: '';
                if (trim($body) === '') {
                    return [];
                }

                $decoded = json_decode($body, true);
                return is_array($decoded) ? $decoded : [];
            }

            public function getPost(): array
            {
                return $_POST;
            }

            public function getBody(): string
            {
                return file_get_contents('php://input') ?: '';
            }

            public function headers(): array
            {
                if (function_exists('getallheaders')) {
                    return getallheaders() ?: [];
                }

                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (str_starts_with($name, 'HTTP_')) {
                        $header = str_replace('_', '-', strtolower(substr($name, 5)));
                        $headers[$header] = $value;
                    }
                }

                return $headers;
            }
        };

        $this->response = new class {
            public function setJSON(array $payload): string
            {
                return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        };
    }
}

if (!function_exists(__NAMESPACE__ . '\\view')) {
    function view(string $view, array $data = []): string
    {
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . $view . '.php';
        if (!file_exists($file)) {
            return 'View nao encontrada: ' . $view;
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }
}
