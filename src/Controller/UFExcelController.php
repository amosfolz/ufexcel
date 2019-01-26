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
use PhpOffice\PhpSpreadsheet\IOFactory;

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

Debug::debug("var table");
Debug::debug(print_r($table, true));

  /** Load $inputFileName to a Spreadsheet Object  **/
  $spreadsheet = IOFactory::load($uploadedFile->file);


$worksheet = $spreadsheet->getActiveSheet();
//Debug::debug("var worksheet");
//Debug::debug(print_r($worksheet, true));


$rows = $worksheet->toArray();
Debug::debug("var rows");
Debug::debug(print_r($rows, true));

//Grab the header row so array only contains data
$keys = array_shift($rows);
Debug::debug("var dataRows");
Debug::debug(print_r($dataRows, true));

Debug::debug("var rows after array_shift");
Debug::debug(print_r($rows, true));

foreach ($rows as $key => $value){
  Debug::debug("foreach var key");
  Debug::debug(print_r($key, true));
  Debug::debug("foreach var value");
  Debug::debug(print_r($value, true));

  //  $values =
    $rowsArray[] = array_combine($keys, $value);
    Debug::debug("var rowsToArray");
    Debug::debug(print_r($rowsArray, true));
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

  $table = $request->getQueryParams('table-name');

  Debug::debug("getModalImport var table");
  Debug::debug(print_r($table,true));


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
$table = $request->getQueryParam("table");

$requiredColumns = $this->getNullableColumns($table);

$columns = array_diff($this->getColumns($table), $requiredColumns);


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
      'columns' => $columns,
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

$params = $request->getParsedBodyParam('columns');
$table = $request->getParsedBodyParam('table');

$classMapper = $this->ci->classMapper;

$model = $classMapper->getClassMapping($table);

//$model = new Vehicle;

//select the model columns
$modelColumns =  $model::select($params)->get();

foreach ($params as $p){
  $modelColumns->makeVisible($p);
};

$modelColumns->toArray();

$data = Capsule::table('vehicles')->select($params)->get();

//hack
$array = json_decode(json_encode($data), true);

  header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
  header("Content-Disposition: attachment;filename=\"export.xlsx\"");
  header("Cache-Control: max-age=0");

  $spreadsheet = new Spreadsheet();
  $spreadsheet->getActiveSheet()
  ->fromArray($params, NULL, 'A1')
  ->fromArray($array, NULL,'A2');

  $writer = new Xlsx($spreadsheet);
  $writer->save('php://output');

}




  // Grabs the appropriate model and returns all available columns for that model.
  //Exporter
  public function getColumns($table) {


      $classMapper = $this->ci->classMapper;

      $model = $classMapper->getClassMapping($table);

      $newModel = new $model;

      $columns = $newModel->getTableColumns();

  return $columns;
  }

  // Grabs the appropriate model and returns all available columns for that model.
  //Exporter
  public function getNullableColumns($table) {


      $classMapper = $this->ci->classMapper;

      $model = $classMapper->getClassMapping($table);

      $newModel = new $model;

      $nullableColumns = $newModel->getNullable();

  return $nullableColumns;
  }



/*
// Accepts an array of model and columns to be included in export
public function getColumns($columns){

  $columns = $model->getTableColumns();

  $addresses = Address::all()
    ->makeVisible('latitude')
    ->makeVisible('longitude')
    ->makeHidden('created_at')
    ->makeHidden('updated_at')
    ->toArray();


  Debug::debug(print_r($addresses, true));


}

*/


/**
 * Renders the modal form for editing the pickup lists an address is assigned to.
 *
 * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
 * This page requires authentication.
 * Request type: GET
 */







public function getModalExport($request, $response, $args) {

    // GET parameters
    $table = $request->getQueryParam("table");

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
    return $this->ci->view->render($response, 'modals/exporter.html.twig', [
        'columns' => $columns
    ]);
}






}
