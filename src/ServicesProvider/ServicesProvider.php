<?php
/**
 * UserFrosting ufexcel sprinkle
 *

 */
namespace UserFrosting\Sprinkle\Ufexcel\ServicesProvider;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use Illuminate\Database\Capsule\Manager as Capsule;
/**
 * Registers services for ufexcel
 *
 * @author David Attenborough
 */
class ServicesProvider
{
    /*
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {

      $container->extend('authorizer', function ($authorizer, $c) {
          $authorizer->addCallback('ufexcel_authorizer',
              /**
               * Check if the specified user (by id) has authorization for ufexcel features
               *
               * @param int $user_id the id of the user.
               * @param int $table_id the id of the ufexcel table.
               * @return bool true if the user is in the organization, false otherwise.
               */
               function ($user, $table, $feature) {

                    return Capsule::table('ufexcel_users')
                         ->where('user_id', $user)
                         ->where('table_id', $table)
                         ->where($feature, 1)
                         ->count() > 0;
                 }
          );

          return $authorizer;
      });




    $container->extend('classMapper', function ($classMapper, $c) {
        $classMapper->setClassMapping('ufexcel_table', 'UserFrosting\Sprinkle\Ufexcel\Database\Models\UfexcelTable');
        $classMapper->setClassMapping('ufexcel_sprunje', 'UserFrosting\Sprinkle\Ufexcel\Sprunje\UfexcelSprunje');
        return $classMapper;
    });
  }
}
