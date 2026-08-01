<?php

namespace Modules\Glitter\NotificationHub;

use App\Extension\AbstractModule;
use Modules\Glitter\NotificationHub\Listeners\CoreNotificationDeliveryListener;
use Modules\Glitter\NotificationHub\Listeners\NotificationHubActivityLogListener;

class Module extends AbstractModule
{
    public function getSchedules(): array
    {
        return [
            ['command' => 'notification-hub:dispatch-due', 'schedule' => 'everyMinute', 'description' => 'Dispatch due notification plans'],
            ['command' => 'notification-hub:prune', 'schedule' => 'daily', 'description' => 'Prune retained Notification Hub audit records'],
        ];
    }

    /** @return array<int, class-string> */
    public function getHookListeners(): array
    {
        return [CoreNotificationDeliveryListener::class, NotificationHubActivityLogListener::class];
    }

    public function getPermissions(): array
    {
        return [
            'name' => [
                'ko' => '알림 허브',
                'en' => 'Notification Hub',
            ],
            'description' => [
                'ko' => '알림 허브 관리 권한',
                'en' => 'Notification Hub management permissions',
            ],
            'categories' => [
                $this->permissionCategory('plans', ['read', 'cancel', 'retry']),
                $this->permissionCategory('attempts', ['read']),
                $this->permissionCategory('dead-letters', ['read', 'resolve', 'retry']),
            ],
        ];
    }

    public function getAdminMenus(): array
    {
        return [[
                'name' => [
                    'ko' => '알림 허브',
                    'en' => 'Notification Hub',
                ],
                'slug' => 'glitter-notification_hub',
                'url' => '/admin/modules/glitter-notification_hub/plans',
                'icon' => 'fas fa-bell',
                'order' => 40,
                'permission' => 'glitter-notification_hub.plans.read',
                'children' => [
                    ['name'=>['ko'=>'전달 계획','en'=>'Delivery Plans'],'slug'=>'glitter-notification_hub_plans','url'=>'/admin/modules/glitter-notification_hub/plans','permission'=>'glitter-notification_hub.plans.read'],
                    ['name'=>['ko'=>'전달 시도','en'=>'Delivery Attempts'],'slug'=>'glitter-notification_hub_attempts','url'=>'/admin/modules/glitter-notification_hub/attempts','permission'=>'glitter-notification_hub.attempts.read'],
                    ['name'=>['ko'=>'실패 보관함','en'=>'Dead Letters'],'slug'=>'glitter-notification_hub_dead_letters','url'=>'/admin/modules/glitter-notification_hub/dead-letters','permission'=>'glitter-notification_hub.dead-letters.read'],
                ],
            ]];
    }

    /**
     * @param  array<int, string>  $actions
     * @return array<string, mixed>
     */
    private function permissionCategory(string $identifier, array $actions): array
    {
        $translations = [
            'plans' => [
                'name' => ['ko' => '전달 계획', 'en' => 'Plans'],
                'description' => ['ko' => '전달 계획 관리 권한', 'en' => 'Delivery plan permissions'],
                'actions' => [
                    'read' => ['ko' => '전달 계획 조회', 'en' => 'View plans'],
                    'cancel' => ['ko' => '전달 계획 취소', 'en' => 'Cancel plans'],
                    'retry' => ['ko' => '전달 계획 재시도', 'en' => 'Retry plans'],
                ],
            ],
            'attempts' => [
                'name' => ['ko' => '전달 시도', 'en' => 'Attempts'],
                'description' => ['ko' => '전달 시도 조회 권한', 'en' => 'Delivery attempt permissions'],
                'actions' => ['read' => ['ko' => '전달 시도 조회', 'en' => 'View attempts']],
            ],
            'dead-letters' => [
                'name' => ['ko' => '실패 보관함', 'en' => 'Dead Letters'],
                'description' => ['ko' => '최종 실패 내역 관리 권한', 'en' => 'Dead letter permissions'],
                'actions' => [
                    'read' => ['ko' => '실패 내역 조회', 'en' => 'View dead letters'],
                    'resolve' => ['ko' => '실패 내역 해결', 'en' => 'Resolve dead letters'],
                    'retry' => ['ko' => '실패 내역 재시도', 'en' => 'Retry dead letters'],
                ],
            ],
        ];
        $translation = $translations[$identifier];

        return [
            'identifier' => $identifier,
            'resource_route_key' => null,
            'owner_key' => null,
            'name' => $translation['name'],
            'description' => $translation['description'],
            'permissions' => array_map(static fn (string $action): array => [
                'action' => $action,
                'name' => $translation['actions'][$action],
                'description' => $translation['actions'][$action],
                'type' => 'admin',
                'roles' => ['admin'],
            ], $actions),
        ];
    }
}
