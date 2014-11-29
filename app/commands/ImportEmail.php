<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use MimeMailParser\Parser;
use Webpatser\Uuid\Uuid;

class ImportEmail extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'email:import';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// read from stdin
	    $fd = fopen("php://stdin", "r");
	    $email = "";
	    while (!feof($fd)) {
	        $email .= fread($fd, 1024);
	    }
	    fclose($fd);

	    $message = $this->createMessage($email);
	}

	protected function createMessage($email)
	{
		$parser = new Parser();
		$parser->setText($email);

		$recipients = array();

		$sender = $this->getUserByEmail($parser->getHeader('from'));
		$recipients[] = $sender->id;

		if(strlen($parser->getHeader('to')))
		{
			foreach(explode(',', $parser->getHeader('to')) as $email)
			{
				$user = $this->getUserByEmail($email);
				$recipients[] = $user->id;
			}
		}

		if(strlen($parser->getHeader('cc')))
		{
			foreach(explode(',', $parser->getHeader('cc')) as $email)
			{
				$user = $this->getUserByEmail($email);
				$recipients[] = $user->id;
			}
		}

		$recipients = array_unique($recipients);

		$message = new Message();
		$message->sender_id = $sender->id;

		$message->message_identifier = $parser->getHeader('message-id');

		$message->subject = $parser->getHeader('subject');
		$message->body = $parser->getMessageBody('text');

		$message->uuid = Uuid::generate(4);

		$thread = $this->getThread($message, $recipients);
		$message->thread_id = $thread->id;

		$message->save();
	}

	/**
	 * Find the user that matches the email address, or create them.
	 */
	protected function getUserByEmail($email_address)
	{
		$email_address = trim($email_address);

		if(preg_match('/^(?P<name>.*?) \<(?P<email>.*?)\>$/', $email_address, $matches))
		{
			$name = $matches['name'];
			$email = $matches['email'];
		}
		else
		{
			$name = $email_address;
			$email = $email_address;
		}

		$user = User::where('email', $email)->first();

		if(!$user)
		{
			$user = new User();
			$user->uuid = Uuid::generate(4);
			$user->name = $name;
			$user->email = $email;
			$user->save();
		}

		return $user;
	}

	/**
	 * Find the correct thread, or create one.
	 */
	protected function getThread($message, $recipients)
	{
		$references = explode(' ', $message->references);

		$thread = Thread::whereIn('message_identifier', $references)
			->whereIn('owner_id', $recipients)
			->take(1)
			->first();

		if(!$thread)
		{
			$thread = new Thread();
			$thread->owner_id = $message->sender_id;
			$thread->uuid = Uuid::generate(4);
			$thread->message_identifier = $message->message_identifier;
			$thread->save();
		}

		$thread->updateRecipients($recipients);

		return $thread;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			//array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
