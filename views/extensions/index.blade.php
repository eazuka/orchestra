@layout('orchestra::layout.main')

@section('content')

<div class="row-fluid">
	
	<div class="page-header">
		<h2>{{ __('orchestra::title.extensions.list') }}</h2>
	</div>

	<table class="table table-bordered table-stripped">
		<thead>
			<tr>
				<th>Extension</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			@forelse ($extensions as $name => $extension)
			<tr>
				<td>
					<strong>{{ $extension->name }}</strong>
					<span class="pull-right">
						@if ( ! Orchestra\Extension::started($name))
							{{ HTML::link('orchestra/extensions/activate/'.$name, 'Activate', array('class' => 'btn btn-primary btn-mini')) }}
						@else
							{{ HTML::link('orchestra/extensions/deactivate/'.$name, 'Deactivate', array('class' => 'btn btn-warning btn-mini')) }}

						@endif
					</span>
				</td>
				<td>
					<p>{{ $extension->description }}</p>

					<span class="meta">
						Version {{ $extension->version }} | By {{ HTML::link($extension->url ?: '#', $extension->author) }}
					</span>
				</td>
			</tr>
			@empty
			<tr>
				<td colspan="2">No data</td>
			</tr>
			@endforelse
		</tbody>
	</table>

</div>

@endsection