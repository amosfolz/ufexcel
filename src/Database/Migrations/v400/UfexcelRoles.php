<?php
namespace UserFrosting\Sprinkle\Ufexcel\Database\Migrations\v400;


use UserFrosting\System\Bakery\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Account\Database\Models\Role;


/**
 * Migration for default UfExcel roles
 */
class UfExcelRoles extends Migration
{

  public $dependencies = [
      '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable',
      '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable'
  ];

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            // Don't save if already exist
            if (Role::where('slug', $role->slug)->first() == null) {
                $role->save();
            }
        }
    }

    public function down()
    {
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            // Don't save if already exist
            if (Role::where('slug', $role->slug)->first() == null) {
                $role->delete();
            }
        }
    }




    /**
     * @return array Roles to seed
     */
    protected function getRoles()
    {
        return [
            new Role([
                'slug'        => 'ufexcel_admin',
                'name'        => 'UFExcel Administrator',
                'description' => 'This role grants access to UFExcel dashboard and all administrative features.'
            ])
        ];
    }
}
