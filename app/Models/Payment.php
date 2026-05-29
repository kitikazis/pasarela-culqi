<?php

namespace App\Models;

use App\Exceptions\CulqiException;
use PDO;
use PDOException;
use Ramsey\Uuid\Uuid;

class Payment
{
    private PDO $pdo;

    public function __construct(array $dbConfig)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['name']
        );

        try {
            $this->pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }catch (PDOException $e) {
    throw new \RuntimeException(
        'DB Error: ' . $e->getMessage(),  // ← esto nos dirá exactamente qué falla
        503,
        $e);
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // ── Crear pago en estado pending ───────────────────────────

    public function create(array $data): string
    {
        $id = Uuid::uuid4()->toString();

        $sql = 'INSERT INTO payments 
                    (id, culqi_token_id, amount, currency, status, payment_method, email, description, metadata)
                VALUES 
                    (:id, :token_id, :amount, :currency, :status, :method, :email, :description, :metadata)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'          => $id,
            ':token_id'    => $data['token_id']       ?? null,
            ':amount'      => (int) $data['amount'],
            ':currency'    => $data['currency']        ?? 'PEN',
            ':status'      => 'pending',
            ':method'      => $data['payment_method']  ?? 'card',
            ':email'       => $data['email']           ?? null,
            ':description' => $data['description']     ?? null,
            ':metadata'    => isset($data['metadata']) ? json_encode($data['metadata']) : null,
        ]);

        return $id;
    }

    // ── Actualizar estado tras respuesta de Culqi ──────────────

    public function updateStatus(string $id, string $status, ?string $chargeId, ?array $culqiResponse): void
    {
        $sql = 'UPDATE payments 
                   SET status = :status, 
                       culqi_charge_id = :charge_id, 
                       culqi_response = :response
                 WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':status'    => $status,
            ':charge_id' => $chargeId,
            ':response'  => $culqiResponse ? json_encode($culqiResponse) : null,
            ':id'        => $id,
        ]);
    }

    // ── Buscar por ID ──────────────────────────────────────────

    public function findById(string $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM payments WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    // ── Verificar conexión (health check) ─────────────────────

    public function ping(): bool
    {
        $this->pdo->query('SELECT 1');
        return true;
    }
}