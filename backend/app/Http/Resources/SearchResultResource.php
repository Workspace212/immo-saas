<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $result = is_array($this->resource) ? $this->resource : [];

        return [
            'entity' => $result['entity'] ?? null,
            'entity_type' => $result['entity_type'] ?? null,
            'id' => $result['id'] ?? null,
            'title' => $result['title'] ?? null,
            'subtitle' => $result['subtitle'] ?? null,
            'status' => $result['status'] ?? null,
            'url' => $result['url'] ?? null,
            'score' => $result['score'] ?? null,
            'highlight' => $result['highlight'] ?? null,
            'metadata' => $result['metadata'] ?? [],
        ];
    }
}
