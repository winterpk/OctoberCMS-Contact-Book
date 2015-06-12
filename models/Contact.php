<?php namespace Winterpk\Contactbook\Models;

use Model;

/**
 * contact Model
 */
class Contact extends Model
{
	
	use \October\Rain\Database\Traits\Validation;
	
    /**
     * @var string The database table used by the model.
     */
    public $table = 'winterpk_contactbook_contacts';
	
	public $rules = [
		'first_name' => 'required|min:1|max:32',
		'last_name' => 'required|min:1|max:32',
		'email' => 'required|email',
		'street_address' => 'min:1|max:256|required_with:city,state,zip',
		'city' => 'alpha_dash|min:1|max:124|required_with:street_address,state,zip',
		'state' => 'alpha_dash|min:1|max:124|required_with:street_address,city,zip',
		'zip' => 'alpha_dash|min:1|max:124|required_with:street_address,city,state',
		'phone' => 'min:1|max:24'
	];
	

	
	public $messages = [];
	
    /**
     * @var array Guarded fields
     */
    protected $guarded = [
	    'user_id', 
	    'id'
    ];
	
    /**
     * @var array Fillable fields
     */
	protected $fillable = [
		'first_name',
		'last_name',
		'email',
		'street_address',
		'city',
		'state',
		'zip',
		'phone',
	];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}