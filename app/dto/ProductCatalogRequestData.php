<?php
/**
 * Typed storefront catalog request data.
 */
class ProductCatalogRequestData {
    public function __construct(
        private string $category = '',
        private string $search = '',
        private string $sort = ProductService::SORT_NEWEST,
        private ?float $minPrice = null,
        private ?float $maxPrice = null,
        private int $page = 1,
        private int $limit = ITEMS_PER_PAGE,
        private bool $useCategoryPath = false,
    ) {
        $this->page = max(1, $page);
        $this->limit = max(1, $limit);
    }

    /**
     * Build a typed request object from the current HTTP request.
     */
    public static function fromRequest(Request $request, array $overrides = []): self {
        return self::fromArray(array_replace([
            'category' => $request->string('category'),
            'search' => $request->string('search'),
            'sort' => $request->string('sort', ProductService::SORT_NEWEST),
            'min_price' => $request->float('min_price'),
            'max_price' => $request->float('max_price'),
            'page' => $request->integer('page', 1),
            'limit' => ITEMS_PER_PAGE,
            'use_category_path' => false,
        ], $overrides));
    }

    /**
     * Build a typed request object from an array payload.
     */
    public static function fromArray(array $data): self {
        $minPrice = isset($data['min_price']) && is_numeric($data['min_price'])
            ? (float)$data['min_price']
            : null;
        $maxPrice = isset($data['max_price']) && is_numeric($data['max_price'])
            ? (float)$data['max_price']
            : null;

        return new self(
            trim((string)($data['category'] ?? '')),
            trim((string)($data['search'] ?? '')),
            trim((string)($data['sort'] ?? ProductService::SORT_NEWEST)),
            $minPrice,
            $maxPrice,
            max(1, (int)($data['page'] ?? 1)),
            max(1, (int)($data['limit'] ?? ITEMS_PER_PAGE)),
            (bool)($data['use_category_path'] ?? false),
        );
    }

    /**
     * Convert request data to service filter payload.
     */
    public function toServiceFilters(): array {
        return [
            'category' => $this->category,
            'search' => $this->search,
            'sort' => $this->sort,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }

    public function category(): string {
        return $this->category;
    }

    public function search(): string {
        return $this->search;
    }

    public function sort(): string {
        return $this->sort;
    }

    public function minPrice(): ?float {
        return $this->minPrice;
    }

    public function maxPrice(): ?float {
        return $this->maxPrice;
    }

    public function page(): int {
        return $this->page;
    }

    public function limit(): int {
        return $this->limit;
    }

    public function usesCategoryPath(): bool {
        return $this->useCategoryPath;
    }
}
