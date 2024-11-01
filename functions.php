<?php
/**
 * Functions to use with wp-championship.
 *
 * @package wp-championship
 */

if ( ! function_exists( 'admin_message' ) ) {
	/**
	 * Function to show an admin message on an admin page.
	 *
	 * @param string $msg Message to show as admin message.
	 */
	function admin_message( $msg ) {
		echo "<div class='updated'><p><strong>";
		echo wp_kses( $msg, wpc_allowed_tags() );
		echo "</strong></p></div>\n";
	}
}



if ( ! function_exists( 'cs_get_group_selector' ) ) {
	/**
	 * Return an html form field selector for num groups with id.
	 *
	 * @param integer $num numberof entries.
	 * @param string  $id id of the HTML select tag.
	 * @param integer $sel entry to select.
	 */
	function cs_get_group_selector( $num, $id, $sel = -1 ) {
		$groupstr = 'ABCDEFGHIJKLM';
		$out      = '';
		$out     .= '<select name="' . $id . '" id="' . $id . '" class="postform">' . "\n";
		// build group selection box.
		for ( $i = 0; $i < $num; $i++ ) {
			$charone = substr( $groupstr, $i, 1 );
			$out    .= '<option value="' . $charone . '"';
			if ( $charone == $sel ) {
				$out .= ' selected';
			}
			$out .= '>' . $charone . '</option>';
		}
		$out .= '</select>';
		return $out;
	}
}


if ( ! function_exists( 'cs_get_place_selector' ) ) {
	/**
	 * Returns an html form field selector for num places with id
	 *
	 * @param integer $num numberof entries.
	 * @param string  $id id of the HTML select tag.
	 * @param integer $sel entry to select.
	 */
	function cs_get_place_selector( $num, $id, $sel = -1 ) {
		$out  = '';
		$out .= '<select name="' . $id . '" id="' . $id . '" class="postform">' . "\n";
		// build group selection box.
		for ( $i = 1; $i <= $num; $i++ ) {
			$out .= '<option value="' . $i . '"';
			if ( $i == $sel ) {
				$out .= ' selected';
			}
			$out .= '>' . $i . '</option>';
		}
		$out .= '</select>';
		return $out;
	}
}


