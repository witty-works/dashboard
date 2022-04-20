<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $r1 = Role::firstOrCreate(["name" => "Superadmin"]);
        Permission::firstOrCreate(['name' => 'manage users']);
        $r1->givePermissionTo('manage users');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("SET foreign_key_checks=0");
        Role::truncate();
        Permission::truncate();
        DB::statement("SET foreign_key_checks=1");
    }
};
