<?php
namespace UserFrosting\Sprinkle\Ufexcel\Database\Migrations\v400;



use UserFrosting\System\Bakery\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;

/**
 * Migration for default UfExcel permissions
 */
class UfExcelPermissions extends Migration
{
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Ufexcel\Database\Migrations\v400\UfExcelRoles'
    ];


    public function up()
    {
        // Get and save permissions
        $permissions = $this->getPermissions();
        $this->savePermissions($permissions);
        // Add default mappings to permissions
        $this->syncPermissionsRole($permissions);
    }



        /**
         * {@inheritDoc}
         */
        public function down()
        {
            foreach ($this->getPermissions() as $permissionInfo) {
                $permission = Permission::where('slug', $permissionInfo['slug'])->first();
                $permission->delete();
            }
        }


        /**
           * @return array Permissions to seed
           */
          protected function getPermissions()
          {
              $defaultRoleIds = [
                  'import'      => Role::where('slug', 'import')->first()->id,
                  'export'      => Role::where('slug', 'export')->first()->id
              ];
              return [
                  'import_data' => new Permission([
                      'slug'        => 'import_data',
                      'name'        => 'Import data',
                      'conditions'  => 'always()',
                      'description' => 'Import data into database using UfExcel Import.'
                  ]),
                  'export_data' => new Permission([
                      'slug'        => 'export_data',
                      'name'        => 'Export data',
                      'conditions'  => 'always()',
                      'description' => 'Export data from database using UfExcel Export.'
                  ])
              ];
          }
          /**
           * Save permissions
           * @param array $permissions
           */
          protected function savePermissions($permissions)
          {
              foreach ($permissions as $slug => $permission) {
                  // Trying to find if the permission already exist
                  $existingPermission = Permission::where(['slug' => $permission->slug, 'conditions' => $permission->conditions])->first();
                  // Don't save if already exist, use existing permission reference
                  // otherwise to re-sync permissions and roles
                  if ($existingPermission == null) {
                      $permission->save();
                  } else {
                      $permissions[$slug] = $existingPermission;
                  }
              }
          }
          /**
           * Sync permissions with default roles
           * @param array $permissions
           */
          protected function syncPermissionsRole($permissions)
          {
              $roleImport = Role::where('slug', 'import')->first();
              if ($roleImport) {
                  $roleImport->permissions()->sync([
                      $permissions['import_data']->id
                  ]);
              }
              $roleExport = Role::where('slug', 'export')->first();
              if ($roleExport) {
                  $roleExport->permissions()->sync([
                      $permissions['export_data']->id
                  ]);
              }
          }
      }