if ( ! function_exists( 'cs_calc_points' ) ) {
	/**
	 * Calculates the points for each user and match and stores it in cs_tipp.
	 *
	 * @param bool $new if true do a complete calculation.
	 */
	function cs_calc_points( $new = false ) {
		include 'globals.php';
		global $wpdb;

		// always do a complete calculation.
		$new = true;

		// alles zuruecksetzen.
		if ( $new ) {
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$res = $wpdb->query( $wpdb->prepare( 'update %i set points=-1 where points <>-1', $cs_tipp ) );
		}

		// punktevorgaben lesen.
		$cs_pts_tipp         = get_option( 'cs_pts_tipp' );           // korrekter tipp.
		$cs_pts_tendency     = get_option( 'cs_pts_tendency' );    // tendenz.
		$cs_pts_supertipp    = get_option( 'cs_pts_supertipp' );  // tendenz und tordifferenz.
		$cs_pts_champ        = get_option( 'cs_pts_champ' );          // championtipp.
		$cs_pts_oneside      = get_option( 'cs_pts_oneside' );      // einseitg richtiger tipp.
		$cs_oneside_tendency = get_option( 'cs_oneside_tendency' );  // einseitg richtiger tipp nur mit richtiger tendenz gültig.
		$cs_goalsum          = get_option( 'cs_goalsum' );              // schwellwert für torsumme tipp.
		$cs_pts_goalsum      = get_option( 'cs_pts_goalsum' );      // punkte für tosummentipp.
		$cs_goalsum_auto     = get_option( 'cs_goalsum_auto' );  // torsummentipp aus tipp berechnen oder separat?
		$cs_goalsum_equal    = get_option( 'cs_goalsum_equal' ); // torsummentipp zieht nur bei gleichheit nicht bei >=.
		$cs_joker_idlist     = get_option( 'cs_joker_idlist' );   // Liste die Match IDs fuer die doppelte Punkte vergeben werden.
		$cs_joker_player     = get_option( 'cs_joker_player' );   // Anzahl der Joker, die jeder Spieler selbst setzen kann.

		// genauer treffer.
		// @codingStandardsIgnoreStart
		$res = $wpdb->query(
			$wpdb->prepare(
				'update %i b inner join %i a on a.mid=b.mid and a.result1=b.result1 and a.result2=b.result2 and a.result1 <> -1 and a.result2 <> -1 
				set b.points= %d where b.points  =-1 and b.result1>-1 and b.result2>-1;',
				$cs_tipp,
				$cs_match,
				$cs_pts_tipp
			)
		);

		// tordifferenz.
		$res = $wpdb->query(
			$wpdb->prepare(
				'update %i b inner join %i a on a.mid=b.mid and a.result1 <> -1 and a.result2 <> -1 and abs(a.result1 - a.result2) = abs(b.result1 - b.result2) 
				and ( (a.result1>a.result2 and b.result1>b.result2) or (a.result1<a.result2 and b.result1<b.result2 ) 
				or (a.result1=a.result2 and b.result1=b.result2) )
				set points = %d where b.points = -1 and b.result1>-1 and b.result2>-1;',
				$cs_tipp,
				$cs_match,
				$cs_pts_supertipp
			)
		);

		// tendenz.
		$res = $wpdb->query(
			$wpdb->prepare(
				'update %i b inner join %i a on a.mid=b.mid and a.result1 <> -1 and a.result2 <> -1 
				and ( (a.result1<a.result2 and b.result1<b.result2) 
				or (a.result1=a.result2 and b.result1=b.result2) or (a.result1>a.result2 and b.result1>b.result2)  ) 
				set points= %d where b.points = -1 and b.result1>-1 and b.result2>-1;',
				$cs_tipp,
				$cs_match,
				$cs_pts_tendency
			)
		);
		// @codingStandardsIgnoreEnd

		// einseitig richtiger tipp ohne und mit tendenz.
		if ( $cs_pts_oneside > 0 ) {
			if ( 1 == $cs_oneside_tendency ) { // nur mit Tendenz.
				// @codingStandardsIgnoreStart
				$res = $wpdb->query(
					$wpdb->prepare(
						'update %i b 
						inner join %i a on a.mid=b.mid and a.result1 <> -1 and a.result2 <> -1 
						and ( (a.result1<a.result2 and b.result1<b.result2) or (a.result1=a.result2 and b.result1=b.result2) 
						or (a.result1>a.result2 and b.result1>b.result2)  ) and ( a.result1=b.result1 or a.result2=b.result2 ) 
						set points= points + %d where b.points >= 0 and b.result1>-1 and b.result2>-1;',
						$cs_tipp,
						$cs_match,
						$cs_pts_oneside
					)
				);
				// @codingStandardsIgnoreend
			}

			if ( 0 == $cs_oneside_tendency ) { // immer.
				// @codingStandardsIgnoreStart
				$res = $wpdb->query(
					$wpdb->prepare(
						'update %i b inner join %i a on a.mid=b.mid and a.result1 <> -1 and a.result2 <> -1 
						and ( a.result1=b.result1 or a.result2=b.result2 ) 
						set points= points + %d where b.points > -1 and b.result1>-1 and b.result2>-1;',
						$cs_tipp,
						$cs_match,
						$cs_pts_oneside
					)
				);
				// @codingStandardsIgnoreEnd

				// @codingStandardsIgnoreStart
				$res = $wpdb->query(
					$wpdb->prepare(
						'update %i b inner join %i a on a.mid=b.mid and a.result1 <> -1 and a.result2 <> -1 
						and ( a.result1=b.result1 or a.result2=b.result2 ) 
						set points=%d where b.points = -1 and b.result1>-1 and b.result2>-1;',
						$cs_tipp,
						$cs_match,
						$cs_pts_oneside
					)
				);
				// @codingStandardsIgnoreEnd
			}

			if ( 2 == $cs_oneside_tendency ) { // nur ohne Tendenz.
				// @codingStandardsIgnoreStart
				$res = $wpdb->query(
					$wpdb->prepare(
						'update %i b inner join %i a on a.mid=b.mid and a.result1 <> -1 and a.result2 <> -1 
						and ( a.result1=b.result1 or a.result2=b.result2 ) 
						set points= %d 
						where b.points = -1 and b.result1>-1 and b.result2>-1;',
						$cs_tipp,
						$cs_match,
						$cs_pts_oneside
					)
				);
				// @codingStandardsIgnoreEnd
			}
		}

		// falscher tipp (setzt alle restlichen auf 0).
		// @codingStandardsIgnoreStart
		$res = $wpdb->query(
			$wpdb->prepare(
				'update %i b inner join %i a on  a.mid=b.mid and a.result1 <> -1 and a.result2 <> -1  
				set points=0 where b.points = -1 and b.result1>-1 and b.result2>-1;',
				$cs_tipp,
				$cs_match
			)
		);
		// @codingStandardsIgnoreEnd

		// torsummen tipp prüfen und ggf addieren.
		if ( $cs_goalsum > -1 ) {
			// ermittle operator fuer punktevergabe.
			$goalsum_operator = '<=';
			if ( 1 == $cs_goalsum_equal ) {
				$goalsum_operator = '=';
			}
			if ( 0 == $cs_goalsum_auto ) {
				// @codingStandardsIgnoreStart
				$res = $wpdb->query(
					$wpdb->prepare(
						'update %i b inner join %i a on a.mid=b.mid and a.result1 <> -1 and a.result2 <> -1 
						and ( a.result1+a.result2 %i b.result3 and a.result1+a.result2 > %d ) 
						set points=points+%d where b.result1>-1 and b.result2>-1;',
						$cs_tipp,
						$cs_match,
						$goalsum_operator,
						$cs_goalsum,
						$cs_pts_goalsum
					)
				);
				// @codingStandardsIgnoreEnd
			} else {
				// @codingStandardsIgnoreStart
				$res = $wpdb->query(
					str_replace(
						'GOALSUMOP',
						$goalsum_operator,
						$wpdb->prepare(
							'update %i b inner join %i a on a.mid=b.mid and a.result1 <> -1 and a.result2 <> -1 
							and ( a.result1+a.result2 GOALSUMOP b.result1+b.result2 and a.result1+a.result2 > %d ) 
							set points=points+%d where b.result1>-1 and b.result2>-1;',
							$cs_tipp,
							$cs_match,
							$cs_goalsum,
							$cs_pts_goalsum
						)
					)
				);
				// @codingStandardsIgnoreEnd
			}
		}

		// joker verarbeiten.

		// globale joker
		// Liste aus string holen.
		$joker_mid_list = explode( ',', $cs_joker_idlist );
		// String für SQL wieder zusammen bauen.
		$jml = '';
		foreach ( $joker_mid_list as $jm ) {
			$jml .= $jm . ',';
		}
		// letztes Komma abschneiden.
		$jml = substr( $jml, 0, -1 );

		// update points.
		if ( strlen( trim( $jml ) ) > 0 ) {
			// @codingStandardsIgnoreStart
			$res = $wpdb->query(
				$wpdb->prepare(
					'update %i b inner join %i a on a.mid=b.mid 
					and b.mid IN ( %s ) and a.result1 <> -1 and a.result2 <> -1 
					set b.points=b.points * 2 
					where b.result1>-1 and b.result2>-1;',
					$cs_tipp,
					$cs_match,
					$jml
				)
			);
			// @codingStandardsIgnoreEnd
		}

		// user-spezifische joker.
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$res1 = $wpdb->get_results( $wpdb->prepare( 'select userid, jokerlist from %i order by userid;', $cs_users ) );

		$jil = array();
		foreach ( $res1 as $k => $v ) {
			// joker in array packen.
			$jil = explode( ',', $v->jokerlist );
			// nur die maximal erlaubte jokermenge zulassen.
			$jil = array_slice( $jil, 0, $cs_joker_player );

			// string für update zusammensetzen.
			$jml = '';
			foreach ( $jil as $jm ) {
				if ( $jm > 0 ) {
					$jml .= $jm . ',';
				}
			}
			// letztes Komma abschneiden.
			$jml = substr( $jml, 0, -1 );

			if ( strlen( trim( $jml ) ) > 0 ) {
				// update points.
				// @codingStandardsIgnoreStart
				$res2 = $wpdb->query(
					$wpdb->prepare(
						'update %i b inner join %i a on a.mid=b.mid 
						and b.mid IN ( %s ) and b.userid = %d and a.result1 <> -1 and a.result2 <> -1 
						set b.points=b.points * 2 
						where b.result1>-1 and b.result2>-1;',
						$cs_tipp,
						$cs_match,
						$jml,
						$v->userid
					)
				);
				// @codingStandardsIgnoreEnd
			}
		}

		// champion tipp addieren auf finalspiel.
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$ftime      = $wpdb->get_row( $wpdb->prepare( "select max(matchtime) as mtime from %i where round='F';", $cs_match ) );
		$fmatchtime = $ftime->mtime;

		// @codingStandardsIgnoreStart
		$res = $wpdb->get_row(
			$wpdb->prepare(
				"select case winner when 1 then tid1 when 2 then tid2 end as winner 
				from %i a 
				where a.round='F' 
				and a.winner <> -1 
				and matchtime = %s;",
				$cs_match,
				$fmatchtime
			)
		);
		// @codingStandardsIgnoreEnd

		$champion = ( isset( $res ) ? $res->winner : -999 );

		// falls turniergewinner uebersteuert wurde nehmen wir den.
		$cs_final_winner = get_option( 'cs_final_winner' );
		if ( -1 != $cs_final_winner ) {
			$champion = $cs_final_winner;
		}

		if ( $champion ) {
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$res = $wpdb->get_results( $wpdb->prepare( 'select userid from %i where champion=%d;', $cs_users, $champion ) );

			foreach ( $res as $r ) {
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$wpdb->query( $wpdb->prepare( 'update %i set points=points + %d where userid=%d limit 1;', $cs_tipp, $cs_pts_champ, $r->userid ) );
			}
		}

	}
}


if ( ! function_exists( 'cs_get_ranking' ) ) {
	/**
	 * Funktion um das aktuelle Ranking zu ermitteln.
	 *
	 * @param string $tippgroup Tippgruppe auf die gefiltert werden soll, wenn leer werden alle Tipper einbezogen.
	 */
	function cs_get_ranking( $tippgroup = '' ) {
		include 'globals.php';
		global $wpdb;

		// select fuer ranking der tipper.
		if ( '' != $tippgroup ) {
			// @codingStandardsIgnoreStart
			$res = $wpdb->get_results(
				$wpdb->prepare(
					"select b.user_nicename, 
					case when b.display_name != '' then b.display_name when b.display_name is null then b.user_login else b.user_login end as vdisplay_name, 
					a.userid, sum(a.points)+c.penalty as points, c.rang as oldrank, c.champion as champion, e.name as teamname 
					from %i a inner join %i b on a.userid=b.ID 
					inner join %i c on a.userid=c.userid 
					inner join %i d on a.mid = d.mid 
					left outer join %i  e on c.champion = e.tid
					where points <> -1 and c.tippgroup=%s
					group by vdisplay_name, a.userid order by points DESC;",
					$cs_tipp,
					$wp_users,
					$cs_users,
					$cs_match,
					$cs_team,
					$tippgroup
				)
			);
			// @codingStandardsIgnoreEnd
		} else {
			// @codingStandardsIgnoreStart
			$res = $wpdb->get_results(
				$wpdb->prepare(
					"select b.user_nicename, 
					case when b.display_name != '' then b.display_name when b.display_name is null then b.user_login else b.user_login end as vdisplay_name, 
					a.userid, sum(a.points)+c.penalty as points, c.rang as oldrank, c.champion as champion, e.name as teamname 
					from %i a inner join %i b on a.userid=b.ID 
					inner join %i c on a.userid=c.userid 
					inner join %i d on a.mid = d.mid 
					left outer join %i  e on c.champion = e.tid 
					where points <> -1 
					group by vdisplay_name, a.userid order by points DESC;",
					$cs_tipp,
					$wp_users,
					$cs_users,
					$cs_match,
					$cs_team
				)
			);
			// @codingStandardsIgnoreEnd
		}

		return $res;
	}
}



