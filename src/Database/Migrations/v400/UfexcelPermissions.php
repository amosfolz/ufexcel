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
        '\UserFrosting\Sprinkle\Ufexcel\Database\Migrations\v400\UfexcelRoles'
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
                if($permission != null){
                $permission->delete();
              }
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
                  'ufexcel_dashboard' => new Permission([
                      'slug'        => 'ufexcel_dashboard',
                      'name'        => 'UFExcel Dashboard',
                      'conditions'  => 'always()',
                      'description' => 'View and manage UFExcel through the dashboard.'
                  ]),
                  'ufexcel_authorizer' => new Permission([
                      'slug'        => 'ufexcel_authorizer',
                      'name'        => 'UfExcel Authorizer',
                      'conditions'  => 'ufexcel_authorizer(user.id,table.id,feature)',
                      'description' => 'Main UFExcel authorization component.'
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
              $ufexcelAdmin = Role::where('slug', 'ufexcel_admin')->first();
              if ($ufexcelAdmin) {
                  $ufexcelAdmin->permissions()->sync([
                      $permissions['ufexcel_dashboard']->id
                  ]);
              }
          }
      }
