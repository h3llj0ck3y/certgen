(function ($) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$(function () {

		$('.generate_pdf').click(function (e) {
			var data = {
				action: 'replace_tpl_vars',
				product_id: woocommerce_admin_meta_boxes.post_id,
				cert_title: $('#cert_title').val()
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post(ajaxurl, data, function (response) {

				window.open(response, '_blank');
				var a = document.createElement('a');
				a.href= "data:application/octet-stream;base64,"+response;
				a.target = '_blank';
				a.download = 'filename.pdf';
				// window.open(response);
				// a.click();
			}).success(function (e) {
				console.log(e);
				
			}).fail(function (xhr, status, error) {
				console.log(error);
				console.log(status);
				
			}).error(function (xhr, status, error) {
				console.log(error);
				console.log(status);
				

			});
			e.preventDefault();
		});

	});
})(jQuery);
