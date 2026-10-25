<?php

namespace Maklad\Permission\Test;

use Maklad\Permission\Models\Permission;
use Maklad\Permission\Models\Role;
use Maklad\Permission\PermissionRegistrar;
use Maklad\Permission\PermissionServiceProvider;
use MongoDB\Laravel\MongodbServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $database = config('database.connections.mongodb.database');
        if ($database !== 'permission_package_test') {
            $this->fail('拒絕清空非套件測試庫：'.$database);
        }

        User::query()->delete();
        Role::query()->delete();
        Permission::query()->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function getPackageProviders($app): array
    {
        return [
            MongodbServiceProvider::class,
            PermissionServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'mongodb');
        $app['config']->set('database.connections.mongodb', [
            'driver' => 'mongodb',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int) env('DB_PORT', 27017),
            'database' => env('DB_DATABASE', 'permission_package_test'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'admin'),
            'options' => [
                'database' => env('DB_AUTHENTICATION_DATABASE', 'admin'),
            ],
        ]);

        $app['config']->set('auth.defaults.guard', 'api');
        $app['config']->set('auth.guards.api', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('permission.collection_names.roles', 'roles');
        $app['config']->set('permission.collection_names.permissions', 'permissions');
        $app['config']->set('cache.default', 'array');
    }
}
