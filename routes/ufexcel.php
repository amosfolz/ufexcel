<?php


/**
 * UFExcel
 */

 $app->group('/ufexcel', function () {
     $this->get('', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:pageList')
         ->setName('uri_ufexcel');
     })->add('authGuard');


 $app->group('/api/ufexcel', function () {
     $this->get('', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getList');

     $this->post('/create', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:create');

     $this->put('/update/{tableid}', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:update');

     $this->put('/update/{tableid}/users', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:updateUsers');

     $this->get('/{tableid}/users', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getUsers');

     $this->post('/export/{tableid}', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:export');

     $this->post('/import/{tableid}', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:import');

     $this->post('/template/{tableid}', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getImportTemplate');

     $this->get('/features/{tableid}', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getTableFeatures');
 })->add('authGuard');


$app->group('/modals/ufexcel', function () {
    $this->get('/create', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalCreate');

    $this->get('/export', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalExport');

    $this->get('/import', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalImport');

    $this->get('/import/template', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalImportTemplate');

    $this->get('/edit', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalEdit');

    $this->get('/edit/users', 'UserFrosting\Sprinkle\Ufexcel\Controller\UFExcelController:getModalEditUsers');
})->add('authGuard');
