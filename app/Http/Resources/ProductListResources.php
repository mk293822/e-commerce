<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResources extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'slug'=>$this->slug,
            'price'=>$this->getFirstPrice(),
            'quantity'=>$this->quantity,
            'discount'=>$this->discount,
            'image'=> $this->getFirstImageUrl(),
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
        ];
    }
}
