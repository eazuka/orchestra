<?php 

use Orchestra\Form, 
	Orchestra\Messages, 
	Orchestra\Table,
	Orchestra\Model\Role, 
	Orchestra\Model\User;

class Orchestra_Users_Controller extends Orchestra\Controller
{
	/**
	 * Construct Users Controller with some pre-define configuration 
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->filter('before', 'orchestra::manage-users');
	}

	/**
	 * List All Users Page
	 *
	 * @access public
	 * @return Response
	 */
	public function get_index()
	{
		// Get Users (with roles) and limit it to only 30 results for 
		// pagination. Don't you just love it when pagination simply works.
		$users = User::with('roles')->paginate(30);

		// Build users table HTML using a schema liked code structure.
		Table::of('orchestra.users', function ($table) use ($users) 
		{
			// Add HTML attributes option for the table.
			$table->attr('class', 'table table-bordered table-striped');

			// attach Model and set pagination option to true
			$table->with($users, true);

			// Add columns
			$table->column('id');
			$table->column('Fullname', 'fullname');
			$table->column('email', function ($column) 
			{
				$column->heading = 'E-mail Address';
				$column->value   = function ($row) 
				{
					return $row->email;
				};
			});

			$table->column('action', function ($column) 
			{
				$column->heading = '';
				$column->value   = function ($row) 
				{
					$btn = array(
						'<div class="btn-group">',
						'<a class="btn btn-mini" href="'.URL::to('orchestra/users/view/'.$row->id).'">Edit</a>',
						Auth::user()->id !== $row->id ? '<a class="btn btn-mini btn-danger" href="'.URL::to('orchestra/users/delete/'.$row->id).'">Delete</a>' : '',
						'</div>',
					);

					return implode('', $btn);
				};
			});
		});

		Event::fire('orchestra.list: users', array($users));

		$data = array(
			'eloquent'      => $users,
			'table'         => Table::of('orchestra.users'),
			'resource_name' => 'Users',
		);

		return View::make('orchestra::resources.index', $data);
	}

	/**
	 * GET A User (either create or update)
	 *
	 * @access public
	 * @param  integer $id
	 * @return Response
	 */
	public function get_view($id = null) 
	{
		$user = User::find($id);

		if (is_null($user)) $user = new User;

		Form::of('orchestra.users', function ($form) use ($user)
		{
			$form->row($user);
			$form->attr(array(
				'action' => URL::to('orchestra/users/view/'.$user->id),
				'method' => 'POST',
			));

			$form->fieldset(function ($fieldset) 
			{
				$fieldset->control('input:text', 'E-mail Address', 'email');
				$fieldset->control('input:text', 'fullname');

				$fieldset->control('input:password', 'password', function ($control) 
				{
					$control->field = function ($row, $control) 
					{
						return \Form::password($control->name);
					};
				});

				$fieldset->control('select', 'roles', function ($control) 
				{
					$options = array();

					foreach (Role::all() as $role) 
					{
						$options[$role->id] = $role->name;
					}

					$control->field = function ($row, $self) use ($options) 
					{
						// get all the user roles from objects
						$roles = array();

						foreach ($row->{$self->name} as $row) 
						{
							$roles[] = $row->id;
						}

						return \Form::select('roles[]', $options, $roles, array('multiple' => true));
					};
				});
			});
		});

		Event::fire('orchestra.form: users', array($user));

		$data = array(
			'eloquent'      => $user,
			'form'          => Form::of('orchestra.users'),
			'resource_name' => 'User',
		);

		return View::make('orchestra::resources.edit', $data);
	}

	/**
	 * POST A User (either create or update)
	 *
	 * @access public
	 * @param  integer $id
	 * @return Response
	 */
	public function post_view($id = null) 
	{
		$input = Input::all();
		$rules = array(
			'email'    => array('required', 'email'),
			'fullname' => array('required'),
			'roles'    => array('required'),
		);

		$v = Validator::make($input, $rules);

		if ($v->fails())
		{
			return Redirect::to('orchestra/users/view/'.$id)
					->with_input()
					->with_errors($v);
		}

		$type  = 'updated';
		$user  = User::find($id);

		if (is_null($user)) 
		{
			$type = 'created';
			$user = new User(array(
				'password' => Hash::make($input['password'] ?: ''),
			));
		}

		$user->fullname = $input['fullname'];
		$user->email    = $input['email'];
		
		if ( ! empty($input['password'])) 
		{
			$user->password = Hash::make($input['password']);
		}

		$m = new Messages;

		try
		{
			DB::transaction(function () use ($user, $input)
			{
				$user->save();
				$user->roles()->sync($input['roles']);
			});

			$m->add('success', __("orchestra::response.users.{$type}"));
		}
		catch (Exception $e)
		{
			$m->add('error', __('orchestra::response.db-failed', array('error' => $e->getMessage())));
		}

		return Redirect::to('orchestra/users')
				->with('message', $m->serialize());
	}

	/**
	 * GET Delete a User
	 *
	 * @access public
	 * @param  integer $id
	 * @return Response
	 */
	public function get_delete($id = null)
	{
		$user = User::find($id);

		if (is_null($id)) return Event::fire('404');

		if ($user->id === Auth::user()->id) return Event::fire('404');

		$user->delete();

		$m = Messages::make('success', __('orchestra::response.users.deleted'));

		return Redirect::to('orchestra/user')
				->with('message', $m->serialize());
	}
}