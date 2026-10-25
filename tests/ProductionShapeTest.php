<?php

namespace Maklad\Permission\Test;

use Maklad\Permission\Models\Permission;
use Maklad\Permission\Models\Role;
use MongoDB\BSON\ObjectId;

class ProductionShapeTest extends TestCase
{
    public function test_create_permission_role_assign_and_check_with_production_shape(): void
    {
        $companyId = (string) new ObjectId();

        $view = Permission::create([
            'name' => 'event.view',
            'guard_name' => 'api',
        ]);
        $edit = Permission::create([
            'name' => 'event.edit',
            'guard_name' => 'api',
        ]);
        Permission::create([
            'name' => 'event.delete',
            'guard_name' => 'api',
        ]);

        $ownerRole = Role::create([
            'name' => 'owner.'.$companyId,
            'guard_name' => 'api',
        ]);
        $adminRole = Role::create([
            'name' => 'admin.'.$companyId,
            'guard_name' => 'api',
        ]);

        $ownerRole->givePermissionTo([$view, $edit]);
        $adminRole->givePermissionTo([$view]);

        $ownerRole->refresh();
        $this->assertNotEmpty($ownerRole->permission_ids);
        $this->assertCount(2, $ownerRole->permission_ids);

        $user = User::create(['email' => 'owner@example.com']);
        $user->assignRole($ownerRole);
        $user->refresh();

        $this->assertNotEmpty($user->role_ids);
        $this->assertTrue($user->hasRole('owner.'.$companyId));
        $this->assertTrue($user->hasPermissionTo('event.view'));
        $this->assertTrue($user->hasPermissionTo('event.edit'));
        $this->assertFalse($user->hasPermissionTo('event.delete'));

        $roleNames = $user->getRoleNames();
        $this->assertTrue($roleNames->contains('owner.'.$companyId));
        $this->assertNotFalse(strpos($roleNames->first(), $companyId));

        $this->assertSame('api', $ownerRole->guard_name);
        $this->assertSame('api', $view->guard_name);
    }
}
