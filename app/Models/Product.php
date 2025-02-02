<?php

namespace App\Models;

use App\Enums\ProductStatusEnums;
use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(100);
        $this->addMediaConversion('small')->width(480);
        $this->addMediaConversion('large')->width(1200);
    }

    public function scopeForVendor(Builder $query): Builder
    {
        return $query->where('created_by', auth()->user()->id);
    }
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('products.status', ProductStatusEnums::Published->value);
    }

    public function scopeForWebsite(Builder $query): Builder
    {
        return $query->published()->forVendorApproved();
    }
    public function scopeForVendorApproved(Builder $query): Builder
    {
        return $query->join('vendors', 'vendors.user_id', '=', 'products.created_by')
            ->where('vendors.status', VendorStatusEnum::Approved->value);
    }

    public function variationType(): HasMany
    {
        return $this->hasMany(VariationType::class);
    }

    public function vendor_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function productVariation(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class );
    }

    public function options(): HasManyThrough
    {
        return $this->hasManyThrough(
          VariationTypeOption::class,
          VariationType::class,
          'product_id',
          'variation_type_id',
          'id',
          'id'
        );
    }

    public function getFirstPrice()
    {
        $product_variation = $this->productVariation()->first();
        if ($product_variation) {
            return $product_variation->price;
        }
        return $this->price;
    }
    public function getFirstImageUrl($collection = 'images', $conversion = 'small'):string
    {
        if ($this->options->count() > 0) {
            foreach ($this->options as $option){
                $image_url = $option->getFirstMediaUrl($collection, $conversion);
                if ($image_url) {
                    return $image_url;
                }
            }
        }
        return $this->getFirstMediaUrl($collection, $conversion);
    }
    public function getFirstOptionMap(): array
    {
        return $this->variationType->mapWithKeys(fn($variation) => [$variation->id => $variation->variationTypeOption[0]?->id])->toArray();
    }
    public function getPriceForOption($option_ids = []): mixed
    {
        sort($option_ids);

        foreach ($this->productVariation as $variation) {
            $variation_type_option_ids = json_decode($variation->variation_type_option_ids);

            if ($variation_type_option_ids == $option_ids) {
                return $variation->price !== null && $variation->price > 0 ? $variation->price : $this->price;
            }
        }
        return $this->price;
    }
    public function getImagesForOptions($option_ids = []): mixed
    {
        sort($option_ids);

        if($option_ids){
            $options = VariationTypeOption::whereIn('id', $option_ids)->get();

            foreach ($options as $option) {
                if($option->variationType['type'] === 'image'){
                    $images = $option->getMedia('images');
                    if($images){
                        return $images;
                    }
                }
            }
        }

        return $this->getMedia('images');
    }
    public function getImageForOptions($option_ids = []): mixed
    {
        sort($option_ids);

        if($option_ids){
            $options = VariationTypeOption::whereIn('id', $option_ids)->get();

            foreach ($options as $option) {
                if($option->variationType['type'] === 'image'){
                    $images = $option->getFirstMediaUrl('images');
                    if($images){
                        return $images;
                    }
                }
            }
        }

        return $this->getFirstMediaUrl('images');
    }
    public function getImages()
    {
        if($this->options->count() > 0){
            foreach ($this->options as $option) {
                if($option->variationType['type'] === 'image'){
                    $images = $option->getMedia('images');
                    if($images){
                        return $images;
                    }
                }
            }
        }

        return $this->getMedia('images');
    }


}
