<?php


namespace UserFrosting\Sprinkle\Ufexcel\Database\Seeds;
use UserFrosting\Sprinkle\Core\Database\Seeder\SeedInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
/**
 * Seeder for the default roles
 */
class UfExcelRoles implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            // Don't save if already exist
            if (Role::where('slug', $role->slug)->first() == null) {
                $role->save();
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
                'slug'        => 'import',
                'name'        => 'Import',
                'description' => 'This role allows user to import data.'
            ]),
            new Role([
                'slug'        => 'export',
                'name'        => 'Export',
                'description' => 'This role allows user to export data.'
            ]),
        ];
    }
}
