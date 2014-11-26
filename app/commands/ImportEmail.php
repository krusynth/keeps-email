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
	    $rawEmail = "";
	    while (!feof($fd)) {
	        $rawEmail .= fread($fd, 1024);
	    }
	    fclose($fd);

		$parser = new Parser();
		$parser->setText($rawEmail);

		$from = $parser->getHeader('from');

		$recipients = array();

		$owner = $this->email_address_to_user($from);

		$recipients[] = $owner->id;

		foreach(explode(',', $parser->getHeader('to')) as $email)
		{
			$user = $this->email_address_to_user($email);
			$recipients[] = $user->id;
		}
		foreach(explode(',', $parser->getHeader('cc')) as $email)
		{
			$user = $this->email_address_to_user($email);
			$recipients[] = $user->id;
		}

		$recipients = array_unique($recipients);

		$message = new Message();
		$message->owner_id = $owner->id;

		$message->subject = $parser->getHeader('subject');
		$message->body = $parser->getMessageBody('text');

		$message->uuid = Uuid::generate(4);
		$message->save();


		$message->recipients()->attach($recipients);
		$message->push();
	}

	protected function email_address_to_user($email_address)
	{
		$email_address = trim($email_address);

		if(preg_match('/^(?:<name>.*?) \<(?:<email>.*?)\>$/', $email_address, $matches))
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
