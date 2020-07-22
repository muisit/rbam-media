jQuery(document).ready(function($) {
    var last;
    var cache;
    var separator = window.tagsSuggestL10n.tagDelimiter || ',';
    var tempID = 0;

    function split( val ) {
		return val.split( new RegExp( separator + '\\s*' ) );
	}

	function getLast( term ) {
		return split( term ).pop();
    }
	var $element = $( '#new-security-rbammedia_roles' );
	window.tagBox.init();
	$('#new-security-rbammedia_roles').wpTagsSuggest({
		taxonomy: "rbammedia",
		source: function( request, response ) {
			var term;

            if ( last === request.term ) {
				response( cache );
				return;
			}

			term = getLast( request.term );

            $.get( window.ajaxurl, {
				action: 'rbammedia',
				q: term
			} ).always( function() {
				$element.removeClass( 'ui-autocomplete-loading' ); // UI fails to remove this sometimes?
			} ).done( function( data ) {
				var tagName;
				var tags = [];

				if ( data ) {
					data = data.split( '\n' );

					for ( tagName in data ) {
						var id = ++tempID;

						tags.push({
							id: id,
							name: data[tagName]
						});
					}

					cache = tags;
					response( tags );
				} else {
					response( tags );
				}
			} );

			last = request.term;
		},        
        select: function( event, ui ) {
            var tags = split( $element.val() );
            // Remove the last user input.
            tags.pop();
            // Append the new tag and an empty element to get one more separator at the end.
            tags.push( ui.item.name, '' );

            $element.val( tags.join( separator + ' ' ) );

            if ( $.ui.keyCode.TAB === event.keyCode ) {
                // Audible confirmation message when a tag has been selected.
                window.wp.a11y.speak( window.tagsSuggestL10n.termSelected, 'assertive' );
                event.preventDefault();
            } 
            else {
                // If we're in the edit post Tags meta box, add the tag.
                if ( window.tagBox ) {
                    window.tagBox.userAction = 'add';
                    window.tagBox.flushTags( $( this ).closest( '.tagsdiv' ) );
                }

                // Do not close Quick Edit / Bulk Edit.
                event.preventDefault();
                event.stopPropagation();
            }

            return false;
        },
    });
});