if ( ! function_exists( 'cs_get_tippgroup_ranking' ) ) {
	/**
	 * Calc ranking for tippgroups.
	 */
	function cs_get_tippgroup_ranking() {
		include 'globals.php';
		global $wpdb;

		// get sort criteria.
		$sortby = 'points';
		if ( get_option( 'cs_stats8_sort_average' ) ) {
			$sortby = 'average';
		}
		// @codingStandardsIgnoreStart
        $res = $wpdb->get_results(
			$wpdb->prepare(
                'select e.tgid, e.name, count(a.userid) as numusers, sum(a.points)+c.penalty as points, (sum(a.points)+c.penalty)/count(a.userid) as average, g.anz as members
                from %i a inner join %i c on a.userid=c.userid 
                inner join %i e on c.tippgroup=e.tgid 
                inner join ( select tippgroup as tg, count(*) as anz from %i where tippgroup >= 0 group by tippgroup ) as g on e.tgid = g.tg
                where points <> -1 
                group by e.tgid, e.name 
                order by %i DESC',
                $cs_tipp,
				$cs_users,
				$cs_tippgroup,
				$cs_users,
                $sortby
             )
		);
		// @codingStandardsIgnoreEnd

		return $res;
	}
}



if ( ! function_exists( 'cs_get_team_clification' ) ) {
	/**
	 * Liefert die platzierung der gruppe groupid und davon die ersten
	 * count plaetze zurueck. ist groupid nicht angegeben werden alle gruppen
	 * zurueck geliefert. ist count  = 0 werden alle teams zuruckgegegben
	 *
	 * @param string  $groupid the ID of the group to filter.
	 * @param integer $count number fo teams for the ranking.
	 */
	function cs_get_team_clification( $groupid = '', $count = 0 ) {
		include 'globals.php';
		global $wpdb;

		// turniermodus lesen.
		$cs_modus = get_option( 'cs_modus' );
		// punktvergabe fuer match einlesen.
		$cs_pts_winner = get_option( 'cs_pts_winner' );
		$cs_pts_looser = get_option( 'cs_pts_looser' );
		$cs_pts_deuce  = get_option( 'cs_pts_deuce' );

		// @codingStandardsIgnoreStart
		$wpdb->query(
			$wpdb->prepare(
				"create table if not exists cs_tt
					select groupid,name,tid,icon,qualified, sum(result1) as tore, sum(result2) as gegentore, 
					sum( case winner when 0 then %d when 1 then %d else %d end) as points
					from %i inner join %i on tid=tid1
					where winner<>-1 and tid1<>0 and round='V'
					group by groupid,name,icon,qualified
				UNION ALL
					select groupid,name,tid,icon,qualified, sum(result2) as tore, sum(result1) as gegentore, 
					sum( case winner when 0 then %d when 2 then %d else %d end) as points
					from %i inner join %i on tid=tid2
					where winner <>-1 and tid2<>0 and round='V'
					group by groupid,name,icon,qualified
				UNION ALL
					select distinct groupid,name,tid,icon,qualified, 0 as tore,0 as gegentore, 0 as points
					from %i inner join %i on tid=tid1
					where winner =-1 and tid1<>0 and round ='V'
				UNION ALL
					select distinct groupid,name,tid,icon, qualified, 0 as tore,0 as gegentore, 0 as points
					from %i inner join %i on tid=tid2
					where winner =-1 and tid2<>0 and round='V'
				UNION ALL
					select distinct groupid,name,tid,icon, qualified, 0 as tore,0 as gegentore, -1*penalty as points
					from %i where penalty <> 0;",
				$cs_pts_deuce,
				$cs_pts_winner,
				$cs_pts_looser,
				$cs_match,
				$cs_team,
				$cs_pts_deuce,
				$cs_pts_winner,
				$cs_pts_looser,
				$cs_match,
				$cs_team,
				$cs_match,
				$cs_team,
				$cs_match,
				$cs_team,
				$cs_team
			)
		);
		// @codingStandardsIgnoreEnd

		$sql1 = '%';
		if ( '' != $groupid ) {
			$sql1 = $groupid;
		}

		$sql2 = '9999999';
		if ( 0 != $count ) {
			$sql2 = $count;
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				'select groupid, name,tid,icon,qualified, sum(tore) as store,sum(gegentore) as sgegentore, 
				sum(points) as spoints, (sum(tore)-sum(gegentore)) as tdiff 
				from cs_tt where groupid like %s
				group by groupid,name,icon order by groupid,qualified,spoints DESC,tdiff DESC, store DESC limit 0, %d;',
				$sql1,
				$sql2
			)
		);

		$wpdb->query( 'drop table cs_tt;' );

		// erweiterung fuer bundesligamodus.
		if ( 2 == $cs_modus ) {

			$points1 = 0;
			$diff1   = 0;
			$tore1   = 0;
			$tid1    = 0;
			$points2 = 0;
			$diff2   = 0;
			$tore2   = 0;
			$tid2    = 0;

			foreach ( $results as $key => $r ) {
				// hole werte des aktuellen teams.
				$points1 = $r->spoints;
				$diff1   = $r->tdiff;
				$tore1   = $r->store;
				$tid1    = $r->tid;

				// vergleiche mit vorherigem team.
				if ( $points1 == $points2 && $diff1 == $diff2 && $tore1 == $tore2 ) {
					// ermittle besseres team im direkten vergleich.
					$erstes_team = cs_compare_direct( $tid1, $tid2 );
				}

				if ( 2 == $erstes_team ) {
					// plaetze tauschen.
					$temp                = $results[ $key ];
					$results[ $key ]     = $results[ $key - 1 ];
					$results[ $key - 1 ] = $temp;
				}
				// vergleichswerte aktualisieren.
				$points2 = $points1;
				$diff2   = $diff1;
				$tore2   = $tore1;
				$tid2    = $tid1;
			}
		}
		return $results;
	}
}



if ( ! function_exists( 'cs_compare_direct' ) ) {
	/**
	 * Vergleicht zwei teams im direkten vergleich gemaess bundesliga reglement
	 * zuerst das gesamtergebnis direkter vergleich, dann die auswaertstore im direkten
	 * vergleich und dann die auswaertstore im gesamten turnier
	 * liefert 1 zurueck wenn team1 besser war und 2 wenn team2 besser war
	 * und 0 wenn der bessere nicht zu ermitteln war
	 *
	 * @param integer $team1 Id von Team1.
	 * @param integer $team2 Id von Team2.
	 */
	function cs_compare_direct( $team1, $team2 ) {
		include 'globals.php';
		global $wpdb;

		$tore1  = 0;
		$tore2  = 0;
		$atore1 = 0;
		$atore2 = 0;
		$winner = -1;

		$res = $wpdb->get_results(
			$wpdb->prepare(
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				'select * from %i where (tid1=%d and tid2=%d) or (tid1=%d and tid2=%d) and winner <> -1',
				$cs_match,
				$team1,
				$team2,
				$team2,
				$team1
			)
		);

		// summiere die erzielten tore beider mannschaften auf.
		foreach ( $res as $r ) {
			if ( $r->tid1 == $team1 ) {
				$tore1  = $tore1 + $r->result1;
				$tore2  = $tore2 + $r->result2;
				$atore2 = $atore2 + $r->result2;
			} else {
				$tore2  = $tore2 + $r->result1;
				$tore1  = $tore1 + $r->result2;
				$atore1 = $atore1 + $r->result2;
			}
		}

		// ermittle den sieger im direkten vergleich
		// erst gesamtergebnis, dann auswaertstore im direkten vergleich.
		if ( $tore1 > $tore2 ) {
			$winner = 1;
		} elseif ( $tore2 > $tore1 ) {
			$winner = 2;
		} elseif ( $atore1 > $atore2 ) {
			$winner = 1;
		} elseif ( $atore2 > $atore1 ) {
			$winner = 2;
		} else {
			// jetzt geht es um die auswaertstore im ganzen turnier
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$res       = $wpdb->get_row( $wpdb->prepare( 'select sum(result2) as atore from %i where tid2=%d and winner<>-1', $cs_match, $team1 ) );
			$atoresum1 = $res->atore;

			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$res       = $wpdb->get_row( $wpdb->prepare( 'select sum(result2) as atore from %i where tid2=%d and winner<>-1', $cs_match, $team2 ) );
			$atoresum2 = $res->atore;

			if ( $atoresum1 > $atoresum2 ) {
				$winner = 1;
			} elseif ( $atoresum2 > $atoresum1 ) {
				$winner = 2;
			} else {
				$winner = 0;
			}
		}

		return $winner;
	}
}


