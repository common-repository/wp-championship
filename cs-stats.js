/**
 * Javascript functions for ajax effect in cs_stats
 *
 * @package wp-championship
 */

/* cs_stats1 */
/*
  javascript for ajax like request to update the stats
  and corresponding data on the fly
*/

/* get the data for the new location */
function wpc_stats1_update() {

	var newday    = document.getElementById( "wpc_stats1_selector" ).value;
	var tippgroup = document.getElementById( "wpc_stats1_tippgroup" ).value;
	var nonce     = document.getElementById( "wpc_nonce_stats" ).value;

	jQuery.post(
		wpcobj.wpc_ajaxurl + "?action=update_stats1",
		{nonce:nonce, newday: newday, tippgroup: tippgroup, header: "0", selector: "1", statsnum: "1"},
		function(data){jQuery( "div#wpc-stats1-res" ).html( data );
		}
	);

}

/* cs_stats4 */
/* get the data for the new location */
function wpc_stats4_update() {

	var username = document.getElementById( "wpc_stats4_selector" ).value;
	var match    = document.getElementById( "wpc_stats4_selector2" ).value;
	var nonce    = document.getElementById( "wpc_nonce_stats" ).value;

	jQuery.post(
		wpcobj.wpc_ajaxurl + "?action=update_stats4",
		{ nonce: nonce, username: username, match: match, header: "0" , selector: "1", statsnum: "4" },
		function(data){
			jQuery( "div#wpc-stats4-res" ).html( data );
		}
	);
}


/* cs_stats5 */
/* get the data for the new location */
function wpc_stats5_update() {

	var newday    = document.getElementById( "wpc_stats5_selector" ).value;
	var tippgroup = document.getElementById( "wpc_stats5_tippgroup" ).value;
	var nonce     = document.getElementById( "wpc_nonce_stats" ).value;

	jQuery.post(
		wpcobj.wpc_ajaxurl + "?action=update_stats5",
		{ nonce:nonce, newday5: newday, tippgroup: tippgroup, header: "0", selector: "1", statsnum: "5" },
		function(data){
			jQuery( "div#wpc-stats5-res" ).html( data );
		}
	);
}

function wpc_stats6_update() {

	var team      = document.getElementById( "wpc_stats6_selector" ).value;
	var tippgroup = document.getElementById( "wpc_stats6_tippgroup" ).value;
	var nonce     = document.getElementById( "wpc_nonce_stats" ).value;

	jQuery.post(
		wpcobj.wpc_ajaxurl + "?action=update_stats6",
		{ nonce:nonce, team: team, tippgroup: tippgroup, header: "0" , selector: "1", statsnum: "6" },
		function(data){
			jQuery( "div#wpc-stats6-res" ).html( data );
		}
	);
}

function wpc_stats7_update() {

	var newday    = document.getElementById( "wpc_stats7_selector" ).value;
	var tippgroup = document.getElementById( "wpc_stats7_tippgroup" ).value;
	var nonce     = document.getElementById( "wpc_nonce_stats" ).value;

	jQuery.post(
		wpcobj.wpc_ajaxurl + "?action=update_stats7",
		{ nonce:nonce, newday: newday, tippgroup: tippgroup, header: "0" , selector: "1", statsnum: "7" },
		function(data){
			jQuery( "div#wpc-stats7-res" ).html( data );
		}
	);
}

/* to work around IE problems */

/* javascript to rebuild the onLoad event for triggering
   the first wpc_update call */

// create onDomReady Event.
window.onDomReady = initReady;

// Initialize event depending on browser.
function initReady(fn)
{
	// W3C-compliant browser.
	if (document.addEventListener) {
		document.addEventListener( "DOMContentLoaded", fn, false );
	} else { // IE.
		document.onreadystatechange = function(){readyState( fn );};
	}
}

// IE execute function.
function readyState(func)
{
	// DOM is ready.
	if (document.readyState == "interactive" || document.readyState == "complete") {
		func();
	}
}
