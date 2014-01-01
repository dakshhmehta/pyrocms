<?php namespace Pyro\Module\Users\Model; 

use Pyro\Module\Streams_core\EntryModel;

/**
 * Profile model for the users module.
 * 
 * @author      PyroCMS Dev Team
 * @package     PyroCMS\Core\Modules\User\Models
 */
class Profile extends EntryModel
{
    /**
     * Define the table name
     *
     * @var string
     */
    protected $stream_slug = 'profiles';

    protected $stream_namespace = 'users';

    protected $table = 'profiles';

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    public function user()
    {
    	return $this->belongsTo('Pyro\Module\Users\Model\User');
    }
}
