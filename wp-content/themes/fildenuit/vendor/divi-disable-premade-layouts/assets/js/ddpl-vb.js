(function($,w){$(function(){

	/**********************************************************************************
		Divi - Disable Premade Layouts - Settings Panel Options
	**********************************************************************************/

	class ddplSettings
	{

		setProperties()
		{

			this.triggerVB = 'button[data-tip="Load From Library"], .et-fb-page-creation-card-clone_existing_page';

		} // end setProperties

	    constructor()
	    {

	        this.setProperties();

	        this.bindEvents();

		} // end constructor

	    bindEvents( self = this )
	    {

	        $( '#et-fb-app' ).on( 'mousedown', this.triggerVB, ( e ) =>
	        {

		      	let checkForPanel = setInterval( () =>
				{

					let panel  = $( '#et-fb-settings-column' ),
						loader = panel.find( 'div.et-fb-preloader' ),
						iframe = panel.find( 'iframe' );

					if ( iframe.length > 0 && ! loader.hasClass( 'et-fb-preloader__loading' ) )
					{

						clearInterval( checkForPanel );

						setTimeout( () =>
						{

							if ( $( 'body' ).hasClass( 'no-et-layouts' ) )
								$( 'a.existing_pages' )[0].click();

							else
								$( 'a.modules_library' )[0].click();

							iframe.addClass( 'show' );

						}, 100 );
					} // end if
				}, 10 ); // end setInterval
	        });
	    } // end bindEvents
	} // end class ddplSettings

	new ddplSettings;

});}(jQuery,window));
