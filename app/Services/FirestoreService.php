<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Factory as FirebaseFactory;
use RuntimeException;
use Throwable;

class FirestoreService
{
    private const FIRESTORE_SCOPE = 'https://www.googleapis.com/auth/datastore';
    private const API_BASE = 'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents';
    private const RUN_QUERY_ENDPOINT = 'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents:runQuery';

    private string $projectId;
    private string $baseUrl;
    private ?string $accessToken = null;
    private ?float $tokenExpiry = null;
    /** @var array<string, mixed> */
    private array $credentialsData = [];
    private bool $initialized = false;

    public function __construct()
    {}

    /** @return array<int, array<string, mixed>> */
    public function all(string $collection, ?int $limit = null): array
    {
        $params = [];
        if ($limit) {
            $params['pageSize'] = $limit;
        }

        $response = $this->get($this->collectionPath($collection), $params);

        if (! $response->ok()) {
            Log::error('Firestore all() error', ['status' => $response->status(), 'body' => $response->body()]);
            return [];
        }

        $data = $response->json();
        $results = [];

        foreach ($data['documents'] ?? [] as $document) {
            $results[] = $this->parseDocument($document);
        }

        return $results;
    }

    /** @return array<string, mixed>|null */
    public function find(string $collection, string $id): ?array
    {
        $response = $this->get($this->documentPath($collection, $id));

        if ($response->status() === 404) {
            return null;
        }

        if (! $response->ok()) {
            Log::error('Firestore find() error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return $this->parseDocument($response->json());
    }

    /** @param array<string, mixed> $data */
    /** @return array<string, mixed> */
    public function add(string $collection, array $data): array
    {
        $data['created_at'] = now()->toIso8601String();

        $response = $this->post($this->collectionPath($collection), ['fields' => $this->encodeFields($data)]);

        if (! $response->ok() && ! $response->created()) {
            Log::error('Firestore add() error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Gagal menambahkan dokumen ke Firestore.');
        }

        return $this->parseDocument($response->json());
    }

    /** @param array<string, mixed> $data */
    public function set(string $collection, string $id, array $data): void
    {
        $response = $this->patch($this->documentPath($collection, $id), ['fields' => $this->encodeFields($data)]);

        if (! $response->ok()) {
            Log::error('Firestore set() error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Gagal menyimpan dokumen ke Firestore.');
        }
    }

    /** @param array<string, mixed> $data */
    public function update(string $collection, string $id, array $data): void
    {
        $params = [];
        foreach (array_keys($data) as $key) {
            $params[] = 'updateMask.fieldPaths=' . $key;
        }

        $response = $this->patch(
            $this->documentPath($collection, $id) . '?' . implode('&', $params),
            ['fields' => $this->encodeFields($data)],
        );

        if (! $response->ok()) {
            Log::error('Firestore update() error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Gagal mengupdate dokumen di Firestore.');
        }
    }

    public function delete(string $collection, string $id): void
    {
        $response = $this->deleteRequest($this->documentPath($collection, $id));

        if (! $response->ok() && $response->status() !== 404) {
            Log::error('Firestore delete() error', ['status' => $response->status(), 'body' => $response->body()]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function where(string $collection, string $field, string $operator, mixed $value, ?int $limit = null): array
    {
        $operatorMap = [
            '=' => 'EQUAL',
            '==' => 'EQUAL',
            '!=' => 'NOT_EQUAL',
            '<' => 'LESS_THAN',
            '<=' => 'LESS_THAN_OR_EQUAL',
            '>' => 'GREATER_THAN',
            '>=' => 'GREATER_THAN_OR_EQUAL',
            'in' => 'IN',
            'array-contains' => 'ARRAY_CONTAINS',
        ];

        $firestoreOperator = $operatorMap[$operator] ?? 'EQUAL';

        $structuredQuery = [
            'from' => [['collectionId' => $collection]],
            'where' => [
                'fieldFilter' => [
                    'field' => ['fieldPath' => $field],
                    'op' => $firestoreOperator,
                    'value' => $this->encodeValue($value),
                ],
            ],
        ];

        if ($limit) {
            $structuredQuery['limit'] = $limit;
        }

        $response = $this->post(sprintf(self::RUN_QUERY_ENDPOINT, $this->projectId), ['structuredQuery' => $structuredQuery]);

        if (! $response->ok()) {
            Log::error('Firestore where() error', ['status' => $response->status(), 'body' => $response->body()]);
            return [];
        }

        $results = [];
        foreach ($response->json() as $item) {
            if (isset($item['document'])) {
                $results[] = $this->parseDocument($item['document']);
            }
        }

        return $results;
    }

    public function auth(): FirebaseAuth
    {
        $this->initializeIfNeeded();

        return $this->buildFactory()->createAuth();
    }

    /** @param array<string, mixed> $doc */
    /** @return array<string, mixed> */
    private function parseDocument(array $doc): array
    {
        $name = (string) ($doc['name'] ?? '');
        $parts = explode('/', $name);
        $id = (string) end($parts);

        $fields = $doc['fields'] ?? [];
        $data = ['id' => $id];

        foreach ($fields as $key => $typedValue) {
            $data[$key] = $this->decodeValue($typedValue);
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    /** @return array<string, mixed> */
    private function encodeFields(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[$key] = $this->encodeValue($value);
        }
        return $fields;
    }

    /** @return array<string, mixed> */
    private function encodeValue(mixed $value): array
    {
        if ($value === null) {
            return ['nullValue' => null];
        }
        if (is_bool($value)) {
            return ['booleanValue' => $value];
        }
        if (is_int($value)) {
            return ['integerValue' => (string) $value];
        }
        if (is_float($value)) {
            return ['doubleValue' => $value];
        }
        if (is_array($value)) {
            if (array_is_list($value)) {
                return ['arrayValue' => ['values' => array_map(fn($v) => $this->encodeValue($v), $value)]];
            }
            return ['mapValue' => ['fields' => $this->encodeFields($value)]];
        }
        return ['stringValue' => (string) $value];
    }

    /** @param array<string, mixed> $typedValue */
    private function decodeValue(array $typedValue): mixed
    {
        if (isset($typedValue['stringValue'])) {
            return $typedValue['stringValue'];
        }
        if (isset($typedValue['integerValue'])) {
            return (int) $typedValue['integerValue'];
        }
        if (isset($typedValue['doubleValue'])) {
            return (float) $typedValue['doubleValue'];
        }
        if (isset($typedValue['booleanValue'])) {
            return (bool) $typedValue['booleanValue'];
        }
        if (array_key_exists('nullValue', $typedValue)) {
            return null;
        }
        if (isset($typedValue['arrayValue'])) {
            return array_map(fn($v) => $this->decodeValue($v), $typedValue['arrayValue']['values'] ?? []);
        }
        if (isset($typedValue['mapValue'])) {
            $result = [];
            foreach ($typedValue['mapValue']['fields'] ?? [] as $k => $v) {
                $result[$k] = $this->decodeValue($v);
            }
            return $result;
        }
        if (isset($typedValue['timestampValue'])) {
            return $typedValue['timestampValue'];
        }

        return null;
    }

    private function getAccessToken(): string
    {
        $this->initializeIfNeeded();

        if ($this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }

        $credentials = new ServiceAccountCredentials([self::FIRESTORE_SCOPE], $this->credentialsData);

        try {
            $token = $credentials->fetchAuthToken();
        } catch (Throwable $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'invalid_grant') || str_contains($message, 'Invalid JWT Signature')) {
                throw new RuntimeException('Kredensial Firebase Admin tidak valid. Buat ulang service account key dari Firebase Console lalu update FIREBASE_CREDENTIALS.');
            }

            throw new RuntimeException('Gagal mengambil access token Firestore: ' . $message);
        }

        $accessToken = $token['access_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Gagal mengambil access token Firestore.');
        }

        $this->accessToken = $accessToken;
        $this->tokenExpiry = time() + ($token['expires_in'] ?? 3500);

        return $this->accessToken;
    }

    /** @return array<string, mixed> */
    private function resolveCredentials(): array
    {
        $credentials = config('services.firebase.credentials');

        if (! $credentials) {
            throw new RuntimeException('FIREBASE_CREDENTIALS belum di-set di file .env.');
        }

        if (is_string($credentials) && file_exists($credentials)) {
            return $this->decodeJsonFromFile($credentials);
        }

        if (is_string($credentials)) {
            $storagePath = storage_path($credentials);
            if (file_exists($storagePath)) {
                return $this->decodeJsonFromFile($storagePath);
            }
        }

        if (is_string($credentials)) {
            $basePath = base_path($credentials);
            if (file_exists($basePath)) {
                return $this->decodeJsonFromFile($basePath);
            }
        }

        if (is_string($credentials)) {
            $decoded = json_decode($credentials, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        throw new RuntimeException('FIREBASE_CREDENTIALS tidak valid.');
    }

    /** @return array<string, mixed> */
    private function decodeJsonFromFile(string $path): array
    {
        $json = file_get_contents($path);
        $decoded = is_string($json) ? json_decode($json, true) : null;

        if (! is_array($decoded)) {
            throw new RuntimeException(sprintf('File kredensial Firebase tidak valid: %s', $path));
        }

        return $decoded;
    }

    private function buildFactory(): FirebaseFactory
    {
        return (new FirebaseFactory())->withServiceAccount($this->credentialsData);
    }

    private function collectionPath(string $collection): string
    {
        $this->initializeIfNeeded();

        return $this->baseUrl . '/' . $collection;
    }

    private function documentPath(string $collection, string $id): string
    {
        return $this->collectionPath($collection) . '/' . $id;
    }

    private function get(string $url, array $query = []): Response
    {
        return Http::withToken($this->getAccessToken())->get($url, $query);
    }

    private function post(string $url, array $payload): Response
    {
        return Http::withToken($this->getAccessToken())->post($url, $payload);
    }

    private function patch(string $url, array $payload): Response
    {
        return Http::withToken($this->getAccessToken())->patch($url, $payload);
    }

    private function deleteRequest(string $url): Response
    {
        return Http::withToken($this->getAccessToken())->delete($url);
    }

    private function initializeIfNeeded(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->credentialsData = $this->resolveCredentials();
        $this->projectId = (string) ($this->credentialsData['project_id'] ?? config('services.firebase.project_id'));

        if ($this->projectId === '') {
            throw new RuntimeException('project_id Firebase tidak ditemukan pada credentials.');
        }

        $this->baseUrl = sprintf(self::API_BASE, $this->projectId);
        $this->initialized = true;
    }
}