if ( ! function_exists( 'cs_team2text' ) ) {
	/**
	 * Konvertiert einen teamcode der finalrunde in einen lesbaren Text.
	 *
	 * @param string $teamcode zu kovertierender Teamcode.
	 */
	function cs_team2text( $teamcode ) {

		if ( '#' != substr( $teamcode, 0, 1 ) ) {
			return $teamcode;
		}

		$code1 = substr( $teamcode, 1, 1 );

		if ( 'W' == $code1 || 'V' == $code1 ) {
			$erg = ( 'W' == $code1 ? __( 'Winner', 'wp-championship' ) : __( 'Looser', 'wp-championship' ) ) . ', ' . __( 'Matchno.', 'wp-championship' ) . substr( $teamcode, 2 );
		} else {
			$erg = __( 'Group ', 'wp-championship' ) . $code1 . ', ' . substr( $teamcode, 2 ) . '. ' . __( 'Rank', 'wp-championship' );
		}

		return $erg;
	}
}




if ( ! function_exists( 'cs_update_finals' ) ) {
	/**
	 * Aktualisiere die team eintraege der finalrundenspiele.
	 */
	function cs_update_finals() {
		include 'globals.php';
		global $wpdb;

		// Pseudo ids wieder aktivieren = alle Teams in finalrunde zuruecksetzen.
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$wpdb->query( $wpdb->prepare( "update %i set tid1=ptid1, tid2=ptid2 where round='F';", $cs_match ) );

		// ermittle fertige gruppen mit platzierung und teamcode finalrunde.

		// ermittle fertige gruppen.
		// @codingStandardsIgnoreStart
		$wpdb->query(
			$wpdb->prepare(
				"create table if not exists cs_tt1 
				select distinct groupid
				from %i a inner join %i b 
				on a.tid = b.tid1 
				where round = 'V' and winner=-1;",
				$cs_team,
				$cs_match
			)
		);

		$res0 = $wpdb->get_results(
			$wpdb->prepare(
				"select distinct a.groupid 
				from %i a left outer join cs_tt1 t2 on a.groupid = t2.groupid 
				where t2.groupid is NULL 
				and a.groupid <> ''",
				$cs_team
			)
		);
		// @codingStandardsIgnoreEnd

		$wpdb->query( 'drop table cs_tt1;' );

		// aktualisiere daten fuer fertige gruppen.
		foreach ( $res0 as $res ) {

			// hole platzierung der fertigen gruppe.
			$res_group = cs_get_team_clification( $res->groupid, get_option( 'cs_group_teams' ) );

			$no = 1;
			// tausche die platzhalter gegen die richtigen platzierten aus.
			foreach ( $res_group as $qt ) {
				// teamcode im pseudo tabelleneintrag.
				$tcode = '#' . $qt->groupid . $no;

				// tid des pseudoeintrages.
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$dtid = $wpdb->get_row( $wpdb->prepare( 'select tid from %i where name=%s;', $cs_team, $tcode ) );

				// alte und neue teamid.
				if ( isset( $dtid->tid ) ) {
					$oldtid = $dtid->tid;
					$newtid = $qt->tid;

					// austausch der ids in den match daten.
					// The placeholder ignores can be removed when %i is supported by WPCS.
					// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
					$wpdb->query( $wpdb->prepare( "update %i set tid1=%d where round='F' and tid1=%d;", $cs_match, $newtid, $oldtid ) );
					// The placeholder ignores can be removed when %i is supported by WPCS.
					// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
					$wpdb->query( $wpdb->prepare( "update %i set tid2=%d where round='F' and tid2=%d;", $cs_match, $newtid, $oldtid ) );
				}

				// platzierung erhoehen.
				++$no;
			}
		}

		// aktualisiere daten fuer k.o.-runde
		// selektiere noch zu ersetzende pseudo teams.
		// @codingStandardsIgnoreStart
		$wpdb->query(
			$wpdb->prepare(
				"create table if not exists cs_tt2
				SELECT a.mid,'1' as tnr,a.tid1 as tid ,
				substring( b.name, 2,1 ) as wl,substring(b.name,3) as wlmid,a.matchtime
				FROM %i a INNER JOIN %i b ON a.tid1 = b.tid
				WHERE a.round = 'F' AND (b.name LIKE %s or b.name like %s)
				UNION
				SELECT a.mid,'2' as tnr,a.tid2 as tid,
				substring( b.name, 2,1 ) as wl, substring(b.name,3) as wlmid,a.matchtime
				FROM %i a INNER JOIN %i b ON a.tid2 = b.tid
				WHERE a.round = 'F' AND (b.name LIKE %s or b.name like %s);",
				$cs_match,
				$cs_team,
				'#W%',
				'#V%',
				$cs_match,
				$cs_team,
				'#W%',
				'#V%'
			)
		);
		// @codingStandardsIgnoreEnd

		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$res0 = $wpdb->get_results( 'select * from cs_tt2 order by matchtime;' );
		$wpdb->query( 'drop table cs_tt2;' );

		foreach ( $res0 as $res ) {
			// zugehoeriges spiel suchen.
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$row = $wpdb->get_row( $wpdb->prepare( 'select * from %i where winner <> -1 and mid=%d;', $cs_match, $res->wlmid ) );

			// nur updaten wenn ergebnis vorliegt.
			if ( ! empty( $row ) ) {
				// ermittle einzutragendes team.
				if ( ( 'W' == $res->wl && 1 == $row->winner ) || ( 'V' == $res->wl && 2 == $row->winner ) ) {
					$newtid = $row->tid1;
				}
				if ( ( 'W' == $res->wl && 2 == $row->winner ) || ( 'V' == $res->wl && 1 == $row->winner ) ) {
					$newtid = $row->tid2;
				}

				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$wpdb->query( $wpdb->prepare( 'update %i set %i=%d where mid=%d', $cs_match, 'tid' . $res->tnr, $newtid, $res->mid ) );
			}
		}
	}
}




