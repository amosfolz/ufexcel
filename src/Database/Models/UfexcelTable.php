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
class UfexcelTable extends Model {



    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'ufexcel_tables';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'tableid',
        'dbtable'
    ];



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

        return $this->belongsToMany($classMapper->getClassMapping('user'), 'ufexcel_users', 'table_id', 'user_id')->withPivot('import', 'export')->withTimestamps();
    }

    public function scopeForUsers($query, $userId)
    {
        return $query->join('ufexcel_users', function ($join) use ($userId) {
            $join->on('ufexcel_users.table_id', 'ufexcel_tables.id')
                 ->where('user_id', $userId)
                 ->join('users', 'ufexcel_users.user_id', '=', 'users.id');
        });
    }







}
