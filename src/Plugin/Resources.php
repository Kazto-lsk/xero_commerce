<?php

namespace Xpressengine\Plugins\XeroCommerce\Plugin;

use App\Facades\XeInterception;
use XeRegister;
use Route;
use Xpressengine\Plugins\XeroCommerce\Handlers\CartHandler;
use Xpressengine\Plugins\XeroCommerce\Handlers\OrderHandler;
use Xpressengine\Plugins\XeroCommerce\Handlers\ProductHandler;
use Xpressengine\Plugins\XeroCommerce\Handlers\ProductOptionItemHandler;
use Xpressengine\Plugins\XeroCommerce\Handlers\StoreHandler;
use Xpressengine\Plugins\XeroCommerce\Models\Store;
use Xpressengine\Plugins\XeroCommerce\Models\Order;
use Xpressengine\Plugins\XeroCommerce\Plugin;
use Xpressengine\User\Models\User;

class Resources
{
    /**
     * @return void
     */
    public static function registerRoute()
    {
        Route::settings('xero_commerce', function () {
            Route::group([
                'namespace' => 'Xpressengine\\Plugins\\XeroCommerce\\Controllers\\Settings'
            ], function () {
                //상품관리
                Route::group(['prefix' => 'product'], function () {
                    Route::get('/', ['as' => 'xero_commerce::setting.product.index', 'uses' => 'ProductController@index',
                        'settings_menu' => 'xero_commerce.product.list']);
                    Route::get('/create', ['as' => 'xero_commerce::setting.product.create',
                        'uses' => 'ProductController@create', 'settings_menu' => 'xero_commerce.product.create']);
                    Route::post('/store', ['as' => 'xero_commerce::setting.product.store',
                        'uses' => 'ProductController@store']);
                    Route::get('/{productId}', ['as' => 'xero_commerce::setting.product.show',
                        'uses' => 'ProductController@show']);
                });

                //분류 관리
                Route::get('/category', ['as' => 'xero_commerce::setting.category.index',
                    'uses' => 'CategoryController@index',
                    'settings_menu' => 'xero_commerce.product.category']);

                //주문 관리
                Route::group(['prefix' => 'order'], function () {
                    Route::get('/', [
                        'as' => 'xero_commerce::setting.order.index',
                        'uses' => 'OrderController@index',
                        'settings_menu' => 'xero_commerce.order.index'
                    ]);
                });

                //쇼핑몰 설정
                Route::group(['prefix' => 'shop_config'], function () {
                    Route::get('/create', ['as' => 'xero_commerce::setting.config.create',
                        'uses' => 'ShopConfigController@create',
                        'settings_menu' => 'xero_commerce.config.shopInfo']);

                    Route::post('/store', ['as' => 'xero_commerce::setting.config.store',
                        'uses' => 'ShopConfigController@store']);
                });
            });
        });
        Route::fixed('xero_commerce', function () {
            Route::group([
                'namespace' => 'Xpressengine\\Plugins\\XeroCommerce\\Controllers'
            ], function () {
                Route::get('/cart', [
                    'uses' => 'CartController@index',
                    'as' => 'xero_commerce::cart.index'
                ]);
                Route::get('/cart/draw/{cart}', [
                    'uses' => 'CartController@draw',
                    'as' => 'xero_commerce::cart.draw'
                ]);
                Route::post('/order', [
                    'uses' => 'OrderController@register',
                    'as' => 'xero_commerce::order.register'
                ]);
                Route::get('/order', [
                    'uses' => 'OrderController@index',
                    'as' => 'xero_commerce::order.index'
                ]);
            });
        });
    }

