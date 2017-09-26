/**
 * @author Steven A. Zahm
 */
;jQuery(document).ready( function($) {

	var $document = $( document );

	var CN_Form = {

		init: function() {

			var selected = $('input[name=entry_type]');
			var type;

			if ( selected.length == 1 ) {

				type = selected.val();

			} else if ( selected.length > 1) {

				type = $('input[name=entry_type]:checked').val();
			}

			// Show custom fields based on current selected entry type.
			this.show( type );

			// Convert the org field to a select if the entry type is an individual.
			if ( 'individual' == type ) {

				this.convertOrgFieldToSelect();

			} else {

				this.convertOrgFieldToInput()
			}

			// // Add new rule for jQuery Validate for the phone number field.
			// /** @link http://stackoverflow.com/a/15482666/5351316 */
			// $.validator.addMethod(
			// 	'phoneFormat',
			// 	function( value, element ) {
			// 		return this.optional( element ) || /^\(\d{3}\) \d{3}-\d{4}$/.test( value );
			// 	},
			// 	'Please enter a valid phone number.'
			// );
			//
			// // Format and validate entered phone numbers.
			// $( '#phone-numbers' ).on( 'keyup paste', 'input[name^="phone"]', function() {
			//
			// 	/** @link http://stackoverflow.com/a/37066380/5351316 */
			// 	// $( this ).val( $( this ).val().replace( /^(\d{3})(\d{3})(\d+)$/, '($1) $2-$3' ) );
			//
			// 	/** @link http://stackoverflow.com/a/28588038/5351316 */
			// 	// Remove invalid chars from the input
			// 	var input = this.value.replace( /[^0-9\(\)\s\-]/g, "" );
			// 	var inputlen = input.length;
			//
			// 	// Get just the numbers in the input
			// 	var numbers = this.value.replace( /\D/g, '' );
			// 	var numberslen = numbers.length;
			//
			// 	// Value to store the masked input
			// 	var newval = "";
			//
			// 	// Loop through the existing numbers and apply the mask
			// 	for ( var i = 0; i < numberslen; i++ ) {
			// 		if ( i == 0 ) newval = "(" + numbers[ i ];
			// 		else if ( i == 3 ) newval += ") " + numbers[ i ];
			// 		else if ( i == 6 ) newval += "-" + numbers[ i ];
			// 		else newval += numbers[ i ];
			// 	}
			//
			// 	// Re-add the non-digit characters to the end of the input that the user entered and that match the mask.
			// 	if ( inputlen >= 1 && numberslen == 0 && input[ 0 ] == "(" ) newval = "(";
			// 	else if ( inputlen >= 6 && numberslen == 3 && input[ 4 ] == ")" && input[ 5 ] == " " ) newval += ") ";
			// 	else if ( inputlen >= 5 && numberslen == 3 && input[ 4 ] == ")" ) newval += ")";
			// 	else if ( inputlen >= 6 && numberslen == 3 && input[ 5 ] == " " ) newval += " ";
			// 	else if ( inputlen >= 10 && numberslen == 6 && input[ 9 ] == "-" ) newval += "-";
			//
			// 	$( this ).val( newval.substring( 0, 14 ) );
			//
			// 	$( this ).rules( 'add', {
			// 		required:    true,
			// 		phoneFormat: true
			// 	} )
			// });
			//
			// // Add the placeholder attribute to phone text input field.
			// $document.on( 'entry-field-type-added', function( event, type ) {
			//
			// 	if ( 'phone' == type ) $( 'input[name^="' + type + '"]' ).attr( 'placeholder', '(###) ###-####' );
			// } );
			//
			// Display the custom fields based on selected entry type.
			$document.on( 'entry-type-selected', function( event, type ) {
				CN_Form.show( type );
			} );
		},

		show: function( type ) {

			// var website = $( '#custom_website-hide' );
			// var volume  = $( '#custom_volume-hide' );

			switch ( type ) {

				case 'individual':

					// if ( website.is(':checked') ) {
					//
					// 	website.trigger( 'click' );
					// }
					//
					// if ( volume.is(':checked') ) {
					//
					// 	volume.trigger( 'click' );
					// }

					CN_Form.convertOrgFieldToSelect();

					break;

				case 'organization':

					// if ( ! website.is(':checked') ) {
					//
					// 	website.trigger( 'click' );
					// }
					//
					// if ( ! volume.is(':checked') ) {
					//
					// 	volume.trigger( 'click' );
					// }

					CN_Form.convertOrgFieldToInput();

					break;
			}
		},

		convertOrgFieldToSelect: function() {

			// console.log('Convert to select.');

			var input = $( 'input#cn-organization' );

			if ( input.length ) {

				var value = input.val();

				var options = '<option value=""></option>';

				var names = [];

				$.each( cnEntryOrganizationOptions.options, function( key, name ) {

					names.push( { 'key': key, 'value': name } );
				} );

				names.sort( function( a, b ) {

					var aName = a.value.toLowerCase();
					var bName = b.value.toLowerCase();
					return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
				} );

				// console.log(names);

				$.each( names, function( key, name ) {

					options = options + '<option value="' + name.value +'">' + name.value + '</option>'
				} );

				input.replaceWith(
					'<select id="cn-organization" name="organization" style="width: 100%;" data-placeholder="' + cnEntryOrganizationOptions.string.select_default + '">' +
					options +
					'</select>' );

				// console.log( value );
				$('select#cn-organization').val( value ).chosen();

				// Set style of label.
				// $('label[for=cn-organization]').css({
				// 	fontWeight: 'bold',
				// 	marginRight: '10px'
				// })
			}
		},

		convertOrgFieldToInput: function() {

			// console.log('Convert to input.');

			var select = $('select#cn-organization');

			if ( select.length ) {

				var value  = select.val();

				select.chosen('destroy')
					.replaceWith('<input type="text" id="cn-organization" name="organization" value="">');

				$( 'input#cn-organization' ).val( value );

				// Set style of label.
				$('label[for=cn-organization]').css({
					fontWeight: 'normal',
					marginRight: '0'
				})
			}

		}
	};

	CN_Form.init();
});
