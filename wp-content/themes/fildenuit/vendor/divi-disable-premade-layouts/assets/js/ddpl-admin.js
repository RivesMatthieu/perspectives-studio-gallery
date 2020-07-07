(function($,w){$(function(){

	/**********************************************************************************
		Divi - Disable Premade Layouts - Settings Panel Options
	**********************************************************************************/

	class ddplSettings
	{

		setProperties()
		{

			this.trigger = $( 'a.et-pb-layout-buttons-load' );

			this.triggerVB = 'button[data-tip="Load From Library"], .et-fb-page-creation-card-clone_existing_page';

		} // end setProperties

	    constructor()
	    {

	        this.setProperties();

	        this.bindEvents();

		} // end constructor

	    bindEvents( self = this )
	    {

	        $( '#normal-sortables' ).on( 'mousedown', this.trigger, () =>
	        {

		      	let checkForPanel = setInterval( () =>
				{

					let loader = $( '#et_pb_loading_animation' ),
						iframe = $( 'div.et_pb_modal_settings' ).find( 'iframe' );

					if ( iframe.length > 0 && 'none' == loader.css( 'display' ) )
					{

						clearInterval( checkForPanel );

						setTimeout( () =>
						{

							if ( $( 'body' ).hasClass( 'no-et-layouts' ) )
								$( '[data-open_tab="et-pb-existing_pages-tab"]' ).children( 'a' ).trigger( 'click' );

							else
								$( '[data-open_tab="et-pb-saved-modules-tab"]' ).children( 'a' ).trigger( 'click' );

							iframe.addClass( 'show' );

						}, 100 );
					} // end if
				}, 10 ); // end setInterval
	        });

	        $( 'body' ).on( 'mousedown', this.triggerVB, ( e ) =>
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
