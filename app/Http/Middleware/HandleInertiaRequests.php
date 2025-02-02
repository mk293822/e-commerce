<?php

namespace App\Http\Middleware;

use App\Services\CartServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $cart_service = app(CartServices::class);
        $cartItems = $cart_service->GetCartItems();
        $total_quantity = $cart_service->GetTotalQuantity() ?: 0;
        $total_price = $cart_service->GetTotalPrice() ?: 0;
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'miniCartItems'=> $cartItems,
            'totalQuantity' => $total_quantity,
            'csrf_token' => csrf_token(),
            'totalPrice' => $total_price,
            'success'=>[
                'message'=>session('success'),
                'time'=> microtime(true),
            ],
            'error'=>[
                'message'=>session('error'),
                'time'=> microtime(true),
            ],
        ];
    }
}
