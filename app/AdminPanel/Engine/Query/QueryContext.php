<?php

namespace App\AdminPanel\Engine\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Immutable value object threaded through the QueryPipeline.
 * Each stage reads from and writes to this context.
 */
final class QueryContext
{
    /** @var array Arbitrary metadata stages can attach */
    public array $meta = [];

    public function __construct(
        public Builder $query,
        public Request $request,
        public array $schema,
    ) {}

    // ── Request helpers ──────────────────────────────────────────────

    /**
     * When set (e.g. "payments"), reads payments_page, payments_q, etc.
     * so multiple grids can coexist on one page.
     */
    private function queryKey(string $name): string
    {
        $prefix = $this->schema['query_prefix'] ?? null;

        return $prefix ? "{$prefix}_{$name}" : $name;
    }

    public function getActiveTab(): ?string
    {
        return $this->request->input($this->queryKey('tab'));
    }

    public function getSearchQuery(): ?string
    {
        $q = $this->request->input($this->queryKey('q'));
        return $q ? trim($q) : null;
    }

    /**
     * Returns all ?search[key]=value pairs (or payments_search[key] when prefixed).
     */
    public function getFilters(): array
    {
        return $this->request->input($this->queryKey('search'), []);
    }

    public function getSortBy(): ?string
    {
        return $this->request->input($this->queryKey('sort_by'));
    }

    public function getSortOrder(): string
    {
        $order = $this->request->input($this->queryKey('sort_order'), 'desc');
        return in_array($order, ['asc', 'desc']) ? $order : 'desc';
    }

    public function getPerPage(): int
    {
        $allowed = [10, 25, 50, 100];
        $pp = (int) $this->request->input($this->queryKey('per_page'), 25);
        return in_array($pp, $allowed) ? $pp : 25;
    }

    public function getPage(): int
    {
        return max(1, (int) $this->request->input($this->queryKey('page'), 1));
    }

    public function isExport(): bool
    {
        return $this->request->input($this->queryKey('export')) !== null;
    }

    public function getExportFormat(): string
    {
        return $this->request->input($this->queryKey('export'), 'csv');
    }
}
