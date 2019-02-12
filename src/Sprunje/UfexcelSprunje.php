<?php

namespace UserFrosting\Sprinkle\Ufexcel\Sprunje;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * UfexcelSprunje
 *
 * Implements Sprunje for ufexcel configuration
 *
 * @author Amos Folz
 */
class UfexcelSprunje extends Sprunje
{
    protected $name = 'ufexcel_tables';

    protected $sortable = [
        'tableid',
        'dbtable'
  ];

    protected $filterable = [
      'tableid',
      'dbtable'
    ];

     protected function baseQuery()
     {
         $instance = $this->classMapper->createInstance('ufexcel_table');
         return $instance->newQuery();
     }
}
