<?php
/**
 *
 * @link      https://github.com/amosfolz/ufexcel
 * @license   https://github.com/amosfolz/ufexcel/blob/master/LICENSE
 */
namespace UserFrosting\Sprinkle\Ufexcel\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\System\Bakery\Migration;
use UserFrosting\Sprinkle\Ufexcel\Database\Models\UfexcelTable;

/**
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @extends Migration
 * @author Amos Folz (https://alexanderweissman.com)
 */
class UfexcelTables extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('ufexcel_tables')) {
            $this->schema->create('ufexcel_tables', function (Blueprint $table) {
                $table->increments('id');
                $table->string('tableid', 30)->unique(); //This is the client-side table-id
                $table->string('dbtable', 30); // This is the db table name
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
            // Add default UserFrosting tables
            $tables = [
                'table-activities' => new UfexcelTable([
                    'tableid' => 'table-activities',
                    'dbtable' => 'activities'
                ]),
                'table-groups' => new UfexcelTable([
                    'tableid' => 'table-groups',
                    'dbtable' => 'groups'
                ]),
                'table-roles' => new UfexcelTable([
                    'tableid' => 'table-roles',
                    'dbtable' => 'roles'
                ]),
                'table-users' => new UfexcelTable([
                    'tableid' => 'table-users',
                    'dbtable' => 'users'
                ])
            ];

            foreach ($tables as $table => $ufexcelTable) {
                $ufexcelTable->save();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('ufexcel_tables');
    }
}
