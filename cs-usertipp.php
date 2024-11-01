<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2006-2024  Hans Matzen  (email : webmaster at tuxlog.de)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package wp-championship
 */

// prüfen, ob wir direkt aufgerufen werden.
if ( ! defined( 'WPINC' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Funktion zum holen einer url (wird verwendet um die lokale zeitzone des users zu ermitteln).
 *
 * @param string $fn URL die geholt wird.
 */
function file_get_contents_utf8( $fn ) {
	$content = '';
	if ( ini_get( 'allow_url_fopen' ) && function_exists( 'mb_convert_encoding' ) ) {
		$content = file_get_contents( $fn );
		return mb_convert_encoding(
			$content,
			'UTF-8',
			mb_detect_encoding( $content, 'UTF-8, ISO-8859-1', true )
		);
	} else {
		return $content;
	}
}

if ( ! function_exists( 'show_usertippform' ) ) {
	/**
	 * Funktion zur Verwaltung des kompletten Tippformulars inkl. admin funktionen.
	 */
	function show_usertippform() {
		include 'globals.php';
		global $wpdb, $wpcs_demo;

		// initialisiere ausgabe variable.
		$out = '';

		// lese anwenderdaten ein.
		$userdata = wp_get_current_user();
		// merke die userid.
		$uid = $userdata->ID;

		// hovertable count init.
		$hovertable_count = 0;

		// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
		if ( ( ! is_user_logged_in() && 0 == $wpcs_demo ) || ! cs_is_tippspiel_user( $uid ) ) {
			$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
			$out .= __( 'To attend the guessing game, you need an account at this website.', 'wp-championship' ) . '<br />';
			return $out;
		}

		// javascript für floating link ausgeben.
		$cs_floating_link = get_option( 'cs_floating_link' );
		if ( $cs_floating_link > 0 && ! is_admin() ) {
			$out .= cs_get_float_js();
		}

		// set userid from wpcs demo user.
		if ( $wpcs_demo > 0 ) {
			$uid = $wpcs_demo;
		}

		// lese torsummen schalter.
		$cs_goalsum      = get_option( 'cs_goalsum' );
		$cs_goalsum_auto = get_option( 'cs_goalsum_auto' );

		// pruefe ob jemand vertreten werden soll und darf.
		$cs_stellv_schalter = get_option( 'cs_stellv_schalter' );

		if ( isset( $_GET['cs_stellv'] ) && intval( $_GET['cs_stellv'] ) > 0 && ! $cs_stellv_schalter ) {
			// @codingStandardsIgnoreStart
			$r2  = $wpdb->get_row(
				$wpdb->prepare(
					"select ID, stellvertreter, case when display_name != '' then display_name when display_name is null 
					then user_login else user_login end as vdisplay_name 
					from %i inner join %i on ID=userid 
					where userid=%d;",
					$cs_users,
					$wp_users,
					intval( $_GET['cs_stellv'] )
				)
			);
			// @codingStandardsIgnoreEnd

			if ( $r2->stellvertreter == $uid ) {
				$out .= '<b>' . __( 'You are a proxy for ', 'wp-championship' ) . $r2->vdisplay_name . '.</b><br />';
				$out .= '<b>' . __( 'To update your own tips again, please reload this page.', 'wp-championship' ) . '</b><br />';
				// user switchen.
				$uid = $r2->ID;
			}
		}

		// userdaten lesen.
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d', $cs_users, $uid ) );

		// lesen fuer welcher anderen user der user als vertreter eingetragen ist
		// aber nur wenn nicht bereits eine stellvertreter regelung genutzt wird.
		if ( $uid == $userdata->ID ) {
			// @codingStandardsIgnoreStart
			$r1   = $wpdb->get_results(
				$wpdb->prepare(
					"select ID, case when display_name != '' then display_name when display_name is null 
					then user_login else user_login end as vdisplay_name 
					from  %i inner join %i on ID=userid 
					where stellvertreter=%d",
					$cs_users,
					$wp_users,
					$uid
				)
			);
			// @codingStandardsIgnoreEnd
		}

		// admin flag setzen.
		$is_admin = false;
		if ( 1 == $r0[0]->admin ) {
			$is_admin = true;
		}

		// ermittle aktuelle uhrzeit.
		$currtime = current_time( 'Y-m-d H:i:s' );

		// begruessung ausgeben.
		$out .= __( 'Welcome ', 'wp-championship' ) . ( $uid == $userdata->ID ? $userdata->display_name : $r2->vdisplay_name ) . ',<br />';
		$out .= __( 'this page shows you your tip overview, you can place new tips or update your tips until the match starts and change your personal settings.', 'wp-championship' ) . '<br /><hr />';

		// um die vertreterregelung in anspruch zu nehmen, links ausgeben
		// aber nur wenn nicht bereits eine vertreter regelung aktiv ist.
		if ( $uid == $userdata->ID && ! $cs_stellv_schalter ) {
			$out .= '<p>' . __( 'You are a proxy for:', 'wp-championship' ) . ' ';
			foreach ( $r1 as $res ) {
				$plink      = get_page_link();
				$pdelimiter = ( strpos( $plink, '/?' ) > 0 ? '' : '?' );
				$out       .= "<a href='" . $plink . $pdelimiter . '&amp;cs_stellv=' . $res->ID . "'>" . $res->vdisplay_name . '</a>&nbsp;';
			}
			$out .= '</p>';
		}

		// in demo mode skip updates.
		if ( ! $wpcs_demo > 0 ) {

			$errlist = array();

			/*
			  Speichern der aenderungen und pruefen der feldinhalte
			  ------------------------------------------------------
			  the next lines work around a strange behaviour when using more than one submit button with same name
			*/

			if ( isset( $_POST['wpcsupdate'] ) && __( 'Save changes', 'wp-championship' ) == $_POST['wpcsupdate'] ) {

				// check nonce.
				if ( ! isset( $_POST['wpc_nonce_usertipp'] ) ) {
					die( "<br><br>Looks like you didn't send any credentials. Please reload the page." );
				}
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_usertipp'] ) ), 'wpc_nonce_usertipp' ) ) {
					die( "<br><br>Looks like you didn't send any credentials. Please reload the page." );
				}

				// $_POST Variable in array $ppost übernehmen und validieren.
				$ppost = array();
				foreach ( $_POST as $k => $v ) {
					$ppost[ $k ] = sanitize_text_field( wp_unslash( $v ) );
				}

				// wurde als stellvertreter gespeichert?
				if ( isset( $ppost['cs_stellv'] ) && ! $cs_stellv_schalter ) {
					$realuser = $uid;
					$uid      = intval( $ppost['cs_stellv'] );
				}

				// optionen speichern.
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$r1 = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where userid=%d;', $cs_users, $uid ) );

				// datenfelder auf gueltigkeit pruefen.
				if ( -1 == $ppost['stellvertreter'] || '-' == $ppost['stellvertreter'] ) {
					$ppost['stellvertreter'] = 0;
				}
				if ( ! isset( $ppost['mailservice'] ) || '' == $ppost['mailservice'] ) {
					$ppost['mailservice'] = 0;
				}
				if ( ! isset( $ppost['mailreceipt'] ) || '' == $ppost['mailreceipt'] ) {
					$ppost['mailreceipt'] = 0;
				}
				if ( '' == $ppost['champion'] ) {
					$ppost['champion'] = -1;
				}
				if ( ! isset( $ppost['mailformat'] ) || '' == $ppost['mailformat'] ) {
					$ppost['mailformat'] = 0;
				}
				if ( ! isset( $ppost['hidefinmatch'] ) || '' == $ppost['hidefinmatch'] ) {
					$ppost['hidefinmatch'] = 0;
				}
				if ( ! isset( $ppost['cs_jokerlist'] ) || '' == $ppost['cs_jokerlist'] ) {
					$ppost['cs_jokerlist'] = '';
				}

				// user einstellungen speichern.
				if ( $r1->anz > 0 ) {
					// @codingStandardsIgnoreStart
					$r3 = $wpdb->query(
						$wpdb->prepare(
							'update %i set mailservice= %d , stellvertreter=%d , mailreceipt=%d, mailformat=%d, hidefinmatch=%d where userid=%d;',
							$cs_users,
							$ppost['mailservice'],
							$ppost['stellvertreter'],
							$ppost['mailreceipt'],
							$ppost['mailformat'],
							$ppost['hidefinmatch'],
							$uid
						)
					);
					// @codingStandardsIgnoreEnd
				} else {
					// @codingStandardsIgnoreStart
					$r3 = $wpdb->query(
						$wpdb->prepare(
							"insert into %i values (%d,0,%d,%d,%d, 0,'0000-00-00 00:00:00',-1,'', %d, %d);",
							$cs_users,
							$uid,
							$ppost['mailservice'],
							$ppost['stellvertreter'],
							$ppost['mailreceipt'],
							$ppost['mailformat'],
							$ppost['hidefinmatch']
						)
					);
					// @codingStandardsIgnoreEnd
				}

				// championtipp und jokerliste speichern und auf zulaessigkeit pruefen.
				$blog_now = current_time( 'mysql', 0 );

				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$mr = $wpdb->get_row( $wpdb->prepare( 'select min(matchtime) as mintime from %i', $cs_match ) );

				if ( $blog_now <= $mr->mintime ) {
					// @codingStandardsIgnoreStart
					$r2   = $wpdb->query(
						$wpdb->prepare(
							'update %i set champion=%d ,championtime=%s where userid=%d;',
							$cs_users,
							intval( $ppost['champion'] ),
							$currtime,
							$uid
						)
					);
					// @codingStandardsIgnoreEnd
				} elseif ( intval( $ppost['champion'] ) != $r0[0]->champion ) {
					$out .= __( 'The champion tip can not be changed anymore.', 'wp-championship' ) . "<br />\n";
				}

				if ( $blog_now <= $mr->mintime ) {
					// nur die maximal erlaubte jokermenge speichern.
					$jil = array_slice( explode( ',', esc_attr( $ppost['cs_jokerlist'] ) ), 0, get_option( 'cs_joker_player' ) );
					// string für update zusammensetzen.
					$jml = '';
					foreach ( $jil as $jm ) {
						if ( $jm > 0 ) {
							$jml .= $jm . ',';
						}
					}
					// letztes Komma abschneiden.
					$jml = substr( $jml, 0, -1 );
					// The placeholder ignores can be removed when %i is supported by WPCS.
					// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
					$r21 = $wpdb->query( $wpdb->prepare( 'update %i set jokerlist=%s where userid=%d;', $cs_users, $jml, $uid ) );
				} elseif ( esc_attr( $ppost['cs_jokerlist'] ) != $r0[0]->jokerlist ) {
					$out .= __( 'The jokers can not be changed anymore.', 'wp-championship' ) . "<br />\n";
				}

				// Tippgruppe speichern und auf zulaessigkeit pruefen.
				if ( get_option( 'cs_use_tippgroup' ) ) {
					if ( $blog_now <= $mr->mintime ) {
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						$r2 = $wpdb->query( $wpdb->prepare( 'update %i set tippgroup=%d where userid=%d;', $cs_users, intval( $ppost['tippgroup'] ), $uid ) );
					} elseif ( intval( $ppost['tippgroup'] ) != $r0[0]->tippgroup ) {
						$out .= __( 'The tip group can not be changed anymore.', 'wp-championship' ) . "<br />\n";
					}
				}

				// userdaten erneut lesen.
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d;', $cs_users, $uid ) );

				$errflag = 0;
				$errlist = array(); // enthält die ids der input felder, die fehlerhaft sind.
				//
				// tipps plausibiliseren.
				//
				foreach ( $ppost as $key => $value ) {
					$mkey = substr( $key, 0, 4 );
					if ( 'gt1_' == $mkey || 'gt2_' == $mkey || 'gt3_' == $mkey ) {
						$mid = substr( $key, 4 );

						// es sind nur zahlen zugelassen, rest herausfiltern
						// ebenso sind werte kleiner als 0 nicht zugelassen.
						if ( preg_replace( '/[^0-9]/i', '', $value ) != $value || (int) $value < 0 ) {
							  $out .= __( 'Invalid tip or value:', 'wp-championship' ) . " $value<br />\n";
							  ++$errflag;
							  $errlist[ $key ] = $key;
						}

						// leere felder auf -1 setzen.
						if ( trim( $ppost[ $key ] ) == '' ) {
								$ppost[ $key ] = -1;
						}

						// pruefe ob das spiel schon begonnen hat.
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						$r1 = $wpdb->get_results( $wpdb->prepare( 'select matchtime from %i where mid=%d', $cs_match, $mid ) );
						if ( $blog_now > $r1[0]->matchtime ) {
							// translators: %d steht für die Match-Id.
							$out          .= sprintf( __( 'Match No. %d has alread stareted.', 'wp-championship' ), $mid ) . '<br />' . __( 'The tip can not be changed anymore.', 'wp-championship' ) . "<br />\n";
							$ppost[ $key ] = -1;
							++$errflag;
						}

						// pruefe ob torsummen tipp erlaubt und im range ist.
						if ( $cs_goalsum > -1 && 0 == $cs_goalsum_auto && 'gt3_' == $mkey && -1 != $ppost[ 'gt2_' . $mid ] && -1 != $ppost[ 'gt1_' . $mid ] ) {
							if ( intval( $ppost[ $key ] ) < $cs_goalsum && intval( $ppost[ $key ] ) >= 0 ) {
								$out .= __( 'Sum of goals must be greater than Threshold', 'wp-championship' ) . '(' . $cs_goalsum . ").<br />\n";
								++$errflag;
								$errlist[ $key ] = $key;
								$ppost[ $key ]   = -1;
							}
						}
					}
				}

				// pruefe ob tipp vollständig (beide werte gefüllt?).
				foreach ( $ppost as $key => $value ) {
					$mkey = substr( $key, 0, 4 );
					if ( 'gt1_' == $mkey ) {
						$mid = substr( $key, 4 );
						if ( ! ( ( -1 == $ppost[ $key ] && -1 == $ppost[ 'gt2_' . $mid ] ) || ( $ppost[ $key ] >= 0 && $ppost[ 'gt2_' . $mid ] >= 0 ) ) ) {
							  $out .= __( 'the tip is incomplete or there is an invalid entry.', 'wp-championship' ) . "<br />\n";
							  ++$errflag;
							  $errlist[ $key ] = $key;
						}
					}
				}

				// wenn alles in ordnung ist $errflag == 0, dann speichere den tipp.
				if ( 0 == $errflag ) {
					// tipp speichern.
					$have_tipps = array();
					foreach ( $ppost as $key => $value ) {
						if ( substr( $key, 0, 4 ) == 'gt1_' || substr( $key, 0, 4 ) == 'gt2_' || substr( $key, 0, 4 ) == 'gt3_' ) {
							// speichere tipp fuer spiel mid.
							$mid = substr( $key, 4 );

							// pruefe ob satz bereits vorhanden.
							// The placeholder ignores can be removed when %i is supported by WPCS.
							// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
							$r1 = $wpdb->get_row( $wpdb->prepare( 'select * from %i where userid=%d and mid=%d;', $cs_tipp, $uid, $mid ) );

							if ( ! isset( $ppost[ 'gt3_' . $mid ] ) ) {
								$ppost[ 'gt3_' . $mid ] = -1;
							}

							if ( $r1 ) {
								if ( $r1->result1 != (int) $ppost[ 'gt1_' . $mid ] ||
								$r1->result2 != (int) $ppost[ 'gt2_' . $mid ] ||
								( isset( $ppost[ 'gt3_' . $mid ] ) && $r1->result3 != (int) $ppost[ 'gt3_' . $mid ] ) ) {
									// @codingStandardsIgnoreStart
									$r2   = $wpdb->query(
										$wpdb->prepare(
											'update  %i set result1=%d, result2=%d, result3=%d, tipptime=%s where userid=%d and mid=%d;',
											$cs_tipp,
											(int) $ppost[ 'gt1_' . $mid ],
											(int) $ppost[ 'gt2_' . $mid ],
											(int) $ppost[ 'gt3_' . $mid ],
											$currtime,
											$uid,
											$mid,
										)
									);
									// @codingStandardsIgnoreEnd
								}
							} else {
								// @codingStandardsIgnoreStart
								$r2   = $wpdb->query(
									$wpdb->prepare(
										'insert into %i values (%d, %d, %d, %d, %d, %s,-1);',
										$cs_tipp,
										$uid,
										$mid,
										(int) $ppost[ 'gt1_' . $mid ],
										(int) $ppost[ 'gt2_' . $mid ],
										(int) $ppost[ 'gt3_' . $mid ],
										$currtime
									)
								);
								// @codingStandardsIgnoreEnd
							}
							// tipp merken fuer tipp bestätigungsmail.
							$have_tipps[ $mid ] = (int) $ppost[ 'gt1_' . $mid ] . ':' . (int) $ppost[ 'gt2_' . $mid ];
						}
					}
					$out .= __( 'Tips successfully saved.', 'wp-championship' ) . '<br/>';
				}

				if ( $is_admin ) {
					$errflag      = 0;
					$have_results = 0;
					// eingegebene ergebnisse plausibiliseren.
					foreach ( $ppost as $key => $value ) {
						$mkey = substr( $key, 0, 4 );
						if ( 'rt1_' == $mkey || 'rt2_' == $mkey ) {
							  $mid = substr( $key, 4 );

							  // leere felder werden als - dargestellt.
							if ( '-' == $value ) {
								$ppost[ $key ] = '';
								$value         = '';
							}

							// es sind nur zahlen zugelassen, rest herausfiltern
							// ebenso sind werte kleiner als 0 nicht zugelassen.
							if ( preg_replace( '/[^0-9]/i', '', $value ) != $value || (int) $value < 0 ) {
								$out .= __( 'Invalid result, value:', 'wp-championship' ) . " $value<br />\n";
								++$errflag;
								$errlist[ $key ] = $key;
							}

							// leere felder auf -1 setzen = ergebnis loeschen.
							if ( '' == $ppost[ $key ] ) {
								$ppost[ $key ] = -1;
							}
						}
					}

					// pruefe ob ergebnisse vollständig (beide werte gefüllt?).
					foreach ( $ppost as $key => $value ) {
						$mkey = substr( $key, 0, 4 );
						if ( 'rt1_' == $mkey ) {
								$mid = substr( $key, 4 );
							if ( ! ( -1 == ( $ppost[ 'rt1_' . $mid ] && -1 == $ppost[ 'rt2_' . $mid ] ) ||
								( $ppost[ 'rt1_' . $mid ] >= 0 && $ppost[ 'rt2_' . $mid ] >= 0 ) ) ) {
								$out .= __( 'the result is incomplete or there is an invalid entry.', 'wp-championship' ) . "<br />\n";
								++$errflag;
								$errlist[ $key ] = $key;

							}
						}
					}

					// wenn alles in ordnung ist $errflag == 0, dann speichere die ergebnisse.
					if ( 0 == $errflag ) {
						// tipp speichern.
						foreach ( $ppost as $key => $value ) {
							if ( substr( $key, 0, 4 ) == 'rt1_' || substr( $key, 0, 4 ) == 'rt2_' ) {
								// speichere tipp fuer spiel mid.
								$mid = substr( $key, 4 );

								// gewinner ermitteln.
								if ( -1 == (int) $ppost[ 'rt1_' . $mid ] && -1 == (int) $ppost[ 'rt2_' . $mid ] ) {
									$winner = -1;
								} elseif ( (int) $ppost[ 'rt1_' . $mid ] > (int) $ppost[ 'rt2_' . $mid ] ) {
									$winner = 1;
								} elseif ( (int) $ppost[ 'rt2_' . $mid ] > (int) $ppost[ 'rt1_' . $mid ] ) {
									$winner = 2;
								} else {
									$winner = 0;
								}

								// @codingStandardsIgnoreStart
								$r4   = $wpdb->get_row(
									$wpdb->prepare(
										'select count(*) as anz from %i  
										where result1=%d and result2=%d and winner=%d and mid=%d;',
										$cs_match,
										(int) $ppost[ 'rt1_' . $mid ],
										(int) $ppost[ 'rt2_' . $mid ],
										$winner,
										$mid
									)
								);
								// @codingStandardsIgnoreEnd

								// wenn dieser satz noch nicht aktuell ist, dann speichern wir ihn.
								if ( 0 == $r4->anz ) {
									$have_results = 1;

									// @codingStandardsIgnoreStart
									$r3   = $wpdb->query(
										$wpdb->prepare(
											'update %i set result1=%d, result2=%d, winner=%d where mid=%d;',
											$cs_match,
											(int) $ppost[ 'rt1_' . $mid ],
											(int) $ppost[ 'rt2_' . $mid ],
											$winner,
											$mid
										)
									);
									// @codingStandardsIgnoreEnd
								}
							}
						}
						if ( $have_results ) {
							  $out .= __( 'Results successfully saved.', 'wp-championship' ) . '<br/>';
						}
					}

					// aktuelle mitspieler platzierung speichern.
					if ( get_option( 'cs_rank_trend' ) && $have_results ) {
						cs_store_current_ranking();
					}
					// punkt nach eingabe neu berechnen.
					cs_calc_points();
					// finalrunde eintraege aktualisieren.
					cs_update_finals();
				} // end is_admin.

				// mailservice für alle user durchführen
				// mailservice durchfuehren (verschickt mails an alle die sie haben wollten).
				if ( $have_results ) {
					cs_mailservice();
					if ( get_option( 'cs_use_tippgroup' ) ) {
						cs_mailservice_tippgroup();
					}
				}
				if ( isset( $have_tipps ) && 0 != $ppost['mailreceipt'] ) {
					cs_mailservice3( $uid, $have_tipps );
				}

				// wurde als stellvertreter gespeichert dann nach speichern
				// wieder umschalten auf realuser.
				if ( isset( $ppost['cs_stellv'] ) ) {
					$uid = $realuser;
				}
			}
		} // end of demo if.

		// Variable, die früher für die Ermittlung der geographischen Zeitdifferenze verwendet wurden und jetzt nur noch initialisiert werden.
		$timediff = 0;
		$geores   = '';

		//
		// ausgabe des floating links.
		//
		if ( $cs_floating_link && ! is_admin() ) {
			$out .= '<div id="WPCSfloatMenu"><ul class="menu1"><li><a href="#" onclick="window.scrollTo(0,0); return false;"> ' . __( 'Go Top', 'wp-championship' ) . ' </a></li></ul></div>';
		}

		/*
		 -------------------------------------------------------------------
		   ausgabe der optionen und der tipptabelle.
		   -------------------------------------------------------------------
		 */

		//
		// lese alternative bezeichnungen.
		//
		$cs_label_group    = get_option( 'cs_label_group' );
		$cs_col_group      = get_option( 'cs_col_group' );
		$cs_label_icon1    = get_option( 'cs_label_icon1' );
		$cs_col_icon1      = get_option( 'cs_col_icon1' );
		$cs_label_match    = get_option( 'cs_label_match' );
		$cs_col_match      = get_option( 'cs_col_match' );
		$cs_label_icon2    = get_option( 'cs_label_icon2' );
		$cs_col_icon2      = get_option( 'cs_col_icon2' );
		$cs_label_location = get_option( 'cs_label_location' );
		$cs_col_location   = get_option( 'cs_col_location' );
		$cs_label_time     = get_option( 'cs_label_time' );
		$cs_col_time       = get_option( 'cs_col_time' );
		$cs_label_tip      = get_option( 'cs_label_tip' );
		$cs_col_tip        = get_option( 'cs_col_tip' );
		$cs_label_points   = get_option( 'cs_label_points' );
		$cs_col_points     = get_option( 'cs_col_points' );
		$cs_label_place    = get_option( 'cs_label_place' );
		$cs_col_place      = get_option( 'cs_col_place' );
		$cs_label_player   = get_option( 'cs_label_player' );
		$cs_col_player     = get_option( 'cs_col_player' );
		$cs_label_upoints  = get_option( 'cs_label_upoints' );
		$cs_col_upoints    = get_option( 'cs_col_upoints' );
		$cs_label_trend    = get_option( 'cs_label_trend' );
		$cs_col_trend      = get_option( 'cs_col_trend' );
		$cs_label_steam    = get_option( 'cs_label_steam' );
		$cs_col_steam      = get_option( 'cs_col_steam' );
		$cs_label_smatch   = get_option( 'cs_label_smatch' );
		$cs_col_smatch     = get_option( 'cs_col_smatch' );
		$cs_label_swin     = get_option( 'cs_label_swin' );
		$cs_col_swin       = get_option( 'cs_col_swin' );
		$cs_label_stie     = get_option( 'cs_label_stie' );
		$cs_col_stie       = get_option( 'cs_col_stie' );
		$cs_label_sloose   = get_option( 'cs_label_sloose' );
		$cs_col_sloose     = get_option( 'cs_col_sloose' );
		$cs_label_sgoal    = get_option( 'cs_label_sgoal' );
		$cs_col_sgoal      = get_option( 'cs_col_sgoal' );
		$cs_label_spoint   = get_option( 'cs_label_spoint' );
		$cs_col_spoint     = get_option( 'cs_col_spoint' );
		$cs_tipp_sort      = get_option( 'cs_tipp_sort' );
		$cs_col_matchid    = get_option( 'cs_col_matchid' );
		$cs_label_matchid  = get_option( 'cs_label_matchid' );

		if ( '' == $cs_label_group ) {
			$cs_label_group = __( 'Group', 'wp-championship' );
		}
		if ( '' == $cs_label_icon1 ) {
			$cs_label_icon1 = '&nbsp;';
		}
		if ( '' == $cs_label_match ) {
			$cs_label_match = __( 'match', 'wp-championship' );
		}
		if ( '' == $cs_label_icon2 ) {
			$cs_label_icon2 = '&nbsp;';
		}
		if ( '' == $cs_label_location ) {
			$cs_label_location = __( 'Location', 'wp-championship' );
		}
		if ( '' == $cs_label_time ) {
			$cs_label_time = __( 'Date<br />Time', 'wp-championship' );
		}
		if ( '' == $cs_label_tip ) {
			$cs_label_tip = __( 'Tip<br />Result', 'wp-championship' );
		}
		if ( '' == $cs_label_points ) {
			$cs_label_points = __( 'Points', 'wp-championship' );
		}
		if ( '' == $cs_label_matchid ) {
			$cs_label_matchid = __( 'Match-Id', 'wp-championship' );
		}

		// teamliste fuer select aufbauen.
		$team1_select_html = '';
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results1 = $wpdb->get_results( $wpdb->prepare( 'select tid,name from %i where name not like %s order by name;', $cs_team, '#%' ) );

		$team1_select_html .= "<option value='-1'>-</option>";
		foreach ( $results1 as $res ) {
			$team1_select_html .= "<option value='" . $res->tid . "' ";
			if ( $res->tid == $r0[0]->champion ) {
				$team1_select_html .= "selected='selected'";
				$champion_team      = $res->name;
			}
			$team1_select_html .= '>' . $res->name . "</option>\n";
		}

		// userliste fuer select aufbauen.
		$user1_select_html = '';

		// @codingStandardsIgnoreStart
		$results1 = $wpdb->get_results(
			$wpdb->prepare(
				"select ID, case when display_name != '' then display_name when display_name is null then user_login else user_login end as vdisplay_name 
				from %i inner join %i on ID=userid order by vdisplay_name;",
				$wp_users,
				$cs_users
			)
		);
		// @codingStandardsIgnoreEnd

		$user1_select_html .= "<option value='-1'>-</option>";
		foreach ( $results1 as $res ) {
			$user1_select_html .= "<option value='" . $res->ID . "' ";
			if ( (int) $res->ID == (int) $r0[0]->stellvertreter ) {
				$user1_select_html .= "selected='selected'";
			}
			$user1_select_html .= '>' . $res->vdisplay_name . "</option>\n";
		}

		// tippgruppenliste fuer select aufbauen.
		$tgroup_select_html = '';
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results1 = $wpdb->get_results( $wpdb->prepare( 'select tgid, name from %i order by name;', $cs_tippgroup ) );

		$tgroup_select_html .= "<option value='-1'>-</option>";
		foreach ( $results1 as $res ) {
			$tgroup_select_html .= "<option value='" . $res->tgid . "' ";
			if ( (int) $res->tgid == (int) $r0[0]->tippgroup ) {
				$tgroup_select_html .= "selected='selected'";
			}
			$tgroup_select_html .= '>' . $res->name . "</option>\n";
		}

		// anzeigen wenn der user admin des tippspiels ist.
		if ( $is_admin ) {
			$out .= '<b>' . __( 'You are a guessing game admin.', 'wp-championship' ) . '</b><br />';
		}

		// ausgabe des aktuellen punktestandes und des ranges.
		$rank = cs_get_ranking();

		$i            = 0;
		$j            = 1;
		$pointsbefore = -1;
		foreach ( $rank as $row ) {
			// platzierung erhoehen, wenn punkte sich veraendern.
			if ( $row->points != $pointsbefore ) {
				$i += $j;
				$j  = 1;
			} else {
				++$j;
			}

			if ( $row->userid == $uid ) {
				$out .= '<div><b>' . __( 'You have', 'wp-championship' ) . ' ' . $row->points . ' ' . __( 'points and your current rank is', 'wp-championship' ) . " $i.</b></div>";
			}
			// gruppenwechsel versorgen.
			$pointsbefore = $row->points;
		}

		// anzeigen wenn eine Punktekorrektur vorhanden ist.
		if ( isset( $r0[0]->penalty ) && intval( $r0[0]->penalty ) != 0 ) {
				$out .= '<div><b>' . __( 'You got a penalty.', 'wp-championship' ) . ' (' . intval( $r0[0]->penalty ) . ' ' . __( 'Points', 'wp-championship' ) . ')';
		}

		$out .= '<hr/>';
		// formularkopf.
		$out .= "<form method='post' action='" . get_permalink() . "'>\n";
		$out .= '<input name="wpc_nonce_usertipp" id="wpc_nonce_usertipp" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_usertipp' ) . '" />';
		$out .= "<div class='submit' style='text-align:right'>";

		// new add nonce field if possible.
		if ( function_exists( 'wp_nonce_field' ) ) {
			$out .= wp_nonce_field( 'wpcs-usertipp-update', '_wpnonce', true, false );
			$out .= '&nbsp;';
		}

		// wenn als stellvertreter unterwegs, dann hidden field mitgeben
		// um beim speichern zu erkennen fuer wen gespeichert wird.
		if ( $userdata->ID != $uid ) {
			$out .= "<input type='hidden' name='cs_stellv' value='$uid' />";
		}

		$out .= "<input type='submit' class='wpcs-button' id='wpcsupdate1' name='wpcsupdate' value='" . __( 'Save changes', 'wp-championship' ) . "' /></div>";

		// persönliche Einstellungen.
		$out .= '<h2>' . __( 'Settings', 'wp-championship' ) . "</h2>\n";
		$out .= "<table>\n";

		$out .= '<tr>';
		if ( ! $cs_stellv_schalter ) {
			$out .= '<td>' . __( 'Proxy:', 'wp-championship' ) . " <select name='stellvertreter'>" . $user1_select_html . '</select><br/>';
		} else {
			$out .= "<td>&nbsp;<input type='hidden' name='stellvertreter' value='-' /><br/>";
		}

		$out .= "<input type='checkbox' name='hidefinmatch' value='1' ";
		$out .= ( 1 == $r0[0]->hidefinmatch ? 'checked="checked"' : '' ) . ' /> ' . __( 'Hide finished matches', 'wp-championship' );
		$out .= '</td>';

		$out .= "<td><input type='checkbox' name='mailservice' value='1' ";
		$out .= ( 1 == $r0[0]->mailservice ? 'checked="checked"' : '' ) . ' /> ' . __( 'Mailservice', 'wp-championship' ) . '<br />';
		$out .= "<input type='checkbox' name='mailreceipt' value='1' ";
		$out .= ( 1 == $r0[0]->mailreceipt ? 'checked="checked"' : '' ) . ' /> ' . __( 'Mailconfirmation', 'wp-championship' ) . '<br/>';
		$out .= __( 'Mailformat', 'wp-championship' ) . ': ' . "<select name='mailformat'><option value='0'" . ( 0 == $r0[0]->mailformat ? ' selected="selected"' : '' ) . ">HTML</option><option value='1'" . ( 1 == $r0[0]->mailformat ? 'selected="selected"' : '' ) . '>Text</option></select><br/>';
		$out .= '</td></tr>';

		if ( get_option( 'cs_use_tippgroup' ) > 0 ) {
			$out .= '<tr><td colspan="2">' . __( 'Tip Group', 'wp-championship' ) . ': ';
			// Tippgruppe kann nur bis tunierbeginn abgegeben werden
			// ermittle aktuell blog zeit.
			$blog_now = current_time( 'mysql', false );

			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$mr = $wpdb->get_row( $wpdb->prepare( 'select min(matchtime) as mintime from %i', $cs_match ) );

			if ( $blog_now > $mr->mintime ) {
				$out .= '<select name="tippgroupshow" disabled="disabled">' . $tgroup_select_html . '</select>';
				$out .= '<input type="hidden" name="tippgroup" value="' . $r0[0]->tippgroup . '" /></td></tr>';
			} else {
				$out .= '<select name="tippgroup">' . $tgroup_select_html . '</select></td></tr>';
			}
		}

		// joker feature - list of match ids to get double points for.
		if ( get_option( 'cs_joker_player' ) != '' && get_option( 'cs_joker_player' ) > 0 ) {
			$out .= '<tr><td style="text-align:center" colspan="2"><label for="cs_jokerlist">' . __( 'Joker - Match-IDs', 'wp-championship' ) . '</label>' . "\n";
			$out .= '(max. ' . get_option( 'cs_joker_player' ) . '):';
			// Joker können nur bis tunierbeginn gesetzt werden
			// ermittle aktuell blog zeit.
			$blog_now = current_time( 'mysql', false );

			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$mr = $wpdb->get_row( $wpdb->prepare( 'select min(matchtime) as mintime from %i', $cs_match ) );

			$jdis = '';
			if ( $blog_now > $mr->mintime ) {
				$jdis = ' disabled="disabled" ';
			}
			$out .= '<input name="cs_jokerlist" id="cs_jokerlist" type="text" value="' . $r0[0]->jokerlist . '" size="20" ' . $jdis . '/></td></tr>' . "\n";
		}

		$out .= "<tr><td colspan='2'>&nbsp;</td></tr>";

		if ( get_option( 'cs_pts_champ' ) > 0 ) {
			$out .= '<tr><td style="text-align:center" colspan="2">' . __( 'Winner-tip', 'wp-championship' ) . ': ';
			// weltmeistertipp kann nur bis tunierbeginn abgegeben werden
			// ermittle aktuell blog zeit.
			$blog_now = current_time( 'mysql', false );

			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$mr = $wpdb->get_row( $wpdb->prepare( 'select min(matchtime) as mintime from %i', $cs_match ) );

			if ( $blog_now > $mr->mintime ) {
				$out .= '<select name="championshow" disabled="disabled">' . $team1_select_html . '</select>';
				$out .= '<input type="hidden" name="champion" value="' . $r0[0]->champion . '" /></td></tr>';
			} else {
				$out .= '<select name="champion">' . $team1_select_html . '</select></td></tr>';
			}
		}

		$out .= "</table>\n";

		// Spielübersicht Vorrunde.
		if ( file_exists( get_stylesheet_directory() . '/wp-championship/icons/' ) ) {
			$iconpath = get_stylesheet_directory_uri() . '/wp-championship/icons/';
		} else {
			$iconpath = plugins_url( 'icons/', __FILE__ );
		}

		// sortierbare tabelle nur im tunier modus.
		if ( 1 == get_option( 'cs_modus' ) ) {
			if ( ! $cs_tipp_sort >= 1 ) {
				$cs_tipp_sort = 1;
			}
			// ermittle nicht sortierbare und datumssortierte spalten.
			$dispcols         = strval( (int) ! $cs_col_group ) . strval( (int) ! $cs_col_matchid ) . strval( (int) ! $cs_col_icon1 ) . strval( (int) ! $cs_col_match ) . strval( (int) ! $cs_col_icon2 ) . strval( (int) ! $cs_col_location ) . strval( (int) ! $cs_col_time ) . strval( (int) ! $cs_col_tip ) . strval( (int) ( ! $cs_goalsum > -1 && 0 == $cs_goaslum_auto ) ) . strval( (int) ! $cs_col_points );
			$nosortcol_icon1  = substr_count( $dispcols, '1', 0, 2 );
			$nosortcol_icon2  = substr_count( $dispcols, '1', 0, 4 );
			$datesortcol_time = substr_count( $dispcols, '1', 0, 6 );
			// setze parameterstring für tablesorter zusammen.
			$ts_sortstring = '';
			if ( ! $cs_col_icon1 ) {
				$ts_sortstring .= $nosortcol_icon1 . ':{sorter:false}, ';
			}
			if ( ! $cs_col_icon2 ) {
				$ts_sortstring .= $nosortcol_icon2 . ':{sorter:false}, ';
			}
			if ( ! $cs_col_time ) {
				$ts_sortstring .= $datesortcol_time . ":{sorter:'germandate'}, ";
			}
			$ts_sortstring = substr( $ts_sortstring, 0, -2 );

			$out .= "<script type='text/javascript'><!--\njQuery(document).ready(function() {\n ";
			$out .= 'jQuery.tablesorter.addParser({ id: "germandate", is: function(s) { return false; },  format: function(s) { s.replace(/(\r\n|\n|\r)/gm,""); s=s.replace(".",""); s=s.replace("<br>",""); s=s.replace(":",""); var a = s.substring(2,4)+s.substring(0,2)+s.substring(4); return a;}, type: "numeric" });';
			$out .= "jQuery('#ptab').tablesorter({sortList:[[" . ( --$cs_tipp_sort ) . ',0]],headers:{' . $ts_sortstring . "}}); }); jQuery(document).ready(function() { jQuery('#ftab').tablesorter({sortList:[[0,0]],headers:{2:{sorter:false},4:{sorter:false}, 6:{sorter:'germandate'}}}); });\n//--></script>\n";
		}
		// collapse / expand für den bundesliga modus.
		if ( file_exists( get_stylesheet_directory() . '/wp-championship/arrow_down.jpg' ) ) {
			$dips_arrow_url = get_stylesheet_directory_uri() . '/wp-championship/';
		} else {
			$dips_arrow_url = plugins_url( '/', __FILE__ );
		}

		if ( get_option( 'cs_modus' ) == 2 ) {
			$out .= "<script type='text/javascript'><!--\njQuery(document).ready(function() { var toggleMinus = '" . $dips_arrow_url . 'arrow_down.jpg' . "'; var togglePlus = '" . $dips_arrow_url . 'arrow_right.jpg' . "'; var AsubHead = jQuery('tbody th:first-child'); AsubHead.prepend('<img src=\"' + toggleMinus + '\" alt=\"collapse this section\" />'); jQuery('img', AsubHead).addClass('clickable') .click(function() { var toggleSrc = jQuery(this).attr('src'); if ( toggleSrc == toggleMinus ) { jQuery(this).attr('src', togglePlus) .parents('tr').siblings().fadeOut('fast'); } else{ jQuery(this).attr('src', toggleMinus) .parents('tr').siblings().fadeIn('fast'); }; }); jQuery('.clickable').trigger('click'); jQuery('img','#currspieltag').trigger('click'); });\n//--></script>\n";
		}

		$out .= "<br /><section id='flip-scroll'>";
		$out .= '<h2>' . __( 'Preliminary', 'wp-championship' ) . "</h2>\n";
		$out .= "<table id='ptab' class='tablesorter cf' ><thead class='cf'><tr>\n";

		if ( get_option( 'cs_modus' ) == 1 && ! $cs_col_group ) {
			$out .= '<th scope="col" style="text-align: center">' . $cs_label_group . '</th>' . "\n";
		}
		if ( ! $cs_col_matchid ) {
			$out .= '<th scope="col" style="text-align: center">' . $cs_label_matchid . '</th>' . "\n";
		}
		if ( ! $cs_col_icon1 ) {
			$out .= '<th >' . $cs_label_icon1 . '</th>' . "\n";
		}
		if ( ! $cs_col_match ) {
			$out .= '<th scope="col" style="text-align: center">' . $cs_label_match . '</th>' . "\n";
		}
		if ( ! $cs_col_icon2 ) {
			$out .= '<th >' . $cs_label_icon2 . '</th>' . "\n";
		}
		if ( ! $cs_col_location ) {
			$out .= '<th scope="col" style="text-align: center">' . $cs_label_location . '</th>' . "\n";
		}
		if ( ! $cs_col_time ) {
			$out .= '<th id="p1stsort" scope="col" style="text-align: center">' . $cs_label_time . '</th>' . "\n";
		}
		if ( ! $cs_col_tip ) {
			$out .= '<th style="text-align:center">' . $cs_label_tip . '</th>';
		}
		if ( $cs_goalsum > -1 && 0 == $cs_goalsum_auto ) {
			$out .= '<th style="text-align:center">' . __( 'Sum of<br />goals', 'wp-championship' ) . '</th>';
		}
		if ( ! $cs_col_points ) {
			$out .= '<th style="text-align:center">' . $cs_label_points . '</th></tr>';
		}

		$out .= '</thead>' . "\n";
		if ( get_option( 'cs_modus' ) == 1 ) {
			$out .= '<tbody>' . "\n";
		}

		// ermittle bis zu welchem datum wir die spiele anzeigen, falls eingeschränkt durch cs_number_of_tippdays.
		$cs_number_of_tippdays = get_option( 'cs_number_of_tippdays' ) - 1;
		$low_spieltag          = 0;
		$high_spieltag         = 999;
		if ( intval( $cs_number_of_tippdays ) > 0 ) {

			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$low_spieltag = $wpdb->get_var( $wpdb->prepare( 'select max(spieltag) as a from %i where date(matchtime) <= current_date()', $cs_match ) );

			if ( null == $low_spieltag ) {
				$low_spieltag = 0;
			}

			// @codingStandardsIgnoreStart
			$rhs = $wpdb->get_var(
				$wpdb->prepare(
					'select spieltag as a 
					from %i 
					where date(matchtime) >= current_date() 
					group by spieltag limit 1 offset %d',
					$cs_match,
					$cs_number_of_tippdays
				)
			);
			// @codingStandardsIgnoreEnd
			$high_spieltag = ( null == $rhs ? $high_spieltag : $rhs );
		}

		// for future use to define a maximum date to show matches in turnier mode.
		$high_date_to_show = '1900-01-01';

		// match loop
		// hole match daten.
		if ( 1 == get_option( 'cs_modus' ) ) {
			// @codingStandardsIgnoreStart
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid as mid,b.groupid as groupid,b.tid as tid1, b.name as team1,b.icon as icon1, 
					c.tid as tid2, c.name as team2,c.icon as icon2,a.location as location,
					date_format(a.matchtime, %s) as matchtime,a.matchtime as origtime,
					a.result1 as result1,a.result2 as result2,a.winner as winner,a.round as round, a.spieltag as spieltag 
					from %i a inner join %i b on a.tid1=b.tid 
					inner join %i c on a.tid2=c.tid 
					where a.round in ('V','F') 
					and date(a.matchtime) >= %s 
					order by origtime;",
					'%d.%m<br />%H:%i',
					$cs_match,
					$cs_team,
					$cs_team,
					$high_date_to_show
				)
			);
			// @codingStandardsIgnoreEnd
		} else {
			// @codingStandardsIgnoreStart
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid as mid,b.groupid as groupid,b.tid as tid1, b.name as team1,b.icon as icon1, 
					c.tid as tid2, c.name as team2,c.icon as icon2,a.location as location,
					date_format(a.matchtime, %s) as matchtime,a.matchtime as origtime,
					a.result1 as result1,a.result2 as result2,a.winner as winner,a.round as round, a.spieltag as spieltag 
					from %i a inner join %i b on a.tid1=b.tid 
					inner join %i c on a.tid2=c.tid 
					where a.round = 'V' and a.spieltag between %d and %d 
					order by spieltag,origtime;",
					'%d.%m<br />%H:%i',
					$cs_match,
					$cs_team,
					$cs_team,
					$low_spieltag,
					$high_spieltag
				)
			);
			// @codingStandardsIgnoreEnd
		}

		// hole tipps des users.
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results2 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d', $cs_tipp, $uid ) );

		// und lege die tipps im array ppost ab.
		foreach ( $results2 as $res ) {
			if ( -1 != $res->result1 ) {
				$ppost[ 'gt1_' . $res->mid ] = $res->result1;
			}
			if ( -1 != $res->result2 ) {
				$ppost[ 'gt2_' . $res->mid ] = $res->result2;
			}
			if ( -1 != $res->result3 ) {
				$ppost[ 'gt3_' . $res->mid ] = $res->result3;
			}

			// setze -1 felder auf leer wenn diese keinen fehler ausgeloest haben.
			if ( isset( $ppost[ 'gt1_' . $res->mid ] ) && -1 == $ppost[ 'gt1_' . $res->mid ] && ! array_key_exists( 'gt1_' . $res->mid, $errlist ) ) {
				$ppost[ 'gt1_' . $res->mid ] = '';
			}
			if ( isset( $ppost[ 'gt2_' . $res->mid ] ) && -1 == $ppost[ 'gt2_' . $res->mid ] && ! array_key_exists( 'gt2_' . $res->mid, $errlist ) ) {
				$ppost[ 'gt2_' . $res->mid ] = '';
			}
			if ( isset( $ppost[ 'gt3_' . $res->mid ] ) && -1 == $ppost[ 'gt3_' . $res->mid ] && ! array_key_exists( 'gt3_' . $res->mid, $errlist ) ) {
				$ppost[ 'gt3_' . $res->mid ] = '';
			}

			$ppost[ 'pt_' . $res->mid ] = $res->points;
		}

		$lastmatchround  = '';
		$bl_lastspieltag = '';
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$bl_currspieltag = $wpdb->get_var( $wpdb->prepare( 'select min(spieltag) as mst from %i where result1=-1;', $cs_match ) );

		if ( ! isset( $errlist ) ) {
			$errlist = array();
		}

		foreach ( $results as $res ) {

			// wenn die beendeten Spiele versteckt werden sollen springen wir bei vorhandenem Ergebnis einfach zum nächsten Satz.
			if ( $r0[0]->hidefinmatch && $res->result1 > -1 && $res->result2 > -2 ) {
				continue;
			}

			if ( 'V' == $lastmatchround && 'F' == $res->round ) {
				$out .= '</tbody></table></section>' . "<p>&nbsp;</p>\n";
				// submit button.
				$out .= "<div class='submit' style='text-align:right'>";
				$out .= "<input type='submit' class='wpcs-button' id='wpcsupdate2' name='wpcsupdate' value='" . __( 'Save changes', 'wp-championship' ) . "' /></div>";

				$out .= "<section id='flip-scroll'>";
				$out .= '<h2>' . __( 'Finals', 'wp-championship' ) . "</h2>\n";
				$out .= "<table id='ftab' class='tablesorter cf'><thead class='cf'><tr>\n";

				if ( ! $cs_col_matchid ) {
					$out .= '<th scope="col" style="text-align: center">' . $cs_label_matchid . '</th>' . "\n";
				}
				if ( ! $cs_col_icon1 ) {
					$out .= '<th >' . $cs_label_icon1 . '</th>' . "\n";
				}
				if ( ! $cs_col_match ) {
					$out .= '<th scope="col" style="text-align: center">' . $cs_label_match . '</th>' . "\n";
				}
				if ( ! $cs_col_icon2 ) {
					$out .= '<th >' . $cs_label_icon2 . '</th>' . "\n";
				}
				if ( ! $cs_col_location ) {
					$out .= '<th scope="col" style="text-align: center">' . $cs_label_location . '</th>' . "\n";
				}
				if ( ! $cs_col_time ) {
					$out .= '<th id="p1stsort" scope="col" style="text-align: center">' . $cs_label_time . '</th>' . "\n";
				}
				if ( ! $cs_col_tip ) {
					$out .= '<th style="text-align:center">' . $cs_label_tip . '</th>';
				}
				if ( $cs_goalsum > -1 && 0 == $cs_goalsum_auto ) {
					$out .= '<th style="text-align:center">' . __( 'Sum of<br />goals', 'wp-championship' ) . '</th>';
				}
				if ( ! $cs_col_points ) {
					$out .= '<th style="text-align:center">' . $cs_label_points . '</th></tr>';
				}

				$out .= '</thead>' . "\n";
				$out .= '<tbody>' . "\n";
			}

			// im bundesligamodus werden die spieltage durch eine untertitelzeile getrennt
			// für das collapse/expand feature.
			if ( 2 == get_option( 'cs_modus' ) && $bl_lastspieltag < $res->spieltag ) {
				if ( '' != $bl_lastspieltag ) {
					$out .= "</tbody>\n";
				}
				$bl_idext = ( $bl_currspieltag == $res->spieltag ? "id='currspieltag'" : '' );

				$out .= "<tbody><tr><th style='text-align:left' colspan='7' " . $bl_idext . '>' . $res->spieltag . ". Spieltag</th></tr>\n";
			}

			// start des spiels als unix timestamp.
			$match_start = $res->origtime;
			// start des spiels in der browser timezone als unix timestamp.
			$match_local_start = strtotime( $res->origtime ) + $timediff;
			// tooltip nur anzeigen, wenn die zeit unterschiedlich ist.
			$match_tooltip = '';
			if ( 0 != $timediff ) {
				$match_tooltip = "title='Spielbeginn (lokal):" . strftime( '%d.%m %H:%M', $match_local_start ) . "'";
			}
			$out .= '<tr>';
			// Erste Spalte für Vorrunde mit und ohne Hoverlink.
			if ( 'V' == $res->round ) {
				if ( 1 == get_option( 'cs_modus' ) && ( ! isset( $cs_col_group ) || ! $cs_col_group ) ) {
					if ( 1 == get_option( 'cs_hovertable' ) ) {
						$hid = 'cs_hovertable_' . $hovertable_count;
						$hovertable_count++;

						$ajaxurl = admin_url( 'admin-ajax.php' );
						$hlink   = $ajaxurl . '?action=tooltip_groupstats&amp;groupid=' . ( 'V' == $res->round ? $res->groupid : $res->mid );

						$out .= "<td style='text-align:center'><div><a href='$hlink' id='$hid'  >" . $res->groupid . '</a></div></td>';
					} else {
						$out .= "<td style='text-align:center'>" . $res->groupid . '</td>';
					}
				}
			}
			// Erste Spalte für Finalrunde.
			if ( 'F' == $res->round && ! $cs_col_matchid ) {
				$out .= "<td style='text-align:center'>" . $res->mid . '</td>';
			}

			// Zweite Spalte für Vorrunde.
			if ( 'V' == $res->round && ! $cs_col_matchid ) {
				$out .= "<td style='text-align:center'>" . $res->mid . '</td>';
			}

			if ( ! $cs_col_icon1 ) {
				if ( '' != $res->icon1 ) {
					if ( substr( $res->icon1, 0, 4 ) == 'http' ) {
						$out .= "<td style='text-align:center'><img class='csicon' alt='icon1' src='" . $res->icon1 . "' /></td>";
					} else {
						$out .= "<td style='text-align:center'><img class='csicon' alt='icon1' src='" . $iconpath . $res->icon1 . "' /></td>";
					}
				} else {
					$out .= '<td>&nbsp;</td>';
				}
			}
			if ( ! $cs_col_match ) {
				$out .= "<td style='text-align:center'>";
				if ( 1 == get_option( 'cs_hovertable' ) ) {
					$hid = 'cs_hovertable_' . $hovertable_count;
					$hovertable_count++;

					$ajaxurl = admin_url( 'admin-ajax.php' );
					$hlink   = $ajaxurl . '?action=tooltip_matchstats&amp;teamid=' . $res->tid1;

					$out .= "<a href='$hlink' id='$hid'  >" . ( 'V' == $res->round ? $res->team1 : cs_team2text( $res->team1 ) ) . '</a> - ';
				} else {
					$out .= ( 'V' == $res->round ? $res->team1 : cs_team2text( $res->team1 ) ) . ' - ';
				}

				if ( 1 == get_option( 'cs_hovertable' ) ) {
					$hid = 'cs_hovertable_' . $hovertable_count;
					$hovertable_count++;

					$ajaxurl = admin_url( 'admin-ajax.php' );
					$hlink   = $ajaxurl . '?action=tooltip_matchstats&amp;teamid=' . $res->tid2;

					$out .= "<a href='$hlink' id='$hid'  >" . ( 'V' == $res->round ? $res->team2 : cs_team2text( $res->team2 ) ) . '</a></td>';
				} else {
					$out .= ( 'V' == $res->round ? $res->team2 : cs_team2text( $res->team2 ) ) . '</td>';
				}
			}
			if ( ! $cs_col_icon2 ) {
				if ( '' != $res->icon2 && ! $cs_col_icon2 ) {
					if ( substr( $res->icon2, 0, 4 ) == 'http' ) {
						$out .= "<td style='text-align:center'><img class='csicon' alt='icon2' src='" . $res->icon2 . "' /></td>";
					} else {
						$out .= "<td style='text-align:center'><img class='csicon' alt='icon2' src='" . $iconpath . $res->icon2 . "' /></td>";
					}
				} else {
					$out .= '<td>&nbsp;</td>';
				}
			}
			if ( ! $cs_col_location ) {
				$out .= "<td style='text-align:center'>" . $res->location . '</td>';
			}
			if ( ! $cs_col_time ) {
				$out .= "<td style='text-align:center' " . $match_tooltip . ' >' . $res->matchtime . '</td>';
			}
			$out .= "<td style='text-align:center'>";

			// fehlerklasse setzen, wenn erforderlich.
			if ( array_key_exists( 'gt1_' . $res->mid, $errlist ) ) {
				$errclass = ' cs_inputerror ';
			} else {
				$errclass = '';
			}

			$leftsidetipp = ( isset( $ppost[ 'gt1_' . $res->mid ] ) && -1 != $ppost[ 'gt1_' . $res->mid ] || array_key_exists( 'gt1_' . $res->mid, $errlist ) ? $ppost[ 'gt1_' . $res->mid ] : '' );
			if ( -1 != $res->result1 || $blog_now > $match_start ||
					( 'V' == $res->round && get_option( 'cs_lock_round1' ) ) ) {
				// $out .= $ppost['gt1_'.$res->mid]." : ";
				$out .= $leftsidetipp . ' : ';
			} else {
				// $out .= "<input class='cs_entry $errclass' name='gt1_".$res->mid."' id='gt1_".$res->mid."' type='text' value='".(isset($ppost['gt1_'.$res->mid])?$ppost['gt1_'.$res->mid]:"")."' size='1' maxlength='2' />";
				$out .= "<input class='cs_entry $errclass' name='gt1_" . $res->mid . "' id='gt1_" . $res->mid . "' type='text' value='" . $leftsidetipp . "' size='1' maxlength='2' />";
			}
			// fehlerklasse setzen, wenn erforderlich.
			if ( array_key_exists( 'gt2_' . $res->mid, $errlist ) || array_key_exists( 'gt1_' . $res->mid, $errlist ) ) {
				$errclass = ' cs_inputerror ';
			} else {
				$errclass = '';
			}

			$rightsidetipp = ( isset( $ppost[ 'gt2_' . $res->mid ] ) && -1 != $ppost[ 'gt2_' . $res->mid ] || array_key_exists( 'gt2_' . $res->mid, $errlist ) ? $ppost[ 'gt2_' . $res->mid ] : '' );
			if ( -1 != $res->result2 || $blog_now > $match_start ||
					( 'V' == $res->round && get_option( 'cs_lock_round1' ) ) ) {
				$out .= $rightsidetipp;
			} else {
				$out .= " : <input class='cs_entry $errclass' name='gt2_" . $res->mid . "' id='gt2_" . $res->mid . "' type='text' value='" . $rightsidetipp . "' size='1' maxlength='2' />";
			}
			$out .= '<br />';

			// der admin darf ergebnisse erfassen, alle anderen duerfen sie nur sehen.
			if ( $is_admin ) {
				// fehlerklasse und wert setzen.
				if ( array_key_exists( 'rt1_' . $res->mid, $errlist ) ) {
					$errclass  = ' cs_inputerror ';
					$rt1_value = (int) $ppost[ 'rt1_' . $res->mid ]; // alten eingabewert anzeigen.
				} else {
					$errclass  = '';
					$rt1_value = ( -1 == $res->result1 ? '-' : $res->result1 );
				}
				$out .= "<input class='cs_entry $errclass' name='rt1_" . $res->mid . "' id='rt1_" . $res->mid . "' type='text' size='1' maxlength='2' value='$rt1_value' /> : ";

				// fehlerklasse setzen, wenn erforderlich.
				if ( array_key_exists( 'rt2_' . $res->mid, $errlist ) ) {
					$errclass  = ' cs_inputerror ';
					$rt2_value = (int) $ppost[ 'rt2_' . $res->mid ]; // alten eingabewert anzeigen.
				} else {
					$errclass  = '';
					$rt2_value = ( -1 == $res->result2 ? '-' : $res->result2 );
				}
				$out .= "<input class='cs_entry $errclass' name='rt2_" . $res->mid . "' id='rt2_" . $res->mid . "' type='text' size='1' maxlength='2' value='$rt2_value' />";

				$out .= '</td>';
			} else {
				$out .= ( -1 == $res->result1 ? '-' : $res->result1 ) . ':' . ( -1 == $res->result2 ? '-' : $res->result2 ) . '</td>';
			}

			if ( $cs_goalsum > 0 && 0 == $cs_goalsum_auto ) {
				$gt3_value = (int) $ppost[ 'gt3_' . $res->mid ];
				if ( array_key_exists( 'gt3_' . $res->mid, $errlist ) ) {
					$errclass = ' cs_inputerror ';
				} else {
					$errclass = '';
				}

				if ( -1 != $res->result2 || $blog_now > $match_start ) {
					$out .= '<td>' . (int) $ppost[ 'gt3_' . $res->mid ] . '</td>';
				} else {
					$out .= "<td><input class='cs_entry $errclass' name='gt3_" . $res->mid . "' id='gt3_" . $res->mid . "' type='text' size='1' maxlength='2' value='$gt3_value' /></td> ";
				}
			}

			$out .= "<td style='text-align:center'>" . ( isset( $ppost[ 'pt_' . $res->mid ] ) && -1 != $ppost[ 'pt_' . $res->mid ] ? $ppost[ 'pt_' . $res->mid ] : '-' ) . '</td>';
			$out .= "</tr>\n";

			// gruppenwechsel versorgen.
			$lastmatchround  = $res->round;
			$bl_lastspieltag = $res->spieltag;
		}
		$out .= '</tbody></table>';
		$out .= '</section>' . "\n<p>&nbsp;</p>";

		// submit button.
		$out .= "<div class='submit' style='text-align:right'>";
		$out .= "<input type='submit' class='wpcs-button' id='wpcsupdate3' name='wpcsupdate' value='" . __( 'Save changes', 'wp-championship' ) . "' /></div>";

		$out .= '</form>';
		//
		// ausgabe javascript fuer hovertable funktion.
		//
		if ( get_option( 'cs_hovertable' ) == 1 ) {
			$out .= "<script type='text/javascript'>";
			$out .= 'jQuery(document).ready(function() {';
			for ( $i = 0;$i < $hovertable_count;$i++ ) {
				$out .= 'jQuery("#cs_hovertable_' . $i . '").wpcstooltip({cssClass: "tooltip-red"});' . "\n";
			}
			$out .= '});</script>';
		}

		return $out;
	}
}
