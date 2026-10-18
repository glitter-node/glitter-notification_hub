<?php

namespace Modules\Glitter\NotificationHub\Providers;

use App\Extension\BaseModuleServiceProvider;
use Modules\Glitter\NotificationHub\Adapters\CoreGenericNotificationAdapter;
use Modules\Glitter\NotificationHub\Adapters\NullCoreNotificationAdapter;
use Modules\Glitter\NotificationHub\Console\Commands\DispatchDueNotificationPlansCommand;
use Modules\Glitter\NotificationHub\Console\Commands\PruneNotificationHubCommand;
use Modules\Glitter\NotificationHub\Contracts\CoreNotificationAdapterInterface;
use Modules\Glitter\NotificationHub\Contracts\NotificationHubInterface;
use Modules\Glitter\NotificationHub\Contracts\CoreNotificationPolicyInspectorInterface;
use Modules\Glitter\NotificationHub\Contracts\PlanRecipientResolverInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\AttemptRepositoryInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\DeadLetterRepositoryInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\NotificationHubTransactionInterface;
use Modules\Glitter\NotificationHub\Contracts\RetryDecisionServiceInterface;
use Modules\Glitter\NotificationHub\Contracts\PlanJobDispatcherInterface;
use Modules\Glitter\NotificationHub\Repositories\AttemptRepository;
use Modules\Glitter\NotificationHub\Repositories\PlanRepository;
use Modules\Glitter\NotificationHub\Repositories\DeadLetterRepository;
use Modules\Glitter\NotificationHub\Repositories\NotificationHubTransaction;
use Modules\Glitter\NotificationHub\Services\NotificationHubService;
use Modules\Glitter\NotificationHub\Services\CoreNotificationPolicyInspector;
use Modules\Glitter\NotificationHub\Services\PlanRecipientResolver;
use Modules\Glitter\NotificationHub\Support\NotificationCorrelationRegistry;
use Modules\Glitter\NotificationHub\Services\RetryDecisionService;
use Modules\Glitter\NotificationHub\Services\LaravelPlanJobDispatcher;
use Modules\Glitter\NotificationHub\Services\NullPlanJobDispatcher;

class NotificationHubServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleIdentifier = 'glitter-notification_hub';

    protected array $repositories = [
        PlanRepositoryInterface::class => PlanRepository::class,
        AttemptRepositoryInterface::class => AttemptRepository::class,
        DeadLetterRepositoryInterface::class => DeadLetterRepository::class,
        NotificationHubTransactionInterface::class => NotificationHubTransaction::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/notification-hub.php', 'notification-hub');

        $this->app->bind(NotificationHubInterface::class, NotificationHubService::class);
        $this->app->bind(PlanRecipientResolverInterface::class, PlanRecipientResolver::class);
        $this->app->bind(CoreNotificationPolicyInspectorInterface::class, CoreNotificationPolicyInspector::class);
        $this->app->bind(RetryDecisionServiceInterface::class, RetryDecisionService::class);
        $this->app->bind(PlanJobDispatcherInterface::class, fn ($app) => $app->make(config('notification-hub.queue_dispatch_enabled', false) ? LaravelPlanJobDispatcher::class : NullPlanJobDispatcher::class));
        $this->app->singleton(NotificationCorrelationRegistry::class);
        $this->app->bind(CoreNotificationAdapterInterface::class, function ($app) {
            $enabled = (bool) config('notification-hub.enabled', false);
            $allowed = (bool) config('notification-hub.allow_external_delivery', false);

            return $app->make($enabled && $allowed
                ? CoreGenericNotificationAdapter::class
                : NullCoreNotificationAdapter::class);
        });
    }

    public function boot(): void
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->commands([
                DispatchDueNotificationPlansCommand::class,
                PruneNotificationHubCommand::class,
            ]);
        }
    }
}
