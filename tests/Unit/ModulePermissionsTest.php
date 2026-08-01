<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit;

use Modules\Glitter\NotificationHub\Module;
use PHPUnit\Framework\TestCase;

class ModulePermissionsTest extends TestCase
{
    public function test_module_and_admin_menu_names_are_consistent_without_changing_menu_contracts(): void
    {
        if (! class_exists(Module::class)) require dirname(__DIR__, 2).'/module.php';

        $root = dirname(__DIR__, 2);
        $manifest = json_decode(file_get_contents($root.'/module.json'), true, 512, JSON_THROW_ON_ERROR);
        $menus = (new Module)->getAdminMenus();

        $this->assertCount(1, $menus);
        $this->assertSame(['ko' => '알림 허브', 'en' => 'Notification Hub'], $manifest['name']);
        $this->assertSame($manifest['name'], $menus[0]['name']);
        $this->assertSame('glitter-notification_hub', $menus[0]['slug']);
        $this->assertSame('/admin/modules/glitter-notification_hub/plans', $menus[0]['url']);
        $this->assertSame('glitter-notification_hub.plans.read', $menus[0]['permission']);

        $this->assertCount(3, $menus[0]['children']);
        $this->assertSame(
            ['glitter-notification_hub_plans', 'glitter-notification_hub_attempts', 'glitter-notification_hub_dead_letters'],
            array_column($menus[0]['children'], 'slug'),
        );
        $this->assertSame(
            ['/admin/modules/glitter-notification_hub/plans', '/admin/modules/glitter-notification_hub/attempts', '/admin/modules/glitter-notification_hub/dead-letters'],
            array_column($menus[0]['children'], 'url'),
        );
        $this->assertSame(
            ['glitter-notification_hub.plans.read', 'glitter-notification_hub.attempts.read', 'glitter-notification_hub.dead-letters.read'],
            array_column($menus[0]['children'], 'permission'),
        );
        $this->assertSame(
            [
                ['ko' => '전달 계획', 'en' => 'Delivery Plans'],
                ['ko' => '전달 시도', 'en' => 'Delivery Attempts'],
                ['ko' => '실패 보관함', 'en' => 'Dead Letters'],
            ],
            array_column($menus[0]['children'], 'name'),
        );

        foreach (['ko', 'en'] as $locale) {
            $translations = require $root.'/src/lang/'.$locale.'/admin.php';
            $this->assertSame($manifest['name'][$locale], $translations['menus']['root']);
        }
    }

    public function test_permissions_are_translated_and_do_not_publish_settings(): void
    {
        if (! class_exists(Module::class)) require dirname(__DIR__, 2).'/module.php';
        $categories = (new Module)->getPermissions()['categories'];
        $this->assertSame(['plans', 'attempts', 'dead-letters'], array_column($categories, 'identifier'));

        foreach ($categories as $category) {
            foreach ([$category, ...$category['permissions']] as $permission) {
                foreach (['ko', 'en'] as $locale) {
                    $this->assertStringNotContainsString('notification_hub.permissions.', $permission['name'][$locale]);
                    $this->assertNotSame('', $permission['description'][$locale]);
                }
            }
        }
    }

    public function test_write_permissions_match_module_routes_buttons_and_modals(): void
    {
        if (! class_exists(Module::class)) require dirname(__DIR__, 2).'/module.php';

        $root = dirname(__DIR__, 2);
        $routes = file_get_contents($root.'/src/routes/api.php');
        $this->assertIsString($routes);

        $layouts = [
            json_decode(file_get_contents($root.'/resources/layouts/admin/plan_detail.json'), true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents($root.'/resources/layouts/admin/dead_letter_detail.json'), true, 512, JSON_THROW_ON_ERROR),
        ];
        $nodes = [];
        $collect = function (array $items) use (&$collect, &$nodes): void {
            foreach ($items as $item) {
                if (! is_array($item)) continue;
                if (isset($item['id'])) $nodes[$item['id']] = $item;
                if (isset($item['children']) && is_array($item['children'])) $collect($item['children']);
            }
        };
        foreach ($layouts as $layout) {
            foreach ($layout['slots'] ?? [] as $slot) $collect($slot);
            $collect($layout['modals'] ?? []);
        }

        $boundaries = [
            'glitter-notification_hub.plans.cancel' => ['cancel_plan_button', 'cancel_plan_modal'],
            'glitter-notification_hub.plans.retry' => ['retry_plan_button', 'retry_plan_modal'],
            'glitter-notification_hub.dead-letters.resolve' => ['resolve_dead_letter_button', 'resolve_dead_letter_modal'],
            'glitter-notification_hub.dead-letters.retry' => ['retry_dead_letter_button', 'retry_dead_letter_modal'],
        ];
        foreach ($boundaries as $permission => $ids) {
            $this->assertStringContainsString($permission, $routes);
            foreach ($ids as $id) $this->assertSame([$permission], $nodes[$id]['permissions'] ?? null, $id);
        }

        $declared = [];
        foreach ((new Module)->getPermissions()['categories'] as $category) {
            foreach ($category['permissions'] as $permission) {
                $declared[] = 'glitter-notification_hub.'.$category['identifier'].'.'.$permission['action'];
            }
        }
        foreach (array_keys($boundaries) as $permission) $this->assertContains($permission, $declared);
    }
}
