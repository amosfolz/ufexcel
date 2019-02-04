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
use UserFrosting\Sprinkle\Address\Database\Models\Address;
use UserFrosting\Sprinkle\Vehicles\Database\Models\Vehicle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style;

class UFExcelController extends SimpleController
{

/**
*      This methods accepts id attribute of table and returns the ufexcel configuration.
*      If the table id is not found it is assumed the table should not be used with ufexcel
*      and returns ForbiddenException
**/
    protected function checkConfig($tableId)
    {
      /*
      * Get the site.ufexcel config
      */
      $ufexcelConfig = $this->ci->config['site.ufexcel'];


      if (array_key_exists($tableId, $ufexcelConfig)) {
        $config = $ufexcelConfig[$tableId];
        return $config;
      }

      else {
        throw new ForbiddenException();
      }

    }






    public function import($request, $response, $args)
    {

  // POST parameters
        $tableId = $request->getParsedBodyParam('table');

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['importFile'];

        /*
        *
        * 1. Check if user is authorized to import.
        * 2. Check if table has 'import' set under 'hidden', in which case import should not be allowed on this table.
        */
        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'import_data')) {
            throw new ForbiddenException();
        }


        $settings = $this->checkConfig($tableId);
        $table = $settings['table'];

        /** Load $uploadedFile to a Spreadsheet Object  **/
        $spreadsheet = IOFactory::load($uploadedFile->file);

        $worksheet = $spreadsheet->getActiveSheet();

        $rows = $worksheet->toArray();

        //Grab the header row so array only contains data
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
                Debug::debug("var inserts");
                Debug::debug(print_r($inserts, true));
                Capsule::table($table)->insert($row);
                Capsule::commit();
            }
            $ms = $this->ci->alerts;
            $ms->addMessage('success', ('Successfully inserted ' . $count . ' records into table: ' . $table));
        } catch (\Exception $e) {
            Capsule::rollback();

            /*
            * For now we can at least provide the row # where error occured.
            */
            $error = $count + 1;

            $ms = $this->ci->alerts;
            $ms->addMessage('warning', 'Error at row: ' . $error . '  Please check your file and try again.');
        }
    }


    public function getModalImport($request, $response, $args)
    {
        $table = $request->getQueryParam('table');


        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /*
          // Access-controlled resource - check that currentUser has permission to edit "permissions" field for this role
          if (!$authorizer->checkAccess($currentUser, 'update_role_field', [
              'role' => $role,
              'fields' => ['permissions']
          ])) {
              throw new ForbiddenException();
          }
        */
        return $this->ci->view->render($response, 'modals/import.html.twig', [
      'table' => $table
  ]);
    }



    public function getModalImportTemplate($request, $response, $args)
    {
        // GET parameters
        $model = $request->getQueryParam('model');
        $table = $request->getQueryParam('table');

        $sm = Capsule::getDoctrineSchemaManager();
        $tableColumns = $sm->listTableColumns($table);


        $columns = $this->getColumns($table);
        //$columns = $this->getColumns($model);

        //Remove autoincrementing and required columns from optional list.
        $optionalColumns = array_diff($columns['columns'], $columns['notNullable']);
        $requiredColumns = array_diff($columns['notNullable'], $columns['autoincrementing']);


        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /*
          // Access-controlled resource - check that currentUser has permission to edit "permissions" field for this role
          if (!$authorizer->checkAccess($currentUser, 'update_role_field', [
              'role' => $role,
              'fields' => ['permissions']
          ])) {
              throw new ForbiddenException();
          }
        */
        return $this->ci->view->render($response, 'modals/import-template.html.twig', [
      'columns' => $optionalColumns,
      'requiredColumns' => $requiredColumns,
      'table' => $table
  ]);
    }

    public function getImportTemplate($request, $response, $args)
    {
        $params = $request->getParsedBodyParam('columns');

        $table = $request->getQueryParam('table');

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"template.xlsx\"");
        header("Cache-Control: max-age=0");

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
    ->fromArray($params, null, 'A1');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }






    public function export($request, $response, $args)
    {

  /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource
        if (!$authorizer->checkAccess($currentUser, 'export_data')) {
            throw new ForbiddenException();
        }

        $params = $request->getParsedBody();

        Debug::debug("var params");
        Debug::debug(print_r($params,true));

        $tableId = $params['table'];
        $format = $params['format'];
        $columns = $params['columns'];


        $settings = $this->checkConfig($tableId);
        $table = $settings['table'];

        Debug::debug("var settings");
        Debug::debug(print_r($settings,true));

        //grab the data for only the selected columns
        $data = Capsule::table($table)->select($columns)->get();

        //gets data into array
        $array = json_decode(json_encode($data), true);


        // Spreadsheet style arrays
        $headerStyle = [
 'font' => [
   //set header to bold font
   'bold' => true,
 ],
];

        if ($params['borders'] == "true") {
            $styleArray = [
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
        } else {
            $styleArray = [
    'font' => [
        'bold' => false,
    ],
    'alignment' => [
        'horizontal' => Style\Alignment::HORIZONTAL_LEFT,
    ]
  ];
        };


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
->fromArray($columns, null, 'A1')
->fromArray($array, null, 'A2');

        $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();
        $highestColumn = $spreadsheet->getActiveSheet()->getHighestColumn();

        $spreadsheet->getActiveSheet()->getStyle("A1:".$highestColumn."1")->applyFromArray($headerStyle);
        $spreadsheet->getActiveSheet()->setShowGridlines(false);



        if ($format == 'xlsx') {
            $spreadsheet->getActiveSheet()->getStyle("A2:".$highestColumn.$highestRow)->applyFromArray($styleArray);

            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment;filename=\"$table-export.xlsx\"");
            header("Cache-Control: max-age=0");

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }

        if ($format == 'pdf') {
            header("Content-type:application/pdf");
            header("Content-Disposition:attachment;filename=\"$table-export.pdf\"");

            $spreadsheet->getActiveSheet()->getStyle("A2:".$highestColumn.$highestRow)->applyFromArray($styleArray);

            //set header row to repeat on each page
            $spreadsheet->getActiveSheet()
  ->setShowGridlines(true)
  ->getPageSetup()
  ->setRowsToRepeatAtTopByStartAndEnd(1, 1);

            $writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
            $writer->save('php://output');
        }


        if ($format == 'html') {
            header("Content-type:test/html");
            header("Content-Disposition:attachment;filename=\"$table-export.htm\"");

            $spreadsheet->getActiveSheet()->getStyle("A2:".$highestColumn.$highestRow)->applyFromArray($styleArray);

            //set header row to repeat on each page
            $spreadsheet->getActiveSheet()
  ->setShowGridlines(true)
  ->getPageSetup()
  ->setRowsToRepeatAtTopByStartAndEnd(1, 1);

            $writer = new Html($spreadsheet);
            $writer->save('php://output');
        }


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
        $tableId = $request->getQueryParam("table");

        $settings = $this->checkConfig($tableId);
        $table = $settings['table'];

        $columns = $this->getColumns($table);

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /*
            // Access-controlled resource - check that currentUser has permission to edit "permissions" field for this role
            if (!$authorizer->checkAccess($currentUser, 'update_role_field', [
                'role' => $role,
                'fields' => ['permissions']
            ])) {
                throw new ForbiddenException();
            }
        */
        return $this->ci->view->render($response, 'modals/export.html.twig', [
        'columns' => $columns['columns'],
        'notNullable' => $columns['notNullable'],
        'model' => $model,
        'table' => $table
    ]);
    }
}