if ( ! function_exists( 'cs_mailservice' ) ) {
	/**
	 * Verschickt an die abonnenten das aktuelle ranking.
	 */
	function cs_mailservice() {
		include 'globals.php';
		global $wpdb;

		// email adressen holen.
		// @codingStandardsIgnoreStart
		$res_email = $wpdb->get_results(
			$wpdb->prepare(
				"select user_nicename, case when display_name != '' then display_name when display_name is null then user_login else user_login end 
				as vdisplay_name, user_email, mailformat 
				from %i inner join %i on ID=userid 
				where mailservice=1;",
				$wp_users,
				$cs_users
			)
		);
		// @codingStandardsIgnoreStart

		// ausgabe des aktuellen punktestandes und des ranges.
		$rank = cs_get_ranking();
		$i = 0;

		// Nachrichten Header für HTML und Text bauen.
		$msg_html  = '<h2>' . __( 'Guessing game mail service', 'wp-championship' ) . "</h2>\n";
		$msg_html .= '<h2>' . __( 'Current score', 'wp-championship' ) . "</h2>\n";
		$msg_html .= "<table border='1' width='500px' cellpadding='0'><thead><tr>\n";
		$msg_html .= '<th scope="col" style="text-align: center">' . __( 'Rank', 'wp-championship' ) . '</th>' . "\n";
		$msg_html .= '<th scope="col" style="text-align: center">' . __( 'Player', 'wp-championship' ) . '</th>' . "\n";
		$msg_html .= '<th width="20">' . __( 'Score', 'wp-championship' ) . '</th>' . "\n";
		if ( get_option( 'cs_rank_trend' ) ) {
			$msg_html .= '<th width="20">' . __( 'Tendency', 'wp-championship' ) . '</th>';
		}
		$msg_html .= '</tr></thead>';

		$msg_text  = __( 'Guessing game mail service', 'wp-championship' ) . "\r\n\r\n";
		$msg_text .= __( 'Current score', 'wp-championship' ) . "\r\n\r\n";
		$msg_text .= str_pad( __( 'Rank', 'wp-championship' ), 10 ) . str_pad( __( 'Player', 'wp-championship' ), 20 ) . str_pad( __( 'Score', 'wp-championship' ), 15 );
		if ( get_option( 'cs_rank_trend' ) ) {
			$msg_text .= __( 'Tendency', 'wp-championship' );
		}
		$msg_text .= "\r\n";
		$msg_text .= "=============================================================\r\n";

		$pointsbefore = -1;
		$i = 0;
		$j = 1;

		foreach ( $rank as $row ) {
			// platzierung erhoehen, wenn punkte sich veraendern.
			if ( $row->points != $pointsbefore ) {
				$i += $j;
				$j = 1;
			} else {
				++$j;
			}

			if ( $i < $row->oldrank ) {
				$trend_html = '&uArr;';
				$trend_text = __( 'rising', 'wp-championship' );
			} elseif ( $i > $row->oldrank ) {
				$trend_html = '&dArr;';
				$trend_text = __( 'falling', 'wp-championship' );
			} else {
				$trend_html = '&rArr;';
				$trend_text = __( 'constant', 'wp-championship' );
			}

			$msg_html .= "<tr><td align='center'>$i</td><td align='center'>" . $row->vdisplay_name . "</td><td align='center'>" . $row->points . '</td>';
			if ( get_option( 'cs_rank_trend' ) ) {
				$msg_html .= "<td align='center'>$trend_html</td>";
			}
			$msg_html .= '</tr>';

			$msg_text .= str_pad( $i, 10 ) . str_pad( $row->vdisplay_name, 20 ) . str_pad( $row->points, 15 );
			if ( get_option( 'cs_rank_trend' ) ) {
				$msg_text .= str_pad( "$trend_text", 15 );
			}
			$msg_text .= "\r\n";

			// gruppenwechsel versorgen.
			$pointsbefore = $row->points;
		}

		// Nachrichten footer.
		$msg_html .= '</table>' . "\n<p>&nbsp;";
		$msg_text .= "=============================================================\r\n";

		foreach ( $res_email as $row ) {
			// mail senden.
			if ( 0 == $row->mailformat ) {       // user wants HTML mailformat.
				$msg = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>wp-championship</title></head><body>' . $msg_html . '</body></html>';
				add_filter(
					'wp_mail_content_type',
					function () {
						return 'text/html';
					}
				);
			} else {
				$msg = $msg_text;
				add_filter(
					'wp_mail_content_type',
					function() {
						return 'text/plain';
					}
				);
			}

			$stat = wp_mail( $row->user_email, 'Update Tippspiel', $msg );

			if ( $stat ) {
				echo esc_attr( __( 'the email to ', 'wp-championship' ) . $row->user_email . __( ' was sent.', 'wp-championship' ) );
			} else {
				echo esc_attr( __( 'the email to ', 'wp-championship' ) . $row->user_email . __( ' could not be sent', 'wp-championship' ) );
			}
			echo "<br />\n";
		}
	}
}//make it pluggable


if ( ! function_exists( 'cs_mailservice_tippgroup' ) ) {
	/**
	 * Verschickt an die abonnenten das aktuelle ranking für tippgruppen.
	 */
	function cs_mailservice_tippgroup() {
		include( 'globals.php' );
		global $wpdb;

		// email adressen holen.
		// @codingStandardsIgnoreStart
		$res_email = $wpdb->get_results(
			$wpdb->prepare(
				"select user_nicename, case when display_name != '' then display_name when display_name is null then user_login else user_login end 
				as vdisplay_name, user_email, mailformat 
				from %i inner join %i on ID=userid 
				where mailservice=1 and tippgroup > 0;",
				$wp_users,
				$cs_users
			)
		);
		// @codingStandardsIgnoreEnd

		// ausgabe des aktuellen punktestandes und des ranges.
		$rank = cs_get_tippgroup_ranking();
		$i    = 0;

		// Nachrichtenheader zusammen bauen für HTML und Text.
		$msg_html  = '<h2>' . __( 'Guessing game mail service', 'wp-championship' ) . "</h2>\n";
		$msg_html .= '<h2>' . __( 'Current score of the tip groups', 'wp-championship' ) . "</h2>\n";
		$msg_html .= "<table border='1' width='500px' cellpadding='0'><thead><tr>\n";
		$msg_html .= '<th scope="col" style="text-align: center">' . __( 'Rank', 'wp-championship' ) . '</th>' . "\n";
		$msg_html .= '<th scope="col" style="text-align: center">' . __( 'Tip Group', 'wp-championship' ) . '</th>' . "\n";
		$msg_html .= '<th width="20">' . __( 'Score', 'wp-championship' ) . '</th>' . "\n";
		$msg_html .= '<th width="20">' . __( 'Average', 'wp-championship' ) . '</th>';
		$msg_html .= '</tr></thead>';

		$msg_text  = __( 'Guessing game mail service', 'wp-championship' ) . "\r\n\r\n";
		$msg_text .= __( 'Current score of the tip groups', 'wp-championship' ) . "\r\n\r\n";
		$msg_text .= str_pad( __( 'Rank', 'wp-championship' ), 10 ) . str_pad( __( 'Tip Group', 'wp-championship' ), 20 ) . str_pad( __( 'Score', 'wp-championship' ), 15 ) . str_pad( __( 'Average', 'wp-championship' ), 15 ) . "\r\n";
		$msg_text .= "==================================================================\r\n";

		$pointsbefore = -1;
		$i            = 0;
		$j            = 1;
		foreach ( $rank as $row ) {
			// platzierung erhoehen, wenn punkte sich veraendern.
			if ( $row->points != $pointsbefore ) {
				$i += $j;
				$j  = 1;
			} else {
				++$j;
			}

			$msg_html .= "<tr><td align='center'>$i</td><td align='center'>" . $row->name . "</td><td align='center'>" . $row->points . '</td>';
			$msg_html .= "<td align='center'>$row->average</td>";
			$msg_html .= '</tr>';

			$msg_text .= str_pad( $i, 10 ) . str_pad( $row->name, 20 ) . str_pad( $row->points, 15 ) . str_pad( $row->average, 15 ) . "\r\n";

			// gruppenwechsel versorgen.
			$pointsbefore = $row->points;
		}
		$msg_html .= '</table>' . "\n<p>&nbsp;";

		foreach ( $res_email as $row ) {
			// mail senden.
			if ( 0 == $row->mailformat ) {
				$msg = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>wp-championship</title></head><body>' . $msg_html . '</body></html>';
				add_filter(
					'wp_mail_content_type',
					function () {
						return 'text/html';
					}
				);
			} else {
				$msg = $msg_text;
				add_filter(
					'wp_mail_content_type',
					function() {
						return 'text/plain';
					}
				);
			}
			$stat = wp_mail( $row->user_email, 'Update Tippspiel Tippgruppen-Ranking', $msg );

			if ( $stat ) {
				echo esc_attr( __( 'the email to ', 'wp-championship' ) . $row->user_email . __( ' was sent.', 'wp-championship' ) );
			} else {
				echo esc_attr( __( 'the email to ', 'wp-championship' ) . $row->user_email . __( ' could not be sent', 'wp-championship' ) );
			}
			echo "<br />\n";
		}
	}
}


