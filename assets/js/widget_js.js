/**
 * Create and register the widget javascript for the email alerts plugin
 *
 * @dependancy jQuery
 */
jQuery(document).ready(function($) {
	//Init modal
	$('#wp_digest_widget_modal').easyModal({
		top: 200,
		overlay: 0.2
	});
	$('#wp_digest_widget_modal_close').click(function(e) {
		$("#wp_digest_widget_modal").trigger('closeModal');
	});

	//First Form submit
	$('#wp_digest_widget_form_dummy').submit(function() {
		//Copy email
		var dummy_email = $('#wp_digest_widget_form_dummy #wp_digest_widget_form_dummy_email').val();
		$('#wp_digest_widget_form #wp_digest_widget_form_email').val(dummy_email);

		//Copy frequency
		var dummy_frequency = $('#wp_digest_widget_form_dummy #wp_digest_widget_form_dummy_frequency').val();
		$('#wp_digest_widget_form #wp_digest_widget_form_frequency').val(dummy_frequency);

		//Open modal
		$("#wp_digest_widget_modal").trigger('openModal');
		return false;
	});

	//Open modal at page load
	if(window.location.hash && window.location.hash === '#wp_digest_widget_modal') {
		$('#wp_digest_widget_modal').trigger('openModal');
	}
});