<?php
namespace App\Services;


use App\Models\CartItems;
use App\Models\Product;
use App\Models\VariationTypeOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class CartServices
{
    private ?array $cachedItems = null;
    protected const COOKIE_LIFETIME = 60 * 24;
    protected const COOKIE_NAME = 'CartItems';



//    Get methods
    public function getCartItems(): array
    {
        try{
            if($this->cachedItems === null){
                if(Auth::check()){
                    $items = $this->getItemFromDatabase();
                } else {
                    $items = $this->getItemFromCookie();
                }

                $product_ids = collect($items)->pluck('product_id')->toArray();
                $products = Product::query()->whereIn('id', $product_ids)->with('user.vendor')->forWebsite()->get()->keyBy('id');

                $cartItems = [];

                foreach ($items as $item) {
                    $product = data_get($products, $item['product_id'], null);

                    if(!$product) continue;

                    $option_Infos = [];
                    $options = VariationTypeOption::query()->whereIn('id', $item['option_ids'])->get()->keyBy('id');

                    $image_url = null;
                    foreach ($item['option_ids'] as $option_id) {
                        $option = data_get($options, $option_id, null);
                        if(!$image_url){
                            $image_url = $option->getFirstMediaUrl('images', 'small');
                        }
                        $option_Infos[] = [
                            'id'=>$option->id,
                            'name'=>$option->name,
                            'variation_type'=> [
                                'id'=>$option->variation_type_id,
                                'name'=>$option->variationType->name,
                                'type'=>$option->variationType->type,
                            ],
                        ];
                    }

                    $cartItems[] = [
                        'id'=>$product->id,
                        'name'=>$product->name,
                        'slug'=>$product->slug,
                        'image_url'=>$image_url ?: $product->getFirstMediaUrl('images', 'small'),
                        'discount'=>$product->discount,
                        'quantity'=>$item['quantity'],
                        'price'=>$item['price'],
                        'options'=>$option_Infos,
                        'option_ids'=>$item['option_ids'],
                        'user'=>[
                            'id'=>$product->user->id,
                            'name'=>$product->user->name,
                            'store_name'=>$product->user->vendor->store_name,
                            'email'=>$product->user->email,
                        ]
                    ];

                }

                return $this->cachedItems = $cartItems;
            }
            return $this->cachedItems;
        } catch(\Exception $e){
            Log::error($e->getMessage(). PHP_EOL . $e->getTraceAsString());
        }
        return [];
    }

    public function GetItemGrouped()
    {
        return collect($this->getCartItems())->groupBy(fn($item)=> $item['user']['id'])
            ->map(fn($item) => [
                'user'=>$item->first()['user'],
                'items' => $item->toArray(),
                'total_price' => $item->sum('price'),
                'total_quantity' => $item->sum('quantity'),
            ]
            )->toArray();
    }

    protected function GetItemFromCookie()
    {
        $items = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);

        return $items;
    }

    protected function GetItemFromDatabase()
    {
        $items = CartItems::query()->where('user_id', Auth::id())->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'option_ids' =>json_decode($item->option_ids, true),
            ];
        })->toArray();

        return $items;
    }

    public function GetTotalPrice(): float
    {
        $total_price = 0;
        foreach($this->getCartItems() as $item){
            $total_price += $item['price'] * $item['quantity'];
        }
        return $total_price;
    }

    public function GetTotalQuantity(): int
    {
        $total_quantity = 0;
        foreach($this->getCartItems() as $item){
            $total_quantity += $item['quantity'];
        }
        return $total_quantity;
    }



//    Add Methods
    public function AddItemToCart(Product $product, $quantity, $option_ids)
    {
        if(!$option_ids){
            $option_ids =  $product->getFirstOptionMap();
        }

        $price = $product->getPriceForOption($option_ids);

        if(Auth::check()){
            $this->AddItemToDatabase($product->id, $quantity, $price, $option_ids);
        } else{
            $this->AddItemToCookie($product->id, $quantity, $price, $option_ids);
        }
    }

    protected function AddItemToCookie($product_id, $quantity, $price, $option_ids)
    {
        $existing_items = $this->GetItemFromCookie();
        ksort($option_ids);

        $item_key = $product_id . '_'. json_encode($option_ids);

        if(isset($existing_items[$item_key])){
            $existing_items[$item_key]['quantity'] += $quantity;
            $existing_items[$item_key]['price'] = $price;
        } else {
            $existing_items[$item_key] = [
                'id'=> Str::uuid(),
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price,
                'option_ids' => $option_ids
            ];
        }

        Cookie::queue(self::COOKIE_NAME, json_encode($existing_items), self::COOKIE_LIFETIME);

    }

    protected function AddItemToDatabase($product_id, $quantity, $price, $option_ids)
    {
        ksort($option_ids);
        $existing_items = CartItems::query()
            ->where('user_id', Auth::id())
            ->where('product_id', $product_id)->first();

            if($existing_items && json_decode($existing_items->option_ids, true) === $option_ids){
                $existing_items->update([
                   'quantity' => DB::raw('quantity + ' . $quantity),
                    'price' => $price,
                ]);
            } else{
                CartItems::create([
                   'product_id' => $product_id,
                   'user_id' => Auth::id(),
                   'quantity' => $quantity,
                   'price' => $price,
                    'option_ids' => json_encode($option_ids)
                ]);
            }


    }



