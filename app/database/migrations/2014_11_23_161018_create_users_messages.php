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
	        $table->boolean('invite_sent');
	        $table->timestamps();
	    });
		Schema::create('threads', function($table)
	    {
	        $table->increments('id');
			$table->string('uuid')->unique();
			$table->string('message_identifier');//->unique();
	        $table->integer('owner_id')->unsigned();
			$table->foreign('owner_id')->references('id')->on('users');
			$table->index('owner_id');
			$table->timestamps();
	    });
		Schema::create('messages', function($table)
	    {
	        $table->increments('id');
			$table->string('uuid')->unique();
	        $table->integer('thread_id')->unsigned()->nullable();
			$table->foreign('thread_id')->references('id')->on('threads');
			$table->index('thread_id');
	        $table->integer('parent_id')->unsigned()->nullable();
			$table->foreign('parent_id')->references('id')->on('messages');
			$table->index('parent_id');
			$table->string('message_identifier');//->unique();
			$table->string('references')->nullable();
			$table->dateTime('sent_datetime');
	        $table->integer('sender_id')->unsigned();
			$table->foreign('sender_id')->references('id')->on('users');
			$table->index('sender_id');
			$table->string('subject', 1024);
			$table->text('body');
			$table->timestamps();
	    });
		Schema::create('thread_user', function($table)
	    {
	        $table->increments('id');
	        $table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
			$table->index('user_id');
	        $table->integer('thread_id')->unsigned();
			$table->foreign('thread_id')->references('id')->on('threads');
			$table->index('thread_id');
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
		// Disable foreign key constraints.
		Schema::table('thread_user', function($table)
		{
			$table->dropForeign('thread_user_user_id_foreign');
			$table->dropForeign('thread_user_thread_id_foreign');
		});
		Schema::table('messages', function($table)
		{
			$table->dropForeign('messages_thread_id_foreign');
			$table->dropForeign('messages_parent_id_foreign');
			$table->dropForeign('messages_sender_id_foreign');

		});
		Schema::table('threads', function($table)
		{
			$table->dropForeign('threads_owner_id_foreign');
		});

		// Drop the tables.
		Schema::drop('thread_user');
		Schema::drop('messages');
		Schema::drop('threads');
		Schema::drop('users');
	}

}
