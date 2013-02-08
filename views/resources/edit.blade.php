@layout(locate('orchestra::layout.main'))

@section('content')

<div class="row-fluid">
	<div class="span12">
		<div class="page-header">
			<h2>{{ ! empty($_title_) ? $_title_ : 'Something Awesome Without A Name' }}
				@if ( ! empty($_description_))
				<small>{{ $_description_ ?: '' }}</small>
				@endif
			</h2>
		</div>

		{{ $form }}
	</div>
</div>

@endsection
