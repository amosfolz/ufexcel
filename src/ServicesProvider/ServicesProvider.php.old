<?php
/**
 * UserFrosting ufexcel sprinkle
 *

 */
namespace UserFrosting\Sprinkle\Ufexcel\ServicesProvider;

use UserFrosting\Sprinkle\Core\Facades\Debug;

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
          $authorizer->addCallback('ufexcel_access',
              /**
               * Check if the specified user (by id) has authorization for ufexcel features
               *
               * @param int $user_id the id of the user.
               * @param int $table_id the id of the ufexcel table.
               * @return bool true if the user is in the organization, false otherwise.
               */
              function ($user_id, $table_id) use ($c) {
                 $user = $c->classMapper->staticMethod('user', 'find', $user_id);
                 return ($user->table_id == $table_id);
              }
          );

          return $authorizer;
      });




    $container->extend('classMapper', function ($classMapper, $c) {
        $classMapper->setClassMapping('ufexcel_tables', 'UserFrosting\Sprinkle\Ufexcel\Database\Models\UfexcelTables');
        return $classMapper;
    });
  }
}
