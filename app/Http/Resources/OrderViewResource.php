<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderViewResource extends JsonResource
{
    public static $wrap = false;
    public function toArray(Request $request)
    {
        return [
            'id'=> $this->id,
            'total_price'=>$this->total_amount,
            'status'=>$this->status,
            'created'=>$this->created_at->format('Y-m-d H:i:s'),
            'vendorUser'=>[
                'id'=>$this->vendorUser->id,
                'name'=>$this->vendorUser->name,
                'email'=>$this->vendorUser->email,
                'store_name'=>$this->vendorUser->vendor->store_name,
                'store_address'=>$this->vendorUser->vendor->store_address,
            ],
            'orderItems'=> $this->orderItems->map(fn ($item) => [
                'id'=>$item->product->id,
                'title'=>$item->product->name,
                'slug'=>$item->product->slug,
                'description'=>$item->product->description,
                'image'=>$item->product->getImageForOptions($item->variation_typ_option_ids?: []),
            ])
        ];
    }
}
