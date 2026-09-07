<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * @var list<string>
     */
    private const ACTIVITY_FIELDS = [
        'name_ar',
        'name_en',
        'slug',
        'description_ar',
        'description_en',
        'ordering',
        'is_active',
    ];

    public function __construct(public ActivityLogService $activityLogService) {}

    public function getPaginatedProducts(
        string $search = '',
        string $sortBy = 'ordering',
        string $sortDir = 'asc',
        int $perPage = 15
    ): LengthAwarePaginator {
        return Product::query()
            ->with('attachment')
            ->when($search, fn ($q) => $q->where(function ($query) use ($search) {
                $query->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description_ar', 'like', "%{$search}%")
                    ->orWhere('description_en', 'like', "%{$search}%");
            }))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function orderingQuery(): Builder
    {
        return Product::query();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data, UploadedFile $image): Product
    {
        return DB::transaction(function () use ($data, $image) {
            $orderingQuery = $this->orderingQuery();

            if (! array_key_exists('ordering', $data) || $data['ordering'] === null) {
                $data['ordering'] = nextOrdering(model: $orderingQuery);
            } else {
                $data['ordering'] = (int) $data['ordering'];
                shiftOrdering(model: $orderingQuery, from: $data['ordering'], direction: 'up');
            }

            $data['slug'] = $this->uniqueSlug(
                source: (string) ($data['slug'] ?? $data['name_en'] ?? $data['name_ar'] ?? 'product'),
            );

            $product = Product::create($data);
            $this->storeImage(product: $product, image: $image);

            $this->activityLogService->recordCreated(
                subject: $product,
                allowedFields: self::ACTIVITY_FIELDS,
                subjectLabel: $this->subjectLabel($product),
            );

            return $product;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($product, $data, $image) {
            $originalValues = $product->only(self::ACTIVITY_FIELDS);
            $orderingQuery = $this->orderingQuery();
            $oldOrdering = $product->ordering;
            $newOrdering = (int) ($data['ordering'] ?? $oldOrdering);

            if ($newOrdering !== $oldOrdering) {
                if ($newOrdering < $oldOrdering) {
                    shiftOrdering(model: $orderingQuery, from: $newOrdering, direction: 'up', to: $oldOrdering - 1, excludeId: $product->id);
                } else {
                    shiftOrdering(model: $orderingQuery, from: $oldOrdering, direction: 'down', to: $newOrdering, excludeId: $product->id);
                }
            }

            $slugSource = (string) ($data['slug'] ?? $product->slug ?? $data['name_en'] ?? $data['name_ar'] ?? 'product');
            $data['slug'] = $this->uniqueSlug(source: $slugSource, ignoreId: $product->id);

            $product->update($data);

            if ($image) {
                $this->deleteImage(product: $product);
                $this->storeImage(product: $product, image: $image);
            }

            $this->activityLogService->recordChanges(
                subject: $product,
                originalValues: $originalValues,
                allowedFields: self::ACTIVITY_FIELDS,
                subjectLabel: $this->subjectLabel($product),
            );

            return $product;
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $this->activityLogService->recordDeleted(
                subject: $product,
                allowedFields: self::ACTIVITY_FIELDS,
                subjectLabel: $this->subjectLabel($product),
            );

            $this->deleteImage(product: $product);
            $ordering = $product->ordering;
            $product->delete();

            shiftOrdering(model: $this->orderingQuery(), from: $ordering, direction: 'down');
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function mapForPublic(Product $product): array
    {
        return [
            'slug' => $product->slug,
            'name_ar' => $product->name_ar,
            'name_en' => $product->name_en,
            'description_ar' => $product->description_ar,
            'description_en' => $product->description_en,
            'excerpt_ar' => $this->excerpt($product->description_ar),
            'excerpt_en' => $this->excerpt($product->description_en),
            'image' => $product->attachment?->asset_path,
        ];
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'product';
        }

        $slug = $base;
        $i = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function excerpt(?string $html): ?string
    {
        if (! $html) {
            return null;
        }

        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

        if ($text === '') {
            return null;
        }

        return Str::limit($text, 140);
    }

    private function subjectLabel(Product $product): string
    {
        return $product->name_ar ?: $product->name_en ?: 'Product';
    }

    private function storeImage(Product $product, UploadedFile $image): void
    {
        $path = $image->store('products', 'public');
        $product->attachment()->create([
            'name' => $image->getClientOriginalName(),
            'path' => $path,
        ]);
    }

    private function deleteImage(Product $product): void
    {
        $attachment = $product->attachment;
        if ($attachment && $attachment->path && Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }
        if ($attachment) {
            $attachment->delete();
        }
    }
}
