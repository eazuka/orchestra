<?php namespace Orchestra\Support;

use \Session,
	Laravel\Messages as M;

class Messages extends M {

	/**
	 * Add a message to the collector.
	 *
	 * <code>
	 *		// Add a message for the e-mail attribute
	 *		Message::make('email', 'The e-mail address is invalid.');
	 * </code>
	 *
	 * @static
	 * @param  string  $key
	 * @param  string  $message
	 * @return void
	 */
	public static function make($key, $message)
	{
		$instance = new static();
		$instance->add($key, $message);

		return $instance;
	}

	/**
	 * Retrieve Message instance from Session, the data should be in
	 * serialize, so we need to unserialize it first.
	 *
	 * @static
	 * @access public
	 * @return Messages
	 */
	public static function retrieve()
	{
		$message = null;

		if (Session::has('message'))
		{
			$message = @unserialize(Session::get('message', ''));
		}

		Session::forget('message');

		return $message;
	}

	/**
	 * Compile the instance into serialize
	 *
	 * @access public
	 * @return string   serialize of this instance
	 */
	public function serialize()
	{
		return serialize($this);
	}
}
