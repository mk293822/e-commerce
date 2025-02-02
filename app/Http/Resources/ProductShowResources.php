<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use function Livewire\wrap;

class ProductShowResources extends JsonResource
{

    public static $wrap = false;
    public function toArray($request): array
    {

        $options = $request->get('options') ?: [];

        if ($options){
            $images = $this->getImagesForOptions($options);
        } else {
            $images = $this->getImages();
        }

        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'slug'=>$this->slug,
            'price'=>$this->price,
            'description'=> $this->description,
            'quantity'=>$this->quantity,
            'discount'=>$this->discount,
            'image'=> $this->getFirstImageUrl(),
            'images'=> $images->map(function($image){
                return [
                    'id'=>$image->id,
                    'thumb'=>$image->getUrl('thumb'),
                    'small'=>$image->getUrl('small'),
                    'large'=>$image->getUrl('large'),
                ];
            }),
            'created_by'=> [
                'id'=>$this->vendor_user->id,
                'name'=> $this->vendor_user->name,
                'store_name'=>$this->vendor_user->vendor->store_name,
                'store_address'=>$this->vendor_user->vendor->store_address,
            ],
            'department'=> [
                'id'=> $this->department->id,
                'name'=> $this->department->name,
                'slug'=>$this->department->slug,
            ],
            'variation_types'=>$this->variationType->map(function($variation){
                return [
                    'id'=>$variation->id,
                    'name'=>$variation->name,
                    'type'=>$variation->type,
                    'product_id'=>$variation->product_id,
                    'variation_type_options'=>$variation->variationTypeOption->map(function($option) use($variation){
                        return [
                            'id'=>$option->id,
                            'name'=>$option->name,
                            'type'=> $variation,
                            'images'=>$option->getMedia('images')->map(function($image){
                                return [
                                    'id'=>$image->id,
                                    'thumb'=>$image->getUrl('thumb'),
                                    'small'=>$image->getUrl('small'),
                                    'large'=>$image->getUrl('large'),
                                ];
                            })
                        ];
                    }),
                ];
            }),
            'product_variations'=>$this->productVariation->map(function($variation){
                return [
                    'id'=>$variation->id,
                    'product_id'=>$variation->product_id,
                    'price'=>$variation->price,
                    'quantity'=>$variation->quantity,
                    'variation_type_option_ids' => json_decode($variation->variation_type_option_ids),
                ];
            }),
            'meta_title'=>$this->meta_title,
            'meta_description'=>$this->meta_description,
        ];
    }
}