if ( ! function_exists( 'cs_mailservice2' ) ) {
	/**
	 * Verschickt an die abonnenten erinnerungen falls noch nicht getippt wurde.
	 */
	function cs_mailservice2() {
		// prüfen ob wir erinnern sollen.
		$cs_reminder = get_option( 'cs_reminder' );
		if ( ! $cs_reminder ) {
			return;
		}

		// globale variable einlesen.
		include 'globals.php';
		global $wpdb;

		// holen der match ids fuer die spiele die noch nicht angefangen haben aber in
		// den nächsten stunden anfangen.
		$cs_reminder_hours = get_option( 'cs_reminder_hours' );
		$now               = current_time( 'timestamp', false );
		$then              = $now + ( $cs_reminder_hours * 3600 );

		$mnow  = gmdate( 'Y-m-d H:i:s', $now );
		$mthen = gmdate( 'Y-m-d H:i:s', $then );

		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$res_mid = $wpdb->get_results( $wpdb->prepare( 'select mid from %i where matchtime > %s and matchtime <= %s;', $cs_match, $mnow, $mthen ) );

		// wenn keine Spiele anstehen, Funktion beenden und auch keine Mails schicken.
		if ( empty( $res_mid ) ) {
			return;
		}

		$mids      = '(';
		$match_msg = '';
		foreach ( $res_mid as $row ) {
			$mids .= $row->mid . ', ';

			// match daten holen und als Nachricht zusammen bauen.
			// @codingStandardsIgnoreStart
			$res_match = $wpdb->get_results(
				$wpdb->prepare(
					'select b.name name1,c.name name2,a.matchtime,a.location 
					from %i a inner join %i b on a.tid1 = b.tid 
					inner join %i c on a.tid2=c.tid 
					where mid=%d;',
					$cs_match,
					$cs_team,
					$cs_team,
					$row->mid
				)
			);
			// @codingStandardsIgnoreEnd

			$match_msg .= $res_match[0]->name1 . ' : ' . $res_match[0]->name2 . ' in ' . $res_match[0]->location . ' startet ' . $res_match[0]->matchtime . "<br /><br/>\n";
		}
		$mids .= '-9999)';

		// holen der userids, die fuer diese match ids noch nicht getippt haben.
		// @codingStandardsIgnoreStart
		$res_user = $wpdb->get_results(
			$wpdb->prepare(
				'select a.userid, b.mid, c.user_email, c.user_nicename, a.mailformat 
				from %i c inner join %i a on a.userid=c.ID 
				left outer join %i b on a.userid = b.userid 
				and mid in %s 
				where (result1 = -1 or result1 is null) 
				order by a.userid, b.mid;',
				$wp_users,
				$cs_users,
				$cs_tipp,
				$mids
			)
		);
		// @codingStandardsIgnoreEnd

		// fuer jeden user mit fehlenden tipp email zusammenstellen und senden.
		$uid     = 0;
		$uid_old = 0;
		foreach ( $res_user as $u ) {
			$uid = $u->userid;

			// Gruppenwechsel nach userid.
			if ( $uid != $uid_old && 0 != $uid_old ) {

				if ( 0 == $u->mailformat ) {
					// mailnachricht zusammen bauen.
					$msg  = '<h2>' . __( 'Guessing game mail service', 'wp-championship' ) . "</h2>\n";
					$msg .= '<h2>' . __( 'Would you like to tip? The following games are starting soon and at least one tip is missing: ', 'wp-championship' ) . "</h2>\n";
					$msg .= $match_msg;
					$msg .= "Viel Glück wünscht der Tippspiel-Admin.\n";

					$msg = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>wp-championship</title></head><body>' . $msg . '</body></html>';
					add_filter(
						'wp_mail_content_type',
						function() {
							return 'text/html';
						}
					);
				} else {
					// mailnachricht zusammen bauen.
					$msg  = __( 'Guessing game mail service', 'wp-championship' ) . "\r\n\r\n";
					$msg .= __( 'Would you like to tip? The following games are starting soon and at least one tip is missing: ', 'wp-championship' ) . "\r\n\r\n";
					$msg .= $match_msg;
					$msg .= "\n\nViel Glück wünscht der Tippspiel-Admin.\r\n";
					add_filter(
						'wp_mail_content_type',
						function() {
							return 'text/plain';
						}
					);
				}

				// mail senden.
				$stat = wp_mail( $u->user_email, 'Update Tippspiel', $msg );

				if ( $stat ) {
					echo esc_attr( __( 'The email reminder to ', 'wp-championship' ) . $u->user_email . __( ' was sent.', 'wp-championship' ) );
				} else {
					echo esc_attr( __( 'The email reminder to ', 'wp-championship' ) . $u->user_email . __( ' could not be sent', 'wp-championship' ) );
				}
				echo '<br />';
			}
			// Grupenwechsel versorgen.
			$uid_old = $uid;
		}
	} // end of function.
}


if ( ! function_exists( 'cs_mailservice3' ) ) {
	/**
	 * Verschickt an die abonnenten die Bestätigungsmail.
	 *
	 * @param integer $userid UserID des Users für den die Mail verschickt werden soll.
	 * @param array   $tipps Array mit den Tipps des Users.
	 */
	function cs_mailservice3( $userid, $tipps ) {
		include 'globals.php';
		global $wpdb;

		// email adressen holen.
		// @codingStandardsIgnoreStart
		$res_email = $wpdb->get_row(
			$wpdb->prepare(
				'select user_nicename, user_email, mailformat 
				from %i inner join %i on ID=userid 
				where mailreceipt=1 
				and userid=%d;',
				$wp_users,
				$cs_users,
				$userid
			)
		);
		// @codingStandardsIgnoreEnd

		// ausgabe des aktuellen punktestandes und des ranges.
		$rank = cs_get_ranking();
		$i    = 0;

		// Nachrichtenheader für HTML und Text Format erstellen.
		$msg_html  = '<h2>' . __( 'Guessing game mail service', 'wp-championship' ) . "</h2>\n";
		$msg_html .= '<h2>' . __( 'Tip confirmation', 'wp-championship' ) . "</h2>\n";
		$msg_html .= "<table border='1' width='500px' cellpadding='0'><thead><tr>\n";
		$msg_html .= '<th scope="col" style="text-align: center">' . __( 'Match', 'wp-championship' ) . '</th>' . "\n";
		$msg_html .= '<th scope="col" style="text-align: center">' . __( 'Tip', 'wp-championship' ) . '</th>' . "\n";
		$msg_html .= '<th scope="col" style="text-align: center">' . __( 'Date / time', 'wp-championship' ) . '</th>' . "\n";
		$msg_html .= '</tr></thead>' . "\n";

		$msg_text  = __( 'Guessing game mail service', 'wp-championship' ) . "\r\n\r\n";
		$msg_text .= __( 'Tip confirmation', 'wp-championship' ) . "\r\n\r\n";
		$msg_text .= str_pad( __( 'Match', 'wp-championship' ), 30 );
		$msg_text .= str_pad( __( 'Tip', 'wp-championship' ), 10 );
		$msg_text .= str_pad( __( 'Date / time', 'wp-championship' ), 20 ) . "\r\n";
		$msg_text .= "===================================================================\r\n";

		foreach ( $tipps as $key => $val ) {
			// @codingStandardsIgnoreStart
			$resm = $wpdb->get_row(
				$wpdb->prepare(
					'select b.name as name1, c.name as name2, a.matchtime as matchtime 
					from %i a inner join %i b on a.tid1=b.tid 
					inner join %i c on a.tid2=c.tid 
					where mid=%d;',
					$cs_match,
					$cs_team,
					$cs_team,
					$key
				)
			);
			// @codingStandardsIgnoreEnd

			$t1 = $resm->name1;
			$t2 = $resm->name2;
			$t3 = $resm->matchtime;

			if ( substr( $t1, 0, 1 ) == '#' ) {
				$t1 = __( 'n/a', 'wp-championship' );
			}
			if ( substr( $t2, 0, 1 ) == '#' ) {
				$t2 = __( 'n/a', 'wp-championship' );
			}

			$msg_html .= "<tr><td align='center'>$t1 : $t2</td><td align='center'>" . ( '-1:-1' == $val ? ' -:- ' : $val ) . "</td><td align='center'>" . $t3 . "</td></tr>\n";
			$msg_text .= cs_mb_str_pad( trim( "$t1 : $t2" ), 30 ) . str_pad( ( '-1:-1' == $val ? '-:-' : $val ), 10 ) . str_pad( $t3, 20 ) . "\r\n";
		}
		$msg_html .= '</table>' . "\n<p>&nbsp;";

		// mail senden.
		if ( 0 == $res_email->mailformat ) {
			$msg = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>wp-championship</title></head><body>' . $msg_html . '</body></html>';
			add_filter(
				'wp_mail_content_type',
				function() {
					return 'text/html';
				}
			);
		} else {
			$msg = $msg_text;
			add_filter(
				'wp_mail_content_type',
				function() {
					return 'text/plain';
				}
			);
		}

		$stat = wp_mail( $res_email->user_email, 'Update Tippspiel', $msg );

		if ( $stat ) {
			echo esc_attr( __( 'The mail confirmation to ', 'wp-championship' ) . $res_email->user_email . __( ' was sent.', 'wp-championship' ) );
		} else {
			echo esc_attr( __( 'The mail confirmation to ', 'wp-championship' ) . $res_email->user_email . __( ' could not be sent', 'wp-championship' ) );
		}
		echo "<br />\n";
	}
}



