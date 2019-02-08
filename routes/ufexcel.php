<?php


/**
 * UFExcel
 */

 $app->group('/ufexcel', function () {
     $this->get('', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:pageList')
         ->setName('uri_ufexcel');
     })->add('authGuard');


 $app->group('/api/ufexcel', function () {

     $this->post('/config', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:createConfig');
     $this->post('/export', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:export');
     $this->post('/import', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:import');
     $this->post('/template', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getImportTemplate');
 })->add('authGuard');


$app->group('/modals/ufexcel', function () {
    $this->get('/create', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalCreateConfig');
    $this->get('/export', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalExport');
    $this->get('/import', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalImport');
    $this->get('/import/template', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalImportTemplate');
})->add('authGuard');
