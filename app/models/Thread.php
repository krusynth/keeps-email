<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class Thread extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'threads';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	//protected $hidden = array('password', 'remember_token');

	public function owner()
	{
		return $this->hasOne('User');
	}

	public function recipients()
	{
		 return $this->belongsToMany('User');
	}

	/**
	 * Shortcut for adding recipients without duplicates.
	 * Does *not* remove recipients!
	 */
	public function updateRecipients($recipients)
	{
		// We can't attach the relational models until after we've saved.
		$old_recipients = $this->recipients()->lists('thread_user.user_id');

		$new_recipients = array_diff($recipients, $old_recipients);
		if(count($new_recipients))
		{
			$this->recipients()->attach($new_recipients);
		}

		$this->push();
	}

	public function messages()
	{
		return $this->hasMany('Message');
	}

}
