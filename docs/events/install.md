# Install Events

## `orchestra.install.schema`

Create a custom schema installation during Orchestra Installation. 
This schema will be available straight away even without 
activation of any extensions.
	
	Event::listen('orchestra.install.schema', function ()
	{
		Schema::create('foo', function ($table)
		{
			$table->increments('id');
			$table->string('name')->default('foobar');
		});
	});

## `orchestra.install.schema: users`

Add custom fields on `users` table, for example you might want to 
add phone number, address or other useful information without the 
need to have additional migrations for `users` table.

	Event::listen('orchestra.install.schema: users', function ($table)
	{
		$table->string('phone', 20);
	});

## `orchestra.install: user`

For each custom fields implemented in `orchestra.install.schema: users`, 
you might want to add default values for the administrator account.

	Event::listen('orchestra.install: user', function ($user, $input)
	{
		$user->phone = '0123456789';
	});

## `orchestra.install: acl`

Other than adding custom fields to user, you can also create additional roles 
and create custom acl for it.

	Event::listen('orchestra.install: acl', function ($acl)
	{
		Orchestra\Model\Role::create(array('name' => 'Developer'));
		$acl->add_role('Developer');
		$acl->add_action('manage website');
		$acl->allow('Developer', 'manager website');
	});