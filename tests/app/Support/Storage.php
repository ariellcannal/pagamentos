<?php

declare(strict_types=1);

namespace App\Support;

use PDO;

final class Storage
{
    private static ?PDO $pdo = null;

    public static function init(string $testsRoot): void
    {
        if (self::$pdo instanceof PDO) {
            return;
        }

        $dbPath = (string) (getenv('DB_PATH') ?: './writable/sandbox.sqlite');
        if (!preg_match('/^[a-zA-Z]:\\\\|^\//', $dbPath)) {
            $dbPath = $testsRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\\\'], DIRECTORY_SEPARATOR, $dbPath);
        }

        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        self::$pdo = new PDO('sqlite:' . $dbPath);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = $testsRoot . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'schema.sql';
        if (file_exists($schema)) {
            $sql = file_get_contents($schema);
            if (is_string($sql) && trim($sql) !== '') {
                self::$pdo->exec($sql);
            }
        }
    }

    public static function insertTransacao(array $row): void
    {
        $stmt = self::$pdo?->prepare('INSERT INTO transacoes (banco, operadora_id, status, forma, valor, payload_request_json, payload_response_json) VALUES (:banco, :operadora_id, :status, :forma, :valor, :payload_request_json, :payload_response_json)');
        $stmt?->execute([
            ':banco' => $row['banco'] ?? null,
            ':operadora_id' => $row['operadora_id'] ?? null,
            ':status' => $row['status'] ?? null,
            ':forma' => $row['forma'] ?? null,
            ':valor' => $row['valor'] ?? null,
            ':payload_request_json' => $row['payload_request_json'] ?? null,
            ':payload_response_json' => $row['payload_response_json'] ?? null,
        ]);
    }

    public static function insertWebhook(array $row): void
    {
        $stmt = self::$pdo?->prepare('INSERT INTO webhooks_log (banco, evento, payload_json, headers_json, status_processamento, erro_mensagem) VALUES (:banco, :evento, :payload_json, :headers_json, :status_processamento, :erro_mensagem)');
        $stmt?->execute([
            ':banco' => $row['banco'] ?? null,
            ':evento' => $row['evento'] ?? null,
            ':payload_json' => $row['payload_json'] ?? null,
            ':headers_json' => $row['headers_json'] ?? null,
            ':status_processamento' => $row['status_processamento'] ?? 'processado',
            ':erro_mensagem' => $row['erro_mensagem'] ?? null,
        ]);
    }

    public static function ultimasTransacoes(int $limit = 20): array
    {
        $stmt = self::$pdo?->prepare('SELECT * FROM transacoes ORDER BY id DESC LIMIT :limit');
        $stmt?->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt?->execute();
        return $stmt?->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function ultimosWebhooks(int $limit = 20): array
    {
        $stmt = self::$pdo?->prepare('SELECT * FROM webhooks_log ORDER BY id DESC LIMIT :limit');
        $stmt?->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt?->execute();
        return $stmt?->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
