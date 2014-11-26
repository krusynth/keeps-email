<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersMessages extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function($table)
	    {
	        $table->increments('id');
	        $table->string('uuid')->unique();
	        $table->string('email')->unique();
	        $table->string('name');
	        $table->timestamps();
	    });
		Schema::create('messages', function($table)
	    {
	        $table->increments('id');
			$table->string('uuid')->unique();
	        $table->integer('parent_id')->unsigned()->nullable();
			$table->foreign('parent_id')->references('id')->on('messages');
			$table->string('message_id');
			$table->string('references')->nullable();
			$table->dateTime('sent_datetime');
	        $table->integer('owner_id')->unsigned();
			$table->foreign('owner_id')->references('id')->on('users');
			$table->text('body');
			$table->timestamps();
	    });
		Schema::create('message_user', function($table)
	    {
	        $table->increments('id');
	        $table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
	        $table->integer('message_id')->unsigned();
			$table->foreign('message_id')->references('id')->on('messages');
	        $table->timestamps();
	    });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('message_user');
		Schema::drop('messages');
		Schema::drop('users');
	}

}
