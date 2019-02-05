<?php
return
['site' => [

  'ufexcel' => [
    'table-vehicles' => [
              'table' => 'vehicles',
    ],
/*
**  Default UserFrosting tables. If you do not want ufexcel for a certain table
**  delete it or add the options you want disable to 'hidden'.
*/
      'table-activities' => [
                'table'  => 'activities',
                'hidden' => []
      ],

      'table-groups' => [
                'table'  => 'groups',
                'hidden' => []
      ],

      'table-roles' => [
                'table'  => 'roles',
                'hidden' => []
      ],

      'table-users' => [
                'table'  => 'users',
                'hidden' => []
      ],
// Import & template should always be hidden for permission table. Permissions should not be created client-side.
// See https://learn.userfrosting.com/recipes/advanced-tutorial/custom-permissions#the-migration-class
      'table-permissions' => [
                'table'  => 'groups',
                'hidden' => [
                  'import',
                  'template'
                ]
      ],



    ]
  ]
];
