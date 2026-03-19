<?php

namespace App\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use Psr\Http\Message\ResponseInterface;

class AiEmailResponse
{
    public static function list(int $page, int $perPage): array
    {
        $start = ($page - 1) * $perPage;
        $end = $start + $perPage - 1;

        $response = self::requestWithRetry('GET', '/rest/v1/test_email_relance', [
            'headers' => self::headers([
                'Range-Unit' => 'items',
                'Range' => $start.'-'.$end,
                'Prefer' => 'count=exact',
            ]),
            'query' => [
                'select' => '*',
                'order' => 'id.desc',
            ],
        ]);

        $items = json_decode((string) $response->getBody(), true) ?: [];
        $contentRange = $response->getHeaderLine('Content-Range');
        $total = null;
        if (preg_match('/\/(\d+)$/', $contentRange, $m)) {
            $total = (int) $m[1];
        }

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    public static function create(array $payload): array
    {
        unset($payload['created_at'], $payload['updated_at']);
        $payload['prospect_status'] = $payload['prospect_status'] ?? null;
        $payload['validated_by_admin'] = $payload['validated_by_admin'] ?? false;
        $payload['validated_at'] = $payload['validated_at'] ?? null;
        $payload['sent_at'] = $payload['sent_at'] ?? null;
        $payload['email_dispatched'] = $payload['email_dispatched'] ?? false;

        try {
            if (!isset($payload['id'])) {
                $payload['id'] = self::nextId('test_email_relance');
            }
            $response = self::requestWithRetry('POST', '/rest/v1/test_email_relance', [
                'headers' => self::headers([
                    'Prefer' => 'return=representation',
                ]),
                'json' => [$payload],
                'query' => [
                    'select' => '*',
                ],
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 409) {
                // recalculer un nouvel id puis retenter
                $payload['id'] = self::nextId('test_email_relance');
                $response = self::requestWithRetry('POST', '/rest/v1/test_email_relance', [
                    'headers' => self::headers([
                        'Prefer' => 'return=representation',
                    ]),
                    'json' => [$payload],
                    'query' => [
                        'select' => '*',
                    ],
                ]);
            } else {
                throw $e;
            }
        }

        $data = json_decode((string) $response->getBody(), true) ?: [];
        return $data[0] ?? [];
    }

    public static function updateById(int $id, array $payload): array
    {
        $response = self::requestWithRetry('PATCH', '/rest/v1/test_email_relance', [
            'headers' => self::headers([
                'Prefer' => 'return=representation',
            ]),
            'json' => $payload,
            'query' => [
                'id' => 'eq.'.$id,
                'select' => '*',
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true) ?: [];
        return $data[0] ?? [];
    }

    private static function headers(array $extra = []): array
    {
        return array_merge([
            'apikey' => self::serviceKey(),
            'Authorization' => 'Bearer '.self::serviceKey(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $extra);
    }

    private static function requestWithRetry(string $method, string $uri, array $options = []): ResponseInterface
    {
        $http = new Client([
            'base_uri' => rtrim((string) env('SUPABASE_URL'), '/'),
            'timeout' => 10,
            'curl' => [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
        ]);

        $maxAttempts = 3;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return $http->request($method, $uri, $options);
            } catch (GuzzleConnectException $e) {
                if ($attempt === $maxAttempts) {
                    throw $e;
                }
                usleep(100000 * $attempt * $attempt);
            }
        }
        throw new \RuntimeException('Unexpected HTTP request failure');
    }

    private static function serviceKey(): string
    {
        return (string) env('SUPABASE_SERVICE_ROLE_KEY');
    }

    private static function resetSequence(string $tableName): void
    {
        try {
            $sql = "SELECT setval(pg_get_serial_sequence('".$tableName."', 'id'), (SELECT COALESCE(MAX(id),0) FROM " . $tableName . ") + 1, false)";
            self::requestWithRetry('POST', '/rest/v1/rpc/exec_sql', [
                'headers' => self::headers(),
                'json' => [
                    'query' => $sql,
                ],
            ]);
        } catch (\Throwable $t) {
            // ignore, we will surface the original error if retry fails
        }
    }

    private static function nextId(string $tableName): int
    {
        $response = self::requestWithRetry('GET', '/rest/v1/' . $tableName, [
            'headers' => self::headers([
                'Prefer' => 'count=exact',
            ]),
            'query' => [
                'select' => 'id',
                'order' => 'id.desc',
                'limit' => 1,
            ],
        ]);
        $items = json_decode((string) $response->getBody(), true) ?: [];
        $maxId = isset($items[0]['id']) ? (int) $items[0]['id'] : 0;
        return $maxId + 1;
    }
}


