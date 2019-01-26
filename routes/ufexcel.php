<?php


/**
 * UFExcel
 */
 $app->group('/api/ufexcel', function () {


     $this->post('/export', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:export');

     $this->post('/import', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:import');

     $this->post('/template', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getImportTemplate');

 })->add('authGuard');

$app->group('/modals/ufexcel', function () {

    $this->get('/export', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalExport');

    $this->get('/import', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalImport');

    $this->get('/import/template', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalImportTemplate');

})->add('authGuard');
