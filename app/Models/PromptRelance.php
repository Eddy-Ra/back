<?php

namespace App\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use Psr\Http\Message\ResponseInterface;

class PromptRelance
{
    public static function list(int $page, int $perPage): array
    {
        $start = ($page - 1) * $perPage;
        $end = $start + $perPage - 1;

        $response = self::requestWithRetry('GET', '/rest/v1/test_prompts_relance', [
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
        $payload['language'] = $payload['language'] ?? 'fr';
        $payload['tags'] = $payload['tags'] ?? [];
        $payload['active'] = $payload['active'] ?? true;
        $payload['use_count'] = $payload['use_count'] ?? 0;
        $payload['last_used_at'] = $payload['last_used_at'] ?? null;
        $payload['created_by_email'] = $payload['created_by_email'] ?? null;

        $response = self::requestWithRetry('POST', '/rest/v1/test_prompts_relance', [
            'headers' => self::headers([
                'Prefer' => 'return=representation',
            ]),
            'json' => [$payload],
            'query' => [
                'select' => '*',
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true) ?: [];
        return $data[0] ?? [];
    }

    public static function updateById(int $id, array $payload): array
    {
        $response = self::requestWithRetry('PATCH', '/rest/v1/test_prompts_relance', [
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

    public static function deleteById(int $id): void
    {
        self::requestWithRetry('DELETE', '/rest/v1/test_prompts_relance', [
            'headers' => self::headers(),
            'query' => [
                'id' => 'eq.'.$id,
            ],
        ]);
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
}