//    Update Methods

    public function UpdateQuantity($product_id, $quantity, $option_ids)
    {
        if(Auth::check()){
            $user_id = Auth::id();
            $this->UpdateQuantityInDatabase($product_id, $option_ids, $user_id, $quantity);
        }  else {
            $this->UpdateQuantityInCookie($product_id, $option_ids, $quantity);
        }
    }

    protected function UpdateQuantityInDatabase($product_id, $option_ids, $user_id, $quantity)
    {
        ksort($option_ids);
        $existing_items = CartItems::query()
            ->where('user_id', $user_id)
            ->where('product_id', $product_id)->get();


        foreach($existing_items as $item){
            $existing_option_ids = json_decode($item->option_ids, true);
            if($existing_option_ids === $option_ids){
                $item->update([
                    'quantity' => DB::raw($quantity),
                ]);
            }
        }
    }

    protected function UpdateQuantityInCookie($product_id, $option_ids, $quantity)
    {
        $existing_items = $this->GetItemFromCookie();
        ksort($option_ids);
        $item_key = $product_id . '_'. json_encode($option_ids);
        if(isset($existing_items[$item_key])){
            $existing_items[$item_key]['quantity'] = $quantity;
        }
        Cookie::queue(self::COOKIE_NAME, json_encode($existing_items), self::COOKIE_LIFETIME);
    }



//    Remove methods
    public function RemoveCartItems($product_id, $option_ids)
    {
      if(Auth::check()){
          $user_id = Auth::id();
          $this->RemoveCartItemFromDatabase($product_id, $option_ids, $user_id);
      }  else {
          $this->RemoveCartItemFromCookie($product_id, $option_ids);
      }
    }

    protected function RemoveCartItemFromDatabase($product_id, $option_ids, $user_id)
    {
        $cart_item = CartItems::query()
            ->where('user_id', $user_id)
            ->where('product_id', $product_id)->get();

        foreach($cart_item as $item){
            $existing_option_ids = json_decode($item->option_ids, true);
            if($existing_option_ids === $option_ids){
                $item->delete();
            }
        }
    }

    protected function RemoveCartItemFromCookie($product_id, $option_ids = [])
    {
        ksort($option_ids);

        $item_key = $product_id . '_'. json_encode($option_ids);
        $items = $this->GetItemFromCookie();

        unset($items[$item_key]);

        Cookie::queue(self::COOKIE_NAME, json_encode($items), self::COOKIE_LIFETIME);
    }

//  Add Item to database to from cookie if user authenticate

    public function AddItemFromCookieToDatabase($user_id)
    {
        $items = $this->GetItemFromCookie();

        foreach ($items as $item) {
            $existing_items = CartItems::query()
                ->where('user_id', $user_id)
                ->where('product_id', $item['product_id'])
                ->get();
            if($existing_items->count() > 0) {
                foreach ($existing_items as $existing_item) {
                    $v_t_o_ids = json_decode($existing_item->option_ids, true);
                    if ($v_t_o_ids === $items['option_ids']) {
                        $existing_item->update([
                            'quantity' => $existing_item->quantity + $item['quantity'],
                            'price' => $item['price'],
                        ]);
                    } else {
                        CartItems::create([
                            'user_id' => $user_id,
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'price' => $item['price'],
                            'option_ids' => json_encode($item['option_ids'])
                        ]);
                    }
                }
            }else {
                CartItems::create([
                    'user_id' => $user_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'option_ids' => json_encode($item['option_ids'])
                ]);
            }

        }

        Cookie::queue(self::COOKIE_NAME, '', -1);

    }
}
