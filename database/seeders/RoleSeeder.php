<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\UserRoleEnums;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $adminRole = Role::create(['name' => UserRoleEnums::Admin->value]);
       $vendorRole = Role::create(['name' => UserRoleEnums::Vendor->value]);
       $userRole = Role::create(['name' => UserRoleEnums::User->value]);

       $approvedVendor = Permission::create(['name' => PermissionEnum::ApprovedVendor->value]);
       $sellProduct = Permission::create(['name' => PermissionEnum::SellProduct->value]);
       $buyProduct = Permission::create(['name' => PermissionEnum::BuyProduct->value]);

       $adminRole->syncPermissions([$approvedVendor]);
       $vendorRole->syncPermissions([$sellProduct, $buyProduct]);
       $userRole->syncPermissions([$buyProduct]);
    }
}
