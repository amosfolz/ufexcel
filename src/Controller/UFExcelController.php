<?php


/* UF Laravel Excel Sprinkle (http://www.userfrosting.com) */

namespace UserFrosting\Sprinkle\Ufexcel\Controller;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style;

class UFExcelController extends SimpleController
{


  public function getUsers($request, $response, $args)
  {

      $ufexcelTable = $this->getUfexcelTableFromParams($args);

      if (!$ufexcelTable) {
          throw new NotFoundException($request, $response);
      }

      // GET parameters
      $params = $request->getQueryParams();

      /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
      $authorizer = $this->ci->authorizer;

      /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
      $currentUser = $this->ci->currentUser;

      // Access-controlled page
      if (!$authorizer->checkAccess($currentUser, 'ufexcel_dashboard')) {
          throw new ForbiddenException();
      }

      /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
      $classMapper = $this->ci->classMapper;

      $sprunje = $classMapper->createInstance('ufexcel_sprunje', $classMapper, $params);
      $sprunje->extendQuery(function ($query) use ($ufexcelTable){
          return $query->with('users')
          ->where('ufexcel_tables.id', '=', $ufexcelTable->id);
      });

      // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
      // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
      return $sprunje->toResponse($response);
  }

  public function getTableFeatures($request, $response, $args)
  {

    $ufexcelTable = $this->getUfexcelTableFromParams($args);

    /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
    $currentUser = $this->ci->currentUser;

    /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
    $authorizer = $this->ci->authorizer;

    $json['table'] =  $args["tableid"];

    if ($authorizer->checkAccess($currentUser, 'ufexcel_authorizer', [
        'user'  => $currentUser,
        'table' => $ufexcelTable,
        'feature'   => 'import'
    ])) {
      $json['features']['import'] = true;
    }

    if ($authorizer->checkAccess($currentUser, 'ufexcel_authorizer', [
        'user'  => $currentUser,
        'table' => $ufexcelTable,
        'feature'   => 'export'
    ])) {
      $json['features']['export'] = true;
    }

    // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
    // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
    return $response->withJson($json, 200, JSON_PRETTY_PRINT);
  }

  public function getModalEditUsers($request, $response, $args)
  {
      // GET parameters
      $params = $request->getQueryParams();

      $ufexcelTable = $this->getUfexcelTableFromParams($params);

      // If the address doesn't exist, return 404
      if (!$ufexcelTable) {
          throw new NotFoundException($request, $response);
      }

      /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
      $currentUser = $this->ci->currentUser;

      /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
      $authorizer = $this->ci->authorizer;

      // Access-controlled page
      if (!$authorizer->checkAccess($currentUser, 'ufexcel_dashboard')) {
          throw new ForbiddenException();
      }
      return $this->ci->view->render($response, 'modals/ufexcel-users.html.twig', [
          'ufexceltable' => $ufexcelTable
      ]);
  }




  /**
   * Processes the request to update user access permissions for UFExcel Tables.
   */
  public function updateUsers($request, $response, $args)
  {

      $ufexcelTable = $this->getUfexcelTableFromParams($args);

      if (!$ufexcelTable) {
          throw new NotFoundException($request, $response);
      }

      /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
      $authorizer = $this->ci->authorizer;

      /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
      $currentUser = $this->ci->currentUser;

      // Access-controlled page
      if (!$authorizer->checkAccess($currentUser, 'ufexcel_dashboard')) {
          throw new ForbiddenException();
      }

      /** @var UserFrosting\Config\Config $config */
      $config = $this->ci->config;

      // Get PUT parameters: value
      $put = $request->getParsedBody();

      if (!isset($put['value'])) {
          throw new BadRequestException();
      }

      $params = [
          'users' => $put['value']
      ];

      // Load the request schema
      $schema = new RequestSchema('schema://requests/ufexcel-users.json');

      // Whitelist and set parameter defaults
      $transformer = new RequestDataTransformer($schema);
      $data = $transformer->transform($params);


      // Validate, and throw exception on validation errors.
      $validator = new ServerSideValidator($schema, $this->ci->translator);
      if (!$validator->validate($data)) {
          // TODO: encapsulate the communication of error messages from ServerSideValidator to the BadRequestException
          $e = new BadRequestException();
          foreach ($validator->errors() as $idx => $field) {
              foreach($field as $eidx => $error) {
                  $e->addUserMessage($error);
              }
          }
          throw $e;
      }

      // Get validated and transformed value
      $users = $data['users'];

      /** @var UserFrosting\I18n\MessageTranslator $translator */
      $ms = $this->ci->alerts;

      // Begin transaction - DB will be rolled back if an exception occurs
      Capsule::transaction( function() use ($users, $ufexcelTable, $currentUser) {

          $ufexcelTable->users()->detach();
          $newUsers = collect($users);
          $ufexcelTable->users()->sync($newUsers);

          // Create activity record
          $this->ci->userActivityLogger->info("User {$currentUser->user_name} updated user(s) permissions for '$ufexcelTable->tableid'.", [
              'type' => 'ufexcel_table_users',
              'user_id' => $currentUser->id
          ]);
      });

      // Add success messages
          $ms->addMessage('success', ('User permissions updated for: ' . $ufexcelTable->tableid));
}



