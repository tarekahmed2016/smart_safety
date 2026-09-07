<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $permissions = [
        'products.view',
        'products.create',
        'products.update',
        'products.delete',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::query()->where('name', 'admin')->where('guard_name', 'web')->first();
        $admin?->givePermissionTo($this->permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::query()->where('name', 'admin')->where('guard_name', 'web')->first();
        $admin?->revokePermissionTo($this->permissions);

        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $this->permissions)
            ->delete();
    }
};