if ( ! function_exists( 'cs_get_cswinner' ) ) {
	/**
	 * Ermittelt den Gewinner des Tuniers und gibt ihn zurück.
	 */
	function cs_get_cswinner() {
		include 'globals.php';
		global $wpdb;
		$wteamname = '';

		// tunier beendet?
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$row = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where winner=-1;', $cs_match ) );
		if ( 0 == $row->anz ) {
			if ( 1 == get_option( 'cs_modus' ) ) {

				// selektiere winner team als gewinner des letzten spiels = finale.
				// @codingStandardsIgnoreStart
				$row = $wpdb->get_row(
					$wpdb->prepare(
						"select case winner when 1 then tid1 when 2 then tid2 else 0 end as wteam 
						from %i where round='F' order by matchtime desc limit 0,1;",
						$cs_match
					)
				);
				// @codingStandardsIgnoreEnd
				$wteam = $row->wteam;

				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$row       = $wpdb->get_row( $wpdb->prepare( 'select name from %i where tid=%d', $cs_team, $wteam ) );
				$wteamname = $row->name;
			} else {
				$r         = cs_get_team_clification( '', 1 );
				$wteamname = $r[0]->name;
			}
		}

		// falls uebersteuert wurde lesen wir den Wert aus.
		$cs_final_winner = get_option( 'cs_final_winner' );
		if ( -1 != $cs_final_winner ) {
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$row       = $wpdb->get_row( $wpdb->prepare( 'select name from %i where tid=%d', $cs_team, $cs_final_winner ) );
			$wteamname = $row->name;
		}

		return $wteamname;
	}
}


if ( ! function_exists( 'cs_get_team_stats' ) ) {
	/**
	 * Liefert die spielstatistiken fuer ein team zurueck.
	 * ermittelt werden:
	 * anzahl der spiele, anzahl siege, anzahl unentschieden, anzahl niederlagen
	 *
	 * @param integer $teamid id of the team.
	 */
	function cs_get_team_stats( $teamid ) {
		include 'globals.php';
		global $wpdb;

		// anzahl spiele.
		$res1 = $wpdb->get_row(
			$wpdb->prepare(
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				"select count(*) as anz1 from %i where round='V' and (tid1=%d or tid2=%d) and winner <> -1;",
				$cs_match,
				$teamid,
				$teamid
			)
		);

		// anzahl siege.
		$res2 = $wpdb->get_row(
			$wpdb->prepare(
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				"select count(*) as anz2 from %i where round='V' and (( tid1=%d and winner=1) or (tid2=%d and winner = 2));",
				$cs_match,
				$teamid,
				$teamid
			)
		);

		// anzahl unentschieden.
		$res3 = $wpdb->get_row(
			$wpdb->prepare(
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				"select count(*) as anz3 from %i where round='V' and ( tid1=%d or tid2=%d ) and winner = 0;",
				$cs_match,
				$teamid,
				$teamid
			)
		);

		// anzahl niederlagen.
		$res4 = $wpdb->get_row(
			$wpdb->prepare(
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				"select count(*) as anz4 from %i where round='V' and (( tid1=%d and winner=2) or (tid2=%d and winner = 1));",
				$cs_match,
				$teamid,
				$teamid
			)
		);

		return array(
			'spiele'        => $res1->anz1,
			'siege'         => $res2->anz2,
			'unentschieden' => $res3->anz3,
			'niederlagen'   => $res4->anz4,
		);
	}
}



if ( ! function_exists( 'cs_get_float_js' ) ) {
	/**
	 * Returns the javascript to add the floating link to the tip page.
	 */
	function cs_get_float_js() {
		$js = <<<EOL
	<script type="text/javascript">
	var name = "#WPCSfloatMenu";
	var menuYloc = null;
		
	jQuery(document).ready(function(){
		menuYloc = parseInt(jQuery(name).css("top").substring(0,jQuery(name).css("top").indexOf("px")))
		    jQuery(window).scroll(function () { 
			    offset = menuYloc+jQuery(document).scrollTop()+"px";
			    jQuery(name).animate({top:offset},{duration:500,queue:false});
			});
	    }); 
	</script>
EOL;

		return $js;
	}
}


if ( ! function_exists( 'cs_store_current_ranking' ) ) {
	/**
	 * Speichert die aktuelle platzierung alle mitspieler in der tabelle cs_users.
	 * in der spalte rang.
	 */
	function cs_store_current_ranking() {
		include 'globals.php';
		global $wpdb;

		$pointsbefore = -1;
		$i            = 0;
		$j            = 1;
		// hole aktuelle mitspielerplatzierung.
		$rank = cs_get_ranking();

		foreach ( $rank as $row ) {
			// platzierung erhoehen, wenn punkte sich veraendern.
			if ( $row->points != $pointsbefore ) {
				$i += $j;
				$j  = 1;
			} else {
				++$j;
			}

			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->query( $wpdb->prepare( 'update %i set rang=%d where userid=%d;', $cs_users, $i, $row->userid ) );

			// gruppenwechsel versorgen.
			$pointsbefore = $row->points;
		}
	}
}


if ( ! function_exists( 'cs_add_user' ) ) {
	/**
	 * Fuegt den user mit userid id dem tippspiel hinzu.
	 *
	 * @param integer $id id des anzulegenden Users.
	 */
	function cs_add_user( $id ) {
		include 'globals.php';
		global $wpdb;

		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where userid=%d;', $cs_users, $id ) );

		if ( 0 == $results->anz ) {
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->query( $wpdb->prepare( "insert into %i values ( %d, 0, 0, 0, 0, -1, '1900-01-01 00:00:00', -1, '', 0, 0, 0, '' );", $cs_users, $id ) );
		}
	}
}

if ( ! function_exists( 'cs_is_tippspiel_user' ) ) {
	/**
	 * Prüft ob der USer userid id als tippspieluser anglegt ist.
	 *
	 * @param integer $id id des Users.
	 */
	function cs_is_tippspiel_user( $id ) {
		include 'globals.php';
		global $wpdb;

		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where userid=%d;', $cs_users, $id ) );

		$erg = true;
		if ( 0 == $results->anz ) {
			$erg = false;
		}
		return $erg;
	}
}