  /**
   * Processes the request to update an existing group's details.
   *
   * Processes the request from the group update form, checking that:
   * 1. The group name/slug are not already in use;
   * 2. The user has the necessary permissions to update the posted field(s);
   * 3. The submitted data is valid.
   * This route requires authentication (and should generally be limited to admins or the root user).
   * Request type: PUT
   * @see getModalGroupEdit
   */
  public function update($request, $response, $args)
  {
      // Get the group based on slug in URL
      $ufexcelTable = $this->getUfexcelTableFromParams($args);

      if (!$ufexcelTable) {
          throw new NotFoundException($request, $response);
      }

      // Get PUT parameters: (name, slug, icon, description)
      $params = $request->getParsedBody();

      /** @var UserFrosting\Sprinkle\Core\MessageStream $ms */
      $ms = $this->ci->alerts;

      // Load the request schema
      $schema = new RequestSchema('schema://requests/ufexcel-create.json');

      // Whitelist and set parameter defaults
      $transformer = new RequestDataTransformer($schema);
      $data = $transformer->transform($params);

      $error = false;

      // Validate request data
      $validator = new ServerSideValidator($schema, $this->ci->translator);
      if (!$validator->validate($data)) {
          $ms->addValidationErrors($validator);
          $error = true;
      }


      /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
      $currentUser = $this->ci->currentUser;

      /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
      $authorizer = $this->ci->authorizer;

      // Access-controlled page
      if (!$authorizer->checkAccess($currentUser, 'ufexcel_dashboard')) {
          throw new ForbiddenException();
      }

      /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
      $classMapper = $this->ci->classMapper;

      // Check if tableid already exists
      if (
          isset($data['tableid']) &&
          $data['tableid'] != $ufexcelTable->tableid &&
          $classMapper->staticMethod('ufexcel_table', 'where', 'tableid', $data['tableid'])->first()
      ) {
          $ms->addMessage('danger', 'There is already a configuration with that Table ID', $data);
          $error = true;
      }

      if ($error) {
          return $response->withStatus(400);
      }

      // Begin transaction - DB will be rolled back if an exception occurs
      Capsule::transaction( function() use ($data, $ufexcelTable, $currentUser) {
          // Update the group and generate success messages
          foreach ($data as $name => $value) {
              if ($value != $ufexcelTable->$name) {
                  $ufexcelTable->$name = $value;
              }
          }

          $ufexcelTable->save();

          // Create activity record
          $this->ci->userActivityLogger->info("User {$currentUser->user_name} updated ufexcel configuration for {$ufexcelTable->tableid}.", [
              'type' => 'ufexcel_table',
              'user_id' => $currentUser->id
          ]);
      });

      $ms->addMessageTranslated('success', 'Details updated.');

      return $response->withStatus(200);
  }


  protected function getUfexcelTableFromParams($params)
  {
      // Load the request schema
      $schema = new RequestSchema('schema://requests/get-by-tableid.json');

      // Whitelist and set parameter defaults
      $transformer = new RequestDataTransformer($schema);
      $data = $transformer->transform($params);

      // Validate, and throw exception on validation errors.
      $validator = new ServerSideValidator($schema, $this->ci->translator);
      if (!$validator->validate($data)) {
          // TODO: encapsulate the communication of error messages from ServerSideValidator to the BadRequestException
          $e = new BadRequestException();
          foreach ($validator->errors() as $idx => $field) {
              foreach($field as $eidx => $error) {
                  $e->addUserMessage($error);
              }
          }
          throw $e;
      }

      /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
      $classMapper = $this->ci->classMapper;

      // Get the ufexcel_table
      $config = $classMapper->staticMethod('ufexcel_table', 'where', 'tableid', $data['tableid'])
          ->first();

      return $config;
  }



