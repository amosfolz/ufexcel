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
class UfexcelUsers extends Migration
{

  public $dependencies = [
      '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable'
  ];
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('ufexcel_users')) {
            $this->schema->create('ufexcel_users', function (Blueprint $table) {
              $table->integer('table_id')->unsigned();
              $table->integer('user_id')->unsigned();
              $table->boolean('import')->default(0);
              $table->boolean('export')->default(0);
              $table->timestamps();

              $table->engine = 'InnoDB';
              $table->collation = 'utf8_unicode_ci';
              $table->charset = 'utf8';
              $table->primary(['table_id', 'user_id']);
              $table->foreign('table_id')->references('id')->on('ufexcel_tables')->onDelete('cascade');
              $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
              $table->index('table_id');
              $table->index('user_id');
            });
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('ufexcel_users');
    }
}
