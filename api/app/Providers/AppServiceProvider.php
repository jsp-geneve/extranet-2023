<?php

namespace App\Providers;

use App\Contracts\MagicLinkServiceInterface;
use App\Services\MagicLinkService;
use Carbon\CarbonImmutable;
use DanielDeWit\LighthouseSanctum\Contracts\Services\SignatureServiceInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MagicLinkServiceInterface::class, function (Container $container) {
            /** @var SignatureServiceInterface $signatureService */
            $signatureService = $container->make(SignatureServiceInterface::class);

            /** @var int $expiresIn */
            $expiresIn = config('auth.magic_links.expire', 15);

            return new MagicLinkService($signatureService, $expiresIn);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Date::use(CarbonImmutable::class);
        Model::shouldBeStrict();
    }
}
