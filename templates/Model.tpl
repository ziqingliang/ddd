<?php

namespace {{namespace}};

use Illuminate\Database\Eloquent\Model;

/**
 * Class {{className}}
 * @package {{package}}
 *
{{properties}}
 *
{{defaults}}
 */
class {{className}} extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '{{table}}';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = '{{connection}}';

}
