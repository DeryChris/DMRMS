<?php

namespace App\Providers;

use App\Services\FinalDecision\DefaultSelectionStrategy;
use App\Services\FinalDecision\SelectionStrategy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SelectionStrategy::class, DefaultSelectionStrategy::class);
    }

    public function boot(): void
    {
        Blade::if('subscription', function (string $tier) {
            $admin = auth('admin')->user();

            if (!$admin || !$admin->subscription_tier) {
                return false;
            }

            return strtolower($admin->subscription_tier) === strtolower($tier);
        });

        Blade::if('hasFeature', function (string $feature) {
            $admin = auth('admin')->user();

            if (!$admin || !$admin->subscription_tier) {
                return false;
            }

            $features = config("subscription.features.{$feature}", []);

            return in_array(strtolower($admin->subscription_tier), $features);
        });

        Blade::directive('money', function (int|float $amount): string {
            return "<?php echo 'GHS ' . number_format((float) {$amount}, 2); ?>";
        });
    }
}
