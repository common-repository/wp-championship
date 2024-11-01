/**
 * Javascript for field validation and input support
 *
 * @package wp-championship
 */

/**
 * Take first 5 characters from fullname as shortname
 */
function calc_shortname() {
	var name      = document.getElementById( "team_name" ).value;
	var shortname = document.getElementById( "team_shortname" ).value;

	if (shortname == "") {
		document.getElementById( "team_shortname" ).value = name.substring( 0,5 );
	}
}

/*
  javascript function for ex/import dialog
*/
function submit_this(mode){

	if (mode == "export") {
		var exmode           = document.getElementById( "exmode" ).value;
		var dlmode           = document.getElementById( "dlmode" ).checked;
		var fnmode           = document.getElementById( "fnmode" ).checked;
		var wpc_nonce_export = document.getElementById( "wpc_nonce_export" ).value;

		if (dlmode == true) {
			var murl          = ajaxurl + "?action=wpc_export&dlmode=" + dlmode + "&exmode=" + exmode + "&fnmode=" + fnmode + "&wpc_nonce_export=" + wpc_nonce_export;
			document.location = murl;
		} else {
			jQuery.post( ajaxurl + "?action=wpc_export", {dlmode: dlmode, exmode: exmode,fnmode: fnmode, wpc_nonce_export: wpc_nonce_export}, function(data){jQuery( "#message" ).html( data );} );
		}
	}

	if (mode == "import") {
		var immode           = document.getElementById( "immode" ).value;
		var csvfile          = document.getElementById( "csvfile" ).value;
		var csvdelall        = document.getElementById( "csvdelall" ).checked;
		var fnmode           = document.getElementById( "fnmode" ).checked;
		var wpc_nonce_import = document.getElementById( "wpc_nonce_import" ).value;
		jQuery.post( ajaxurl + "?action=wpc_import", {csvfile: csvfile, csv_delall: csvdelall, immode: immode, fnmode: fnmode, wpc_nonce_import: wpc_nonce_import}, function(data){jQuery( "#message" ).html( data );} );
	}

	if (mode == "openligadbimport") {
		var league               = document.getElementById( "ol_league" ).value;
		var csvdelall            = document.getElementById( "csvdelall" ).checked;
		var wpc_nonce_oldbimport = document.getElementById( "wpc_nonce_oldbimport" ).value;
		jQuery.post( ajaxurl + "?action=wpc_openligadbimport", {league: league, csv_delall: csvdelall, wpc_nonce_oldbimport: wpc_nonce_oldbimport}, function(data){jQuery( "#message" ).html( data );} );
	}

	return false;
}

function ol_update_league() {
	// get objects and selected values.
	var e      = document.getElementById( "ol_season" );
	var season = e.options[e.selectedIndex].value;
	var f      = document.getElementById( "ol_league" );
	var league = f.options[f.selectedIndex].value;
	var nonce  = document.getElementById( "wpc_nonce_oldbimport" ).value;

	// reset depending select fields.
	if (season == -1) {
		f.value = -1;
		return false;
	} else {
		jQuery.post(
			ajaxurl + "?action=wpc_openligadb_getleagues",
			{season: season, nonce: nonce},
			function(data){
				// clear all entries.
				var flen = f.options.length;
				for (i = flen - 1; i > 0; i--) {
					f.options[i] = null; }
				// populate it again.
				var listitems = '';
				var leagues   = JSON.parse( data );
				if (leagues != null ) {
					for (var i = leagues.length - 1; i >= 0;i--) {
						var opt       = document.createElement( "option" );
						opt.value     = leagues[i].leagueShortcut + ";" + leagues[i].leagueSaison;
						opt.innerHTML = leagues[i].leagueName
						// then append it to the select element.
						f.appendChild( opt );
					}
				}
			}
		);
	}
}
