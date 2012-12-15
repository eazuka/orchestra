<?php

use Orchestra\Form,
	Orchestra\Messages,
	Orchestra\View;

class Orchestra_Account_Controller extends Orchestra\Controller {

	/**
	 * Construct Account Controller to allow user to update own profile.
	 * Only authenticated user should be able to access this controller.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->filter('before', 'orchestra::auth');
	}

	/**
	 * Edit User Profile Page
	 *
	 * GET (:bundle)/account
	 *
	 * @access public
	 * @return Response
	 */
	public function get_index()
	{
		$user = Auth::user();

		$form = Form::of('orchestra.account', function ($form) use ($user)
		{
			$form->row($user);
			$form->attr(array(
				'action' => handles('orchestra::account/index'),
				'method' => 'POST',
			));

			$form->hidden('id');

			$form->fieldset(function ($fieldset)
			{
				$fieldset->control('input:text', 'email', function ($control)
				{
					$control->label = __('orchestra::label.users.email');
				});

				$fieldset->control('input:text', 'fullname', function ($control)
				{
					$control->label = __('orchestra::label.users.fullname');
				});
			});
		});

		Event::fire('orchestra.form: user.account', array($user, $form));

		$data = array(
			'eloquent' => $user,
			'form'     => $form,
			'_title_'  => __("orchestra::title.account.profile"),
		);

		return View::make('orchestra::account.index', $data);
	}

	/**
	 * POST Edit User Profile
	 *
	 * POST (:bundle)/account
	 *
	 * @access public
	 * @return Response
	 */
	public function post_index()
	{
		$input = Input::all();
		$rules = array(
			'email'    => array('required', 'email'),
			'fullname' => array('required'),
		);

		if (Auth::user()->id !== $input['id']) return Response::error('500');

		Event::fire('orchestra.validate: user.account', array(& $rules));

		$msg = new Messages;
		$val = Validator::make($input, $rules);

		if ($val->fails())
		{
			return Redirect::to(handles('orchestra::account'))
					->with_input()
					->with_errors($val);
		}

		$user           = Auth::user();
		$user->email    = $input['email'];
		$user->fullname = $input['fullname'];

		try
		{
			// Reference to self.
			$self = $this;

			DB::transaction(function () use ($user, $self)
			{
				$self->fire_event('updating', $user);
				$self->fire_event('saving', $user);

				$user->save();

				$self->fire_event('updated', $user);
				$self->fire_event('saved', $user);
			});


			$msg->add('success', __('orchestra::response.account.profile.update'));
		}
		catch (Exception $e)
		{
			$msg->add('error', __('orchestra::response.db-failed', array(
				'error' => $e->getMessage(),
			)));
		}

		return Redirect::to(handles('orchestra::account'))
				->with('message', $msg->serialize());
	}

	/**
	 * Edit Password Page
	 *
	 * GET (:bundle)/account/password
	 *
	 * @access public
	 * @return Response
	 */
	public function get_password()
	{
		$data = array(
			'eloquent' => Auth::user(),
			'_title_'  => __("orchestra::title.account.password"),
		);

		return View::make('orchestra::account.password', $data);
	}

	/**
	 * POST Edit User Password
	 *
	 * POST (:bundle)/account/password
	 *
	 * @access public
	 * @return Response
	 */
	public function post_password()
	{
		$input = Input::all();
		$rules = array(
			'current_password' => array(
				'required',
			),
			'new_password'     => array(
				'required',
				'different:current_password',
			),
			'confirm_password' => array(
				'same:new_password',
			),
		);

		$val = Validator::make($input, $rules);

		if ($val->fails())
		{
			return Redirect::to(handles('orchestra::account/password'))
					->with_input()
					->with_errors($val);
		}

		$msg  = new Messages;
		$user = Auth::user();

		if (Hash::check($input['current_password'], $user->password))
		{
			$user->password = $input['new_password'];

			try
			{
				// Reference to self.
				$self = $this;

				DB::transaction(function () use ($user, $self)
				{
					$user->save();
				});

				$msg->add('success', __('orchestra::response.account.password.update'));
			}
			catch (Exception $e)
			{
				$msg->add('error', __('orchestra::response.db-failed'));
			}
		}
		else
		{
			$msg->add('error', __('orchestra::response.account.password.invalid'));
		}

		return Redirect::to(handles('orchestra::account/password'))
				->with('message', $msg->serialize());
	}

	/**
	 * Fire Event related to eloquent process
	 *
	 * @access private
	 * @param  string   $type
	 * @param  Eloquent $user
	 * @return void
	 */
	private function fire_event($type, $user)
	{
		Event::fire("orchestra.{$type}: user.account", array($user));
	}
}
