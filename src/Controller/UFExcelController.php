<?php


/* UF Laravel Excel Sprinkle (http://www.userfrosting.com) */

namespace UserFrosting\Sprinkle\Ufexcel\Controller;


use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
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

class UFExcelController extends SimpleController {



public function import ($request, $response, $args) {

$uploadedFiles = $request->getUploadedFiles();
$uploadedFile = $uploadedFiles['importFile'];

$classMapper = $this->ci->classMapper;

$currentUser = $this->ci->currentUser;

// POST parameters


$table = $request->getParsedBodyParam('table');
$model = $classMapper->getClassMapping($table);



//$table = $classMapper->getClassMapping($request->getParsedBodyParam("table"));


  /** Load $inputFileName to a Spreadsheet Object  **/
$spreadsheet = IOFactory::load($uploadedFile->file);

$worksheet = $spreadsheet->getActiveSheet();

$rows = $worksheet->toArray();


//Grab the header row so array only contains data
$keys = array_shift($rows);

//convert spreadsheet to array
foreach ($rows as $key => $value){
    $rowsArray[] = array_combine($keys, $value);
}

/*
Capsule::transaction( function() use($rowsArray, $currentUser, $classMapper)  {
   $address = new Address($data);
   $address->longitude = $longitude;
   $address->latitude = $latitude;
   $address->location = (array($longitude,$latitude));
   $currentUser->addresses()->save($address);

});
*/

}


public function getModalImport ($request, $response, $args) {

//  $table = $request->getQueryParams('table-name');

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
  return $this->ci->view->render($response, 'modals/import.html.twig');

}



public function getModalImportTemplate ($request, $response, $args) {
  // GET parameters
$model = $request->getQueryParam("model");
$table = $request->getQueryParam("table");

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
      'requiredColumns' => $requiredColumns
  ]);


}

public function getImportTemplate ($request, $response, $args) {



  $params = $request->getParsedBodyParam('columns');

  $table = $request->getQueryParam('table');

    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=\"template.xlsx\"");
    header("Cache-Control: max-age=0");

    $spreadsheet = new Spreadsheet();
    $spreadsheet->getActiveSheet()
    ->fromArray($params, NULL, 'A1');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}




public function export($request, $response, $args) {

$params = $request->getParsedBody();

//$columns = $params['columns'];
$table = $params['table'];
$model = $params['model'];
$format = $params['format'];
$columns = $params['columns'];


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


  if($params['borders'] == "true"){
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
}

else{$styleArray = [
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
->fromArray($array, null,'A2');

$highestRow = $spreadsheet->getActiveSheet()->getHighestRow();
$highestColumn = $spreadsheet->getActiveSheet()->getHighestColumn();

$spreadsheet->getActiveSheet()->getStyle("A1:".$highestColumn."1")->applyFromArray($headerStyle);
$spreadsheet->getActiveSheet()->setShowGridlines(false);



if ($params['format'] == 'xlsx'){

$spreadsheet->getActiveSheet()->getStyle("A2:".$highestColumn.$highestRow)->applyFromArray($styleArray);

  header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
  header("Content-Disposition: attachment;filename=\"$table-export.xlsx\"");
  header("Cache-Control: max-age=0");

  $writer = new Xlsx($spreadsheet);
}

if ($params['format'] == 'pdf'){

  header("Content-type:application/pdf");
  header("Content-Disposition:attachment;filename=\"$table-export.pdf\"");

$spreadsheet->getActiveSheet()->getStyle("A2:".$highestColumn.$highestRow)->applyFromArray($styleArray);

//set header row to repeat on each page
$spreadsheet->getActiveSheet()
  ->setShowGridlines(true)
  ->getPageSetup()
  ->setRowsToRepeatAtTopByStartAndEnd(1,1);

$writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
}


if ($params['format'] == 'html'){

  header("Content-type:test/html");
  header("Content-Disposition:attachment;filename=\"$table-export.htm\"");

$spreadsheet->getActiveSheet()->getStyle("A2:".$highestColumn.$highestRow)->applyFromArray($styleArray);

//set header row to repeat on each page
$spreadsheet->getActiveSheet()
  ->setShowGridlines(true)
  ->getPageSetup()
  ->setRowsToRepeatAtTopByStartAndEnd(1,1);

$writer = new Html($spreadsheet);



}





$writer->save('php://output');
}




  // Grabs the appropriate model and returns all available columns for that model.
  public function getColumns($table) {


      $sm = Capsule::getDoctrineSchemaManager();
      $tableColumns = $sm->listTableColumns($table);
      Debug::debug("var tableColumns");
      Debug::debug(print_r($tableColumns, true));

      foreach ($tableColumns as $tableColumn) {
          $columns['columns'][] = $tableColumn->getName();

          if($tableColumn->getNotnull() == 1){
            $columns['notNullable'][] = $tableColumn->getName();
          }
          if($tableColumn->getAutoincrement() == 1){
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
 */


public function getModalExport($request, $response, $args) {

    // GET parameters
    $model = $request->getQueryParam("model");
    $table = $request->getQueryParam("table");




    $columns = $this->getColumns($table);


  Debug::debug("var columns");
  Debug::debug(print_r($columns, true));

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