    /**
     * @return void
     */
    public static function bindClasses()
    {
        $app = app();

        $app->singleton(StoreHandler::class, function ($app) {
            $proxyHandler = XeInterception::proxy(StoreHandler::class);

            $instance = new $proxyHandler();

            return $instance;
        });
        $app->alias(StoreHandler::class, 'xero_commerce.storeHandler');

        $app->singleton(ProductHandler::class, function ($app) {
            $proxyHandler = XeInterception::proxy(ProductHandler::class);

            $instance = new $proxyHandler();

            return $instance;
        });
        $app->alias(ProductHandler::class, 'xero_commerce.productHandler');

        $app->singleton(ProductOptionItemHandler::class, function ($app) {
            $proxyHandler = XeInterception::proxy(ProductOptionItemHandler::class);

            $instance = new $proxyHandler();

            return $instance;
        });
        $app->alias(ProductOptionItemHandler::class, 'xero_commerce.productOptionItemHandler');

        $app->singleton(OrderHandler::class, function ($app) {
            $proxyHandler = XeInterception::proxy(OrderHandler::class);

            $instance = new $proxyHandler(new Order());

            return $instance;
        });
        $app->alias(OrderHandler::class, 'xero_commerce.orderHandler');


        $app->singleton(CartHandler::class, function ($app) {
            $proxyHandler = XeInterception::proxy(CartHandler::class);

            $instance = new $proxyHandler(User::first());

            return $instance;
        });
        $app->alias(CartHandler::class, 'xero_commerce.cartHandler');
    }

    /**
     * @return void
     */
    public static function setConfig()
    {
        $category = \XeCategory::create([
            'name' => '상품 분류'
        ]);

        $config = \XeConfig::get(Plugin::getId());
        if ($config === null) {
            \XeConfig::add(Plugin::getId(), [
                'categoryId' => $category->id,
            ]);
        }
    }

    public static function storeDefaultStore()
    {
        $userId = \Auth::user()->getId();

        if (Store::where('store_name', Store::BASIC_STORE_NAME)->first() == null) {
            $newStore = new Store();
            $newStore['user_id'] = $userId;
            $newStore['store_type'] = Store::TYPE_STORE;
            $newStore['store_name'] = Store::BASIC_STORE_NAME;
            $newStore->save();
        }
    }

    /**
     * 관리자에 메뉴 등록
     *
     * @return void
     */
    public static function registerSettingMenu()
    {
        $menus = array_merge(
            [
                'xero_commerce' => [
                    'title' => '쇼핑몰 관리',
                    'display' => true,
                    'description' => '',
                    'ordering' => 10000
                ],
            ],
            static::menuConfigure(),
            static::menuOrder(),
            static::menuProduct()
        );

        foreach ($menus as $id => $menu) {
            XeRegister::push('settings/menu', $id, $menu);
        }
    }

    /**
     * @return array
     */
    private static function menuProduct()
    {
        return [
            'xero_commerce.product' => [
                'title' => '상품관리',
                'display' => true,
                'description' => '',
                'ordering' => 10001
            ],
            'xero_commerce.product.list' => [
                'title' => '전체 상품목록',
                'display' => true,
                'description' => '',
                'ordering' => 100011
            ],
            'xero_commerce.product.category' => [
                'title' => '분류 관리',
                'display' => true,
                'description' => '',
                'ordering' => 100012
            ],
            'xero_commerce.product.create' => [
                'title' => '상품 등록',
                'display' => true,
                'description' => '',
                'ordering' => 100013
            ],
        ];
    }

    /**
     * @return array
     */
    private static function menuOrder()
    {
        return [
            'xero_commerce.order' => [
                'title' => '주문',
                'display' => true,
                'description' => '',
                'ordering' => 10002
            ],
            'xero_commerce.order.index' => [
                'title' => '전체 주문내역',
                'display' => true,
                'description' => '',
                'ordering' => 100011
            ],
        ];
    }

    /**
     * @return array
     */
    private static function menuConfigure()
    {
        return [
            'xero_commerce.config' => [
                'title' => '환경설정',
                'display' => true,
                'description' => '',
                'ordering' => 10003
            ],

            'xero_commerce.config.shopInfo' => [
                'title' => '쇼핑몰 정보 등록',
                'display' => true,
                'description' => '',
                'ordering' => 100011
            ],
        ];
    }
}
