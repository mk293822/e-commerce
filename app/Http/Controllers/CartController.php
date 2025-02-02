<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use App\Services\CartServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Mockery\Exception;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CartServices $cartServices)
    {
        return Inertia::render('CartItem/Index',[
            'cartItems' => $cartServices->GetItemGrouped(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product, CartServices $cartServices)
    {
        $validated = $request->validate([
           'quantity' => 'required|integer',
           'option_ids'=>['nullable','array'],
        ]);

        $quantity = $validated['quantity'];
        $option_ids = $validated['option_ids'];

        $cartServices->AddItemToCart($product, $quantity, $option_ids ?: []);

        return back()->with('success','Product added to cart successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, CartServices $cartServices)
    {
        $option_ids = $request->get('option_ids', []);
        $quantity = $request->get('quantity');
        $cartServices->UpdateQuantity($id, $quantity, $option_ids ?: []);
        return back()->with('success','Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, CartServices $cartServices, Request $request)
    {
        $option_ids = $request->get('option_ids', []);
        $cartServices->RemoveCartItems($id, $option_ids ?: []);
        return back()->with('success','Product removed from cart successfully!');
    }

    /**
     * Checkout the cart Items
     */

    public function CheckOut(Request $request, CartServices $cartServices)
    {

        Stripe::setApiKey(config('app.stripe_secret_key'));

        $cart_items = $cartServices->GetItemGrouped();
        $vendor_id = $request->get('vendor_id', null);

        DB::beginTransaction();

        try {
            $checkout_items = $cart_items;
            if($vendor_id){
                $checkout_items = [$cart_items[$vendor_id]];
            }

            $line_items = [];
            $orders = [];

            foreach ($checkout_items as $checkout_item) {

                $user = $checkout_item['user'];
                $cart_items = $checkout_item['items'];


                $order = Order::create([
                   'user_id'=>$request->user()->id,
                    'vendor_user_id'=>$user['id'],
                    'status'=>OrderStatusEnum::Draft->value,
                    'total_amount'=>$checkout_item['total_price'],
                    'stripe_session_id'=>null,
                ]);

                $orders[] = $order;

                foreach ($cart_items as $cart_item) {
                    OrderItems::create([
                        'order_id'=>$order->id,
                        'product_id'=>$cart_item['id'],
                        'quantity'=>$cart_item['quantity'],
                        'price'=>$cart_item['price'],
                        'variation_type_option_ids'=>$cart_item['option_ids'],
                    ]);

                     $description = collect($cart_item['options'])->map(function($item){
                        return "{$item['variation_type']['name']}: {$item['name']}";
                     })->implode(',');

                    $line_item = [
                        'price_data' => [
                            'currency' => config('app.currency'),
                            'product_data' => [
                                'name' => $cart_item['name'],
                                'images' => [$cart_item['image_url']],
                            ],
                            'unit_amount' => $cart_item['price'] * 100,
                        ],
                        'quantity' => $cart_item['quantity'],
                    ];
                    if($description) $line_item['price_data']['product_data']['description'] = $description;
                    $line_items[] = $line_item;
                }

            }
                $session = Session::create([
                   'customer_email'=>$user['email'],
                   'line_items'=>$line_items,
                   'mode'=>'payment',
                   'success_url'=>route('stripe.success') . "?session_id={CHECKOUT_SESSION_ID}",
                   'cancel_url'=>route('stripe.cancel') . "?session_id={CHECKOUT_SESSION_ID}",
                ]);

                foreach ($orders as $order) {
                    $order->stripe_session_id = $session->id;
                    $order->save();
                }

                DB::commit();

                return redirect($session->url);

        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return back()->with("error", $e->getMessage() ?: "Something went wrong!");
        }

    }

}
