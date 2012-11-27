@layout('orchestra::layout.main')

@section('content')

<div class="row-fluid">
	<div class="span8">
		<div class="page-header">
			<h2>{{ ! empty($_title_) ? $_title_ : 'Something Awesome Without A Name' }}
				@if ( ! empty($_description_))
				<small>{{ $_description_ ?: '' }}</small>
				@endif
			</h2>
		</div>

		{{ $form }}
	</div>

	<div class="span4">
		@placeholder('orchestra.settings')
		@placeholder('orchestra.helps')
	</div>
</div>

<script>
	jQuery(function onSettingPageReady ($) { 'use strict';
		var ev, emailDefault;

		ev           = Javie.Events.make();
		emailDefault = $('select[name="email_default"]');

		ev.listen('setting.changed: email.default', function (e, self) {
			var value = self.value ? self.value : '';

			$('input[name^="email_smtp"], input[name^="email_sendmail"]')
				.parent().parent().hide();

			switch (value) {
				case 'smtp' :
					$('input[name^="email_smtp"]').parent().parent().show();
					break;
				case 'sendmail' :
					$('input[name^="email_sendmail"]').parent().parent().show();
					break;
			}
		});

		emailDefault.on('change', function onChangeEmailDefault (e) {
			ev.fire('setting.changed: email.default', [e, this]);
		});

		// lets trigger an onChange event.
		emailDefault.trigger('change');
	});
</script>

@endsection
