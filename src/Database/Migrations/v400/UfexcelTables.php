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
