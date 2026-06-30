<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Agency;
use App\Models\ApiClient;
use App\Models\ApiToken;
use App\Models\ApiWebhook;
use App\Models\ApiWebhookLog;
use App\Models\Integration;
use App\Models\IntegrationLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IntegrationService
{
    public function createApiClient(array $data, ?User $user = null): ApiClient
    {
        return DB::transaction(function () use ($data, $user): ApiClient {
            $clientData = array_intersect_key($data, array_flip([
                'agency_id',
                'created_by',
                'name',
                'client_key',
                'secret',
                'permissions',
                'is_active',
                'notes',
            ]));

            $clientData['agency_id'] ??= $this->agencyIdFor($user);
            $clientData['created_by'] ??= $user?->getKey();
            $clientData['client_key'] ??= $this->generateApiClientKey();
            $clientData['secret'] ??= $this->generateApiClientSecret();
            $clientData['permissions'] ??= [];
            $clientData['is_active'] ??= true;

            // TODO: Hash or encrypt client secrets once the authentication strategy is finalized.
            return ApiClient::query()->create($clientData);
        });
    }

    public function createApiToken(ApiClient $client, array $data, ?User $user = null): ApiToken
    {
        return DB::transaction(function () use ($client, $data, $user): ApiToken {
            $tokenData = array_intersect_key($data, array_flip([
                'agency_id',
                'api_client_id',
                'user_id',
                'name',
                'token',
                'permissions',
                'expires_at',
                'last_accessed_at',
                'is_active',
            ]));

            $tokenData['agency_id'] ??= $client->agency_id;
            $tokenData['api_client_id'] = $client->getKey();
            $tokenData['user_id'] ??= $user?->getKey();
            $tokenData['name'] ??= 'API Token';
            $tokenData['token'] ??= $this->generateToken();
            $tokenData['permissions'] ??= $client->permissions ?? [];
            $tokenData['is_active'] ??= true;

            // TODO: Store a hashed token and return the plain token only once in the API layer.
            return ApiToken::query()->create($tokenData);
        });
    }

    public function revokeApiToken(ApiToken $token): ApiToken
    {
        return DB::transaction(function () use ($token): ApiToken {
            $token->fill([
                'is_active' => false,
            ]);
            $token->save();

            return $token->refresh();
        });
    }

    public function createWebhook(array $data, ?User $user = null): ApiWebhook
    {
        return DB::transaction(function () use ($data, $user): ApiWebhook {
            $webhookData = array_intersect_key($data, array_flip([
                'agency_id',
                'api_client_id',
                'name',
                'url',
                'event',
                'http_method',
                'secret',
                'headers',
                'is_active',
            ]));

            $webhookData['agency_id'] ??= $this->agencyIdFor($user);
            $webhookData['http_method'] ??= 'POST';
            $webhookData['secret'] ??= $this->generateApiClientSecret();
            $webhookData['headers'] ??= [];
            $webhookData['is_active'] ??= true;

            // TODO: Validate webhook URL ownership and supported event names before activation.
            return ApiWebhook::query()->create($webhookData);
        });
    }

    public function logWebhook(ApiWebhook $webhook, array $payload, ?array $response = null): ApiWebhookLog
    {
        return DB::transaction(function () use ($webhook, $payload, $response): ApiWebhookLog {
            return ApiWebhookLog::query()->create([
                'agency_id' => $webhook->agency_id,
                'api_webhook_id' => $webhook->getKey(),
                'event' => $webhook->event,
                'payload' => $payload,
                'response_body' => $response['body'] ?? null,
                'http_code' => $response['http_code'] ?? null,
                'duration_ms' => $response['duration_ms'] ?? null,
                'status' => $response['status'] ?? 'pending',
                'attempted_at' => Carbon::now(),
                'error_message' => $response['error_message'] ?? null,
            ]);
        });
    }

    public function activateIntegration(Integration $integration): Integration
    {
        return DB::transaction(function () use ($integration): Integration {
            $integration->fill(['is_active' => true]);
            $integration->save();

            $this->logIntegration($integration, 'activate', 'success');

            return $integration->refresh();
        });
    }

    public function deactivateIntegration(Integration $integration): Integration
    {
        return DB::transaction(function () use ($integration): Integration {
            $integration->fill(['is_active' => false]);
            $integration->save();

            $this->logIntegration($integration, 'deactivate', 'success');

            return $integration->refresh();
        });
    }

    public function updateIntegrationSettings(Integration $integration, array $settings): Integration
    {
        return DB::transaction(function () use ($integration, $settings): Integration {
            // TODO: Validate provider-specific settings and encrypt sensitive nested values.
            $integration->fill([
                'settings' => array_replace($integration->settings ?? [], $settings),
            ]);
            $integration->save();

            $this->logIntegration($integration, 'settings_updated', 'success', null, [
                'settings_keys' => array_keys($settings),
            ]);

            return $integration->refresh();
        });
    }

    public function logIntegration(
        Integration $integration,
        string $action,
        string $status,
        ?string $message = null,
        array $metadata = []
    ): IntegrationLog {
        return IntegrationLog::query()->create([
            'agency_id' => $integration->agency_id,
            'integration_id' => $integration->getKey(),
            'action' => $action,
            'result' => $status,
            'message' => $message,
            'context' => $metadata,
            'logged_at' => Carbon::now(),
        ]);
    }

    public function generateApiClientSecret(): string
    {
        return sprintf('sk_%s', Str::random(64));
    }

    public function generateToken(): string
    {
        return sprintf('tok_%s', Str::random(80));
    }

    public function dispatchWebhook(ApiWebhook $webhook, array $payload): ApiWebhookLog
    {
        // TODO: Perform the actual HTTP call, sign payloads, retry failures, and record timing.
        return $this->logWebhook($webhook, $payload, [
            'status' => $webhook->is_active ? 'pending' : 'skipped',
            'http_code' => null,
            'duration_ms' => null,
            'body' => null,
            'error_message' => $webhook->is_active ? null : 'Webhook is inactive.',
        ]);
    }

    public function testIntegration(Integration $integration): array
    {
        // TODO: Call the provider health-check endpoint and validate credentials.
        $this->logIntegration($integration, 'test', 'success', 'Placeholder integration test executed.');

        return [
            'integration_id' => $integration->getKey(),
            'provider' => $integration->provider,
            'integration_type' => $integration->integration_type,
            'status' => 'success',
            'message' => 'Integration test placeholder completed.',
            'tested_at' => Carbon::now()->toISOString(),
        ];
    }

    private function generateApiClientKey(): string
    {
        return sprintf('client_%s', Str::lower(Str::random(32)));
    }

    private function agencyIdFor(?User $user): ?int
    {
        if ($user === null) {
            return null;
        }

        if ($user->relationLoaded('agency') && $user->agency instanceof Agency) {
            return (int) $user->agency->getKey();
        }

        return $user->agency_id === null ? null : (int) $user->agency_id;
    }
}