  public function getModalEdit($request, $response, $args)
  {
      // GET parameters
      $params = $request->getQueryParams();

      $ufexcelTable = $this->getUfexcelTableFromParams($params);

      /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
      $currentUser = $this->ci->currentUser;

      /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
      $authorizer = $this->ci->authorizer;

      // Access-controlled page
      if (!$authorizer->checkAccess($currentUser, 'ufexcel_dashboard')) {
          throw new ForbiddenException();
      }
      /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
      $classMapper = $this->ci->classMapper;

      // Load validation rules
      $schema = new RequestSchema('schema://requests/ufexcel-create.json');
      $validator = new JqueryValidationAdapter($schema, $this->ci->translator);
      $rules = $validator->rules();

      return $this->ci->view->render($response, 'modals/ufexcel-config-create.html.twig', [
        'form' => [
            'action' => "api/ufexcel/update/{$ufexcelTable->tableid}",
            'method' => 'PUT',
            'submit_text' => "Update"
        ],
          'ufexcelconfig'  => $ufexcelTable,
          'page'    => [
              'validators' => [
                'ufexcel'  => $rules
              ]
          ]
      ]);
  }







  /**
   * Processes the request to create a new ufexcel_table configuration.
   *
   * This route requires authentication.
   * Request type: POST
   * @see getModalCreate
   */
   public function create($request, $response, $args)
   {
        // Get the alert message stream
        $ms = $this->ci->alerts;
        // Request POST data
        $post = $request->getParsedBody();
        // Load the request schema
        $schema = new RequestSchema("schema://requests/ufexcel-create.json");
        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);

        $data = $transformer->transform($post);

        // Validate, and halt on validation errors.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            return $response->withStatus(400);
        }


        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'ufexcel_dashboard')) {
            throw new ForbiddenException();
        }
       if ($error) {
           return $response->withStatus(400);
       }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        Capsule::transaction( function() use ($classMapper, $data, $ms, $config, $currentUser)
          {
            $newTable = $classMapper->createInstance('ufexcel_table', $data);

            //save the new configuration
            $newTable->save();
            $ms->addMessage('success', 'New table configuration created successfully.', $data);
          });

            return $response->withStatus(200);
    }







  public function getModalCreate($request, $response, $args)
  {

      // GET parameters
      $params = $request->getQueryParams();

      /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
      $currentUser = $this->ci->currentUser;

      /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
      $authorizer = $this->ci->authorizer;

      // Access-controlled page
      if (!$authorizer->checkAccess($currentUser, 'ufexcel_dashboard')) {
          throw new ForbiddenException();
      }

      // Load validation rules
      $schema = new RequestSchema('schema://requests/ufexcel-create.json');
      $validator = new JqueryValidationAdapter($schema, $this->ci->translator);
      $rules = $validator->rules();

      return $this->ci->view->render($response, 'modals/ufexcel-config-create.html.twig', [
        'form' => [
            'action'       => 'api/ufexcel/create',
            'submit_text'  => "Create",
            'method'       => 'post'
        ],
          'page' => [
              'validators' => [
                'ufexcel'  => $rules
              ]
          ]
      ]);
  }

/**
 * The main ufexcel configuration page.
 * View, edit, and create configurations.
 * This page requires authentication.
 * Request type: GET
 */