if ( ! function_exists( 'cs_get_team_matches' ) ) {
	/**
	 * Liefert alle spiele eines teams zurück, die bereits ein ergebnis haben
	 * ermittelt werden fuer jedes spiel: team1, team2,ergebnis
	 *
	 * @param integer $teamid Teamid to get the list for.
	 */
	function cs_get_team_matches( $teamid ) {
		include 'globals.php';
		global $wpdb;

		// spiele ermitteln.
		// @codingStandardsIgnoreStart
		$res1 = $wpdb->get_results(
			$wpdb->prepare(
				'select  date_format( a.matchtime, %s ) as d, b.name as name1,b.shortname as sname1, b.icon as icon1, 
				a.result1 as res1,a.result2 as res2, c.name as name2,c.shortname as sname2, c.icon as icon2 
				from %i a inner join %i b on b.tid=a.tid1 
				inner join %i c on a.tid2=c.tid 
				where (tid1=%d or tid2=%d) 
				and winner <> -1 
				order by date_format( a.matchtime, %s ) desc;',
				'%d.%m.%y',
				$cs_match,
				$cs_team,
				$cs_team,
				$teamid,
				$teamid,
				'%d.%m.%y'
			)
		);
		// @codingStandardsIgnoreEnd

		$erg = array();
		foreach ( $res1 as $m ) {
			$erg[ $m->d ] = array(
				'date'   => $m->d,
				'name1'  => $m->name1,
				'sname1' => $m->sname1,
				'icon1'  => $m->icon1,
				'res1'   => $m->res1,
				'res2'   => $m->res2,
				'name2'  => $m->name2,
				'sname2' => $m->sname2,
				'icon2'  => $m->icon2,
			);
		}

		return $erg;
	}
}


if ( ! function_exists( 'cs_get_team_penalty' ) ) {
	/**
	 * Liefert die strafpunkte je Team zurück
	 * ermittelt wird: Teamname und anzahl Strafpunkte.
	 */
	function cs_get_team_penalty() {
		include 'globals.php';
		global $wpdb;

		// hole penalties.
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$res1 = $wpdb->get_results( $wpdb->prepare( 'select name,penalty from %i where penalty <>0;', $cs_team ) );

		return $res1;
	}
}


if ( ! function_exists( 'wpc_contextual_help' ) ) {
	/**
	 * This function returns the contextual help.
	 */
	function wpc_contextual_help() {

		if ( function_exists( 'load_plugin_textdomain' ) ) {
			load_plugin_textdomain( 'wp-championship', false, basename( dirname( __FILE__ ) ) . '/lang/' );
		}

		$contextual_help  = '<p>';
		$contextual_help .= __( 'If you are looking for instructions or help on wp-championship, please use the following resources. If you are stuck you can always write an email to:', 'wp-championship' );
		$contextual_help .= ' <a href="mailto:support@tuxlog.de">support@tuxlog.de</a>.';
		$contextual_help .= '</p>';

		$contextual_help .= '<ul>';
		$contextual_help .= '<li><a href="http://www.tuxlog.de/wordpress/2013/wp-championship-quickreference-english/" target="_blank">';
		$contextual_help .= __( 'English Manual', 'wp-championship' );
		$contextual_help .= '</a></li>';

		$contextual_help .= '<li><a href="http://www.tuxlog.de/wp-championship-handbuch/" target="_blank">';
		$contextual_help .= __( 'German Manual', 'wp-championship' );
		$contextual_help .= '</a></li>';

		$contextual_help .= '<li><a href="http://wpcdemo.tuxlog.de" target="_blank">';
		$contextual_help .= __( 'Demo-Site', 'wp-championship' );
		$contextual_help .= '</a></li>';

		$contextual_help .= '<li><a href="http://www.wordpress.org/extend/plugins/wp-championship" target="_blank">';
		$contextual_help .= __( 'wp-championship on WordPress.org', 'wp-championship' );
		$contextual_help .= '</a></li>';

		$contextual_help .= '<li><a href="http://www.tuxlog.de/wp-championship/" target="_blank">';
		$contextual_help .= __( 'German wp-championship site', 'wp-championship' );
		$contextual_help .= '</a></li>';

		$contextual_help .= '<li><a href="http://wordpress.org/plugins/wp-championship/changelog/" target="_blank">';
		$contextual_help .= __( 'Changelog', 'wp-championship' );
		$contextual_help .= '</a></li></ul>';

		$contextual_help .= '<p>';
		$contextual_help .= __( 'Links open in a new windows/tab.', 'wp-championship' );
		$contextual_help .= '</p>';

		$screen = get_current_screen();

		// Add my_help_tab if current screen is My Admin Page.
		$screen->add_help_tab(
			array(
				'id'      => 'wpc_help_tab',
				'title'   => __( 'wp-championship Help' ),
				'content' => $contextual_help,
			)
		);
	}
}

/**
 * Pads a string like str_pad but for multibyte characters like german umlauts.
 *
 * @param string  $input String to pad.
 * @param integer $pad_length Length of padding.
 * @param string  $pad_string String to pad with.
 * @param integer $pad_type Type to pad.
 */
function cs_mb_str_pad( $input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT ) {
	$diff = strlen( $input ) - mb_strlen( $input );
	return str_pad( $input, $pad_length + $diff, $pad_string, $pad_type );
}

/**
 * Return an array containgin all allowed HTML tags and attributes
 */
function wpc_allowed_tags() {

	$allowed_atts = array(
		'align'      => array(),
		'class'      => array(),
		'type'       => array(),
		'id'         => array(),
		'dir'        => array(),
		'lang'       => array(),
		'style'      => array(),
		'xml:lang'   => array(),
		'src'        => array(),
		'alt'        => array(),
		'href'       => array(),
		'rel'        => array(),
		'rev'        => array(),
		'target'     => array(),
		'novalidate' => array(),
		'type'       => array(),
		'value'      => array(),
		'name'       => array(),
		'tabindex'   => array(),
		'action'     => array(),
		'method'     => array(),
		'for'        => array(),
		'width'      => array(),
		'height'     => array(),
		'data'       => array(),
		'title'      => array(),
		'maxlength'  => array(),
		'border'     => array(),
		'onclick'    => array(),
		'checked'    => array(),
		'selected'   => array(),
		'cols'       => array(),
		'rows'       => array(),
		'colspan'    => array(),
		'scope'      => array(),
	);

	$allowedposttags['aside']    = $allowed_atts;
	$allowedposttags['section']  = $allowed_atts;
	$allowedposttags['nav']      = $allowed_atts;
	$allowedposttags['form']     = $allowed_atts;
	$allowedposttags['label']    = $allowed_atts;
	$allowedposttags['input']    = $allowed_atts;
	$allowedposttags['textarea'] = $allowed_atts;
	$allowedposttags['select']   = $allowed_atts;
	$allowedposttags['option']   = $allowed_atts;
	$allowedposttags['iframe']   = $allowed_atts;
	$allowedposttags['script']   = $allowed_atts;
	$allowedposttags['style']    = $allowed_atts;
	$allowedposttags['strong']   = $allowed_atts;
	$allowedposttags['small']    = $allowed_atts;
	$allowedposttags['table']    = $allowed_atts;
	$allowedposttags['span']     = $allowed_atts;
	$allowedposttags['abbr']     = $allowed_atts;
	$allowedposttags['code']     = $allowed_atts;
	$allowedposttags['pre']      = $allowed_atts;
	$allowedposttags['div']      = $allowed_atts;
	$allowedposttags['img']      = $allowed_atts;
	$allowedposttags['h1']       = $allowed_atts;
	$allowedposttags['h2']       = $allowed_atts;
	$allowedposttags['h3']       = $allowed_atts;
	$allowedposttags['h4']       = $allowed_atts;
	$allowedposttags['h5']       = $allowed_atts;
	$allowedposttags['h6']       = $allowed_atts;
	$allowedposttags['ol']       = $allowed_atts;
	$allowedposttags['ul']       = $allowed_atts;
	$allowedposttags['li']       = $allowed_atts;
	$allowedposttags['em']       = $allowed_atts;
	$allowedposttags['hr']       = $allowed_atts;
	$allowedposttags['br']       = $allowed_atts;
	$allowedposttags['tr']       = $allowed_atts;
	$allowedposttags['th']       = $allowed_atts;
	$allowedposttags['td']       = $allowed_atts;
	$allowedposttags['p']        = $allowed_atts;
	$allowedposttags['a']        = $allowed_atts;
	$allowedposttags['b']        = $allowed_atts;
	$allowedposttags['i']        = $allowed_atts;
	$allowedposttags['sub']      = $allowed_atts;

	return $allowedposttags;
}
