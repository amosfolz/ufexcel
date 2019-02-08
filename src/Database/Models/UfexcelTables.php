<?php
/**
 *
 *
 */
namespace UserFrosting\Sprinkle\Ufexcel\Database\Models;

use UserFrosting\Sprinkle\Core\Database\Models\Model;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * Route Class
*/
class UfexcelTables extends Model {



    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'ufexcel_tables';



    public $timestamps = true;

    /**
     * Get all of the owning models.
     */
    public function parent()
    {
        return $this->morphTo();
    }


    public function users()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsToMany($classMapper->getClassMapping('users'), 'ufexcel_users', 'ufexcel_id', 'user_id')->withPivot('import', 'export');
    }









}