public function pageList($request, $response, $args)
{
    /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
    $currentUser = $this->ci->currentUser;

    /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
    $authorizer = $this->ci->authorizer;

    // Access-controlled page
    if (!$authorizer->checkAccess($currentUser, 'ufexcel_dashboard')) {
        throw new ForbiddenException();
    }

    return $this->ci->view->render($response, 'pages/ufexcel-config.html.twig');
  }



  public function getList($request, $response, $args)
  {
      // GET parameters
      $params = $request->getQueryParams();

      /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
      $currentUser = $this->ci->currentUser;

      /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
      $authorizer = $this->ci->authorizer;

      // Access-controlled page
      if (!$authorizer->checkAccess($currentUser, 'ufexcel_dashboard')) {
          throw new ForbiddenException();
      }

      /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
      $classMapper = $this->ci->classMapper;

      $sprunje = $classMapper->createInstance('ufexcel_sprunje', $classMapper, $params);

      // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
      // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
      return $sprunje->toResponse($response);
  }







    protected function checkConfig($tableId)
    {
      /*
      * Get the site.ufexcel config
      */
      $ufexcelTable = $this->ci->config['site.ufexcel'];

      if (array_key_exists($tableId, $ufexcelTable)) {
        $config = $ufexcelTable[$tableId];
        return $config;
      }

      else {
        throw new ForbiddenException();
      }

    }


    public function import($request, $response, $args)
    {

        $ufexcelTable = $this->getUfexcelTableFromParams($args);

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['importFile'];

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        if (!$authorizer->checkAccess($currentUser, 'ufexcel_authorizer', [
            'user'  => $currentUser,
            'table' => $ufexcelTable,
            'feature'   => 'import' ])) {
            throw new ForbiddenException();
        };

        /** Load $uploadedFile to a Spreadsheet Object  **/
        $spreadsheet = IOFactory::load($uploadedFile->file);

        $worksheet = $spreadsheet->getActiveSheet();

        $rows = $worksheet->toArray();

        //Grab the header row so $rows only contains our data to be inserted
        $columns = array_shift($rows);

        //convert spreadsheet to array
        foreach ($rows as $key => $value) {

        //Add column names as keys for each row to be inserted.
            $data[] = array_combine($columns, $value);
        }


        Capsule::beginTransaction();
        try {
            $count = 0;

            foreach ($data as $array => $row) {
                $count = $count + 1;

                Capsule::table($ufexcelTable['dbtable'])->insert($row);
                Capsule::commit();
            }
            $ms = $this->ci->alerts;
            $ms->addMessage('success', ('Successfully inserted ' . $count . ' records into table: ' . $ufexcelTable['dbtable']));

        } catch (\Exception $e) {
            Capsule::rollback();

            /*
            * For now we can at least provide the row # where error occured.
            */
            $error = $count + 1;

            $ms = $this->ci->alerts;
            $ms->addMessage('warning', 'Error at row: ' . $error . '  Please check your file and try again.');
        }

        return $response->withStatus(200);
    }


    public function getModalImport($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $ufexcelTable = $this->getUfexcelTableFromParams($params);

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        if (!$authorizer->checkAccess($currentUser, 'ufexcel_authorizer', [
            'user'  => $currentUser,
            'table' => $ufexcelTable,
            'feature'   => 'import' ])) {
            throw new ForbiddenException();
        };

        return $this->ci->view->render($response, 'modals/import.html.twig', [
      'form' => [
          'action' => "api/ufexcel/import/{$ufexcelTable->tableid}",
          'method' => 'POST',
          'submit_text' => "Submit"
      ],
  ]);
}



    public function getModalImportTemplate($request, $response, $args)
    {

        // GET parameters
        $params = $request->getQueryParams();

        $ufexcelTable = $this->getUfexcelTableFromParams($params);



        $columns = $this->getColumns($ufexcelTable['dbtable']);


      // Remove autoincrementing and required columns from optional list.
        $optionalColumns = array_diff($columns['columns'], $columns['notNullable']);
        $requiredColumns = array_diff($columns['notNullable'], $columns['autoincrementing']);

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        if (!$authorizer->checkAccess($currentUser, 'ufexcel_authorizer', [
                  'user'  => $currentUser,
                  'table' => $ufexcelTable,
                  'feature'   => 'import' ])) {
                  throw new ForbiddenException();
              };

        return $this->ci->view->render($response, 'modals/import-template.html.twig', [
      'columns' => $optionalColumns,
      'requiredColumns' => $requiredColumns,
      'form' => [
          'action' => "api/ufexcel/template/{$ufexcelTable->tableid}",
          'method' => 'POST',
          'submit_text' => "Submit"
        ]
     ]);
  }

    public function getImportTemplate($request, $response, $args)
    {
        $ufexcelTable = $this->getUfexcelTableFromParams($args);

        $columns = $request->getParsedBodyParam('columns');

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        if (!$authorizer->checkAccess($currentUser, 'ufexcel_authorizer', [
            'user'  => $currentUser,
            'table' => $ufexcelTable,
            'feature'   => 'import' ])) {
            throw new ForbiddenException();
        };

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"template.xlsx\"");
        header("Cache-Control: max-age=0");

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
    ->fromArray($columns, null, 'A1');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }






    public function export($request, $response, $args)
    {

      // POST parameters
      $params = $request->getParsedBody();

      $ufexcelTable = $this->getUfexcelTableFromParams($args);

            $tableId = $ufexcelTable['tableid'];
            $format = $params['format'];
            $columns = $params['columns'];

  /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        if (!$authorizer->checkAccess($currentUser, 'ufexcel_authorizer', [
            'user'  => $currentUser,
            'table' => $ufexcelTable,
            'feature'   => 'export' ])) {
            throw new ForbiddenException();
        };

        //grab the data for only the selected columns
        $data = Capsule::table($ufexcelTable['dbtable'])->select($columns)->get();

        //gets data into array
        $array = json_decode(json_encode($data), true);



  // Spreadsheet style arrays https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/#styles


        if ($params['borders'] == "true") {
            $bodyStyle = [
    'font' => [
        'bold' => false,
    ],
    'alignment' => [
        'horizontal' => Style\Alignment::HORIZONTAL_LEFT,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Style\Border::BORDER_THICK,
        ],
    ],
];

          $headerStyle = [
    'font' => [
      //set header to bold font
        'bold' => true,
],
];

        } else {
            $bodyStyle = [
    'font' => [
        'bold' => false,
    ],
    'alignment' => [
        'horizontal' => Style\Alignment::HORIZONTAL_LEFT,
    ]
  ];
          $headerStyle = [
    'font' => [
      //set header to bold font
'bold' => true,
  ],
    ];
        };


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
->fromArray($columns, null, 'A1')
->fromArray($array, null, 'A2');

        $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();
        $highestColumn = $spreadsheet->getActiveSheet()->getHighestColumn();

        $spreadsheet->getActiveSheet()->getStyle("A1:".$highestColumn."1")->applyFromArray($headerStyle);
        $spreadsheet->getActiveSheet()->getStyle("A2:".$highestColumn.$highestRow)->applyFromArray($bodyStyle);

        if ($format == 'xlsx')
        {
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment;filename=\"$tableId-export.xlsx\"");
            header("Cache-Control: max-age=0");

            $writer = new Xlsx($spreadsheet);
        }

        if ($format == 'pdf')
        {
            header("Content-type:application/pdf");
            header("Content-Disposition:attachment;filename=\"$table-export.pdf\"");

            //set header row to repeat on each page
            $spreadsheet->getActiveSheet()
  ->setShowGridlines(true)
  ->getPageSetup()
  ->setRowsToRepeatAtTopByStartAndEnd(1, 1);

            $writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
        }


        if ($format == 'html') {
            header("Content-type:test/html");
            header("Content-Disposition:attachment;filename=\"$table-export.htm\"");

            //set header row to repeat on each page
            $spreadsheet->getActiveSheet()
  ->setShowGridlines(true)
  ->getPageSetup()
  ->setRowsToRepeatAtTopByStartAndEnd(1, 1);

            $writer = new Html($spreadsheet);

        }
            $writer->save('php://output');

    }




    // Use DoctrineSchemaManager to return all available columns for a table.
    private function getColumns($table)
    {
        $sm = Capsule::getDoctrineSchemaManager();
        $tableColumns = $sm->listTableColumns($table);

        foreach ($tableColumns as $tableColumn) {
            $columns['columns'][] = $tableColumn->getName();

            if ($tableColumn->getNotnull() == 1) {
                $columns['notNullable'][] = $tableColumn->getName();
            }
            if ($tableColumn->getAutoincrement() == 1) {
                $columns['autoincrementing'][] = $tableColumn->getName();
            }
        };

        return $columns;
    }




    /**
     * Renders the modal form for editing the pickup lists an address is assigned to.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     **/
    public function getModalExport($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $ufexcelTable = $this->getUfexcelTableFromParams($params);

        $table = $settings['table'];

        $columns = $this->getColumns($ufexcelTable['dbtable']);

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        if (!$authorizer->checkAccess($currentUser, 'ufexcel_authorizer', [
        'user'  => $currentUser,
        'table' => $ufexcelTable,
        'feature'   => 'export' ])) {
        throw new ForbiddenException();
    };

        return $this->ci->view->render($response, 'modals/export.html.twig', [
        'form' => [
              'action' => "api/ufexcel/export/{$ufexcelTable->tableid}",
              'method' => 'POST',
              'submit_text' => "Submit"
          ],
        'columns' => $columns['columns'],
        'notNullable' => $columns['notNullable']
    ]);
    }
}
