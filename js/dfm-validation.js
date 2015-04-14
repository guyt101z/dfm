jQuery(document).ready(function($) {
	// !Validate each form on the page
	$( '.dynamic-form-maker' ).each( function() {
		$( this ).validate({
			rules: {
				"dfm-secret":{
					required: true,
					digits: true,
					maxlength:2
				}
			},
			errorClass : 'dfm-error',
			errorPlacement: function(error, element) {
				if ( element.is( ':radio' ) || element.is( ':checkbox' ) )
					error.appendTo( element.parent().parent() );
				else if ( element.is( ':password' ) )
					error.hide();
				else
					error.insertAfter( element );
			}
		});
	});

	// Force bullets to hide, but only if list-style-type isn't set
	$( '.dynamic-form-maker li:not(.dfm-item-instructions li, .dfm-span li)' ).filter( function(){
		return $( this ).css( 'list-style-type' ) !== 'none';
	}).css( 'list-style', 'none' );

	// !Display jQuery UI date picker
	$( '.dfm-date-picker' ).each( function(){
		var dfm_dateFormat = $( this ).attr( 'data-dp-dateFormat' ) ? $( this ).attr( 'data-dp-dateFormat' ) : 'mm/dd/yy';

		$( this ).datepicker({
			dateFormat: dfm_dateFormat
		});
	});

	// !Custom validation method to check multiple emails
	$.validator.addMethod( 'phone', function( value, element ) {
		// Strip out all spaces, periods, dashes, parentheses, and plus signs
		value = value.replace(/[\+\s\(\)\.\-\ ]/g, '');

		return this.optional(element) || value.length > 9 &&
			value.match( /^((\+)?[1-9]{1,2})?([-\s\.])?((\(\d{1,4}\))|\d{1,4})(([-\s\.])?[0-9]{1,12}){1,2}$/ );

		}, $.validator.messages.phone
	);
});