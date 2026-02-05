<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\Product\ProductRepositoryInterface;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Contracts\Cart\CartRepositoryInterface;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Contracts\Product\SearchRepositoryInterface;
use App\Repositories\Product\SearchRepository;
use App\Repositories\Contracts\Promotion\DiscountRepositoryInterface;
use App\Repositories\Promotion\DiscountRepository;
use App\Repositories\Contracts\Order\OrderRepositoryInterface;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Contracts\Payment\EcpayRepositoryInterface;
use App\Repositories\Payment\EcpayRepository;
use App\Repositories\Contracts\Product\CategoryRepositoryInterface;
use App\Repositories\Product\CategoryRepository;
use App\Repositories\Contracts\Product\ProductManageRepositoryInterface;
use App\Repositories\Product\ProductManageRepository;
use App\Repositories\Contracts\Auth\AuthRepositoryInterface;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Contracts\Logistics\LogisticsRepositoryInterface;
use App\Repositories\Logistics\LogisticsRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );

        $this->app->bind(
            CartRepositoryInterface::class,
            CartRepository::class
        );

        $this->app->bind(
            SearchRepositoryInterface::class,
            SearchRepository::class
        );

        $this->app->bind(
            DiscountRepositoryInterface::class,
            DiscountRepository::class
        );

        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );

        $this->app->bind(
            EcpayRepositoryInterface::class,
            EcpayRepository::class
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );

        $this->app->bind(
            ProductManageRepositoryInterface::class,
            ProductManageRepository::class
        );

        $this->app->bind(
            AuthRepositoryInterface::class,
            AuthRepository::class
        );

        $this->app->bind(
            LogisticsRepositoryInterface::class,
            LogisticsRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
