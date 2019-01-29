<?php




namespace UserFrosting\Sprinkle\Ufexcel\Database\Seeds;

use UserFrosting\Sprinkle\Core\Database\Seeder\SeedInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Facades\Seeder;
/**
 * Seeder for the default permissions
 */
class UfExcelPermissions implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // We require the UfExcel roles
        Seeder::execute('UfExcelRoles');
        // Get and save permissions
        $permissions = $this->getPermissions();
        $this->savePermissions($permissions);
        // Add default mappings to permissions
        $this->syncPermissionsRole($permissions);
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
      protected function savePermissions(array $permissions)
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
      protected function syncPermissionsRole(array $permissions)
      {
          $roleImport = Role::where('slug', 'import_data')->first();
          if ($roleUser) {
              $roleUser->permissions()->sync([
                  $permissions['import_data']->id
              ]);
          }
          $roleExport = Role::where('slug', 'export_data')->first();
          if ($roleExport) {
              $roleSiteAdmin->permissions()->sync([
                  $permissions['export_data']->id
              ]);
          }
      }
  }
