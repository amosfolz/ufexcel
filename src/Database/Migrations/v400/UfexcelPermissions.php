<?php
namespace UserFrosting\Sprinkle\Ufexcel\Database\Migrations\v400;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\System\Bakery\Migration;

class UfexcelPermissions extends Migration
{
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable'
    ];

    public function seed()
    {
        // Add default permissions
        $permissions = [
            'export_data' => new Permission([
                'slug' => 'export_data',
                'name' => 'Export data',
                'conditions' => 'always()',
                'description' => 'View a page containing a list of members.'
            ]),
            'import_data' => new Permission([
                'slug' => 'import_data',
                'name' => 'Import data',
                'conditions' => 'always()',
                'description' => 'View a full list of owls in the system.'
            ])
        ];

        foreach ($permissions as $id => $permission) {
            $slug = $permission->slug;
            $conditions = $permission->conditions;
            // Skip if a permission with the same slug and conditions has already been added
            if (!Permission::where('slug', $slug)->where('conditions', $conditions)->first()) {
                $permission->save();
            }
        }

        // Automatically add permissions to particular roles
        $roleAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleAdmin) {
            $roleAdmin->permissions()->syncWithoutDetaching([
                $permissions['uri_members']->id,
                $permissions['uri_owls']->id
            ]);
        }
    }
}
