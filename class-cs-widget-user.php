<?php
/** This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2024  Hans Matzen  (email : webmaster at tuxlog.de)
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

if ( ! class_exists( 'cs_widget_user' ) ) {
	/**
	 * Klasse für das wp-championship widget.
	 */
	class CS_Widget_User extends WP_Widget {
		/**
		 * Konstruktor des cs Widgets.
		 */
		public function __construct() {
			$widget_ops  = array(
				'classname'   => 'cs_widget_user',
				'description' => 'WP Championship User-Widget',
			);
			$control_ops = array(
				'width'  => 300,
				'height' => 150,
			);
			parent::__construct( 'wp-championship-user', 'WP Championship User', $widget_ops, $control_ops );
		}

		/**
		 * Die Widget-Methode zum Anzeigen.
		 *
		 * @param array  $args Parameter für das Widget.
		 * @param object $instance Widgetobjekt.
		 */
		public function widget( $args, $instance ) {
			include plugin_dir_path( __FILE__ ) . 'globals.php';
			global $wpdb;

			$title               = apply_filters( 'widget_title', empty( $instance['title'] ) ? '&nbsp;' : $instance['title'] );
			$showaverage         = empty( $instance['showAverage'] ) ? 'no' : $instance['showAverage'];
			$showamounttipps     = empty( $instance['showAmountTipps'] ) ? 'yes' : $instance['showAmountTipps'];
			$showusertendence    = empty( $instance['showUserTendence'] ) ? 'yes' : $instance['showUserTendence'];
			$showfullranking     = empty( $instance['showFullRanking'] ) ? 'no' : $instance['showFullRanking'];
			$fullrankingurl      = empty( $instance['FullRankingURL'] ) ? 'www.yoursite.com' : $instance['FullRankingURL'];
			$fullrankingurltitle = empty( $instance['FullRankingURLTitle'] ) ? 'more' : $instance['FullRankingURLTitle'];

			echo wp_kses( $args['before_widget'], wpc_allowed_tags() );
			if ( $title ) {
				echo wp_kses( $args['before_title'] . $title . $args['after_title'], wpc_allowed_tags() );
			}

			// Query User and Points.
			$res       = cs_get_ranking();
			$curr_user = get_current_user_id();

			// test for WordPress user logged in.
			if ( 0 == $curr_user ) {
				echo '<div>' . esc_attr__( 'You are not logged in.', 'wp-championship' ) . '</div>';
				echo wp_kses( $args['after_widget'], wpc_allowed_tags() );
				return;
			}

			// test for tippspiel user.
			// The placeholder ignores can be removed when %i is supported by WPCS.
            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$csuser = $wpdb->get_row( $wpdb->prepare( 'select count(*) as csu from %i where userid = %d', $cs_users, $curr_user ) );

			if ( 0 == $csuser->csu ) {
				echo '<div>' . esc_attr__( 'You are not approved for the guessing game.', 'wp-championship' ) . '</div>';
				echo wp_kses( $args['after_widget'], wpc_allowed_tags() );
				return;
			}

			$out = '';
			// user ist logged in and approved for tippspiel. show info now.
			if ( ! empty( $res ) ) {
				// Show Userinfo.
				$pointsbefore = -1;
				$i            = 0;
				$j            = 1;

				foreach ( $res as $row ) {

					if ( $row->points != $pointsbefore ) {
						$i = $i + $j;
						$j = 1;
					} else {
						++$j;
					}

					if ( $i < $row->oldrank || -1 == $row->oldrank ) {
						$trend = plugins_url( 'up.png', __FILE__ );
					} elseif ( $i > $row->oldrank ) {
						$trend = plugins_url( 'down.png', __FILE__ );
					} else {
						$trend = plugins_url( 'samenew.png', __FILE__ );
					}

					// nur den eingeloggten User ausgeben.
					if ( $curr_user == $row->userid ) {
						$out .= '<div>';
						$out .= __( 'You are logged in as', 'wp-championship' ) . ' ' . esc_attr( $row->vdisplay_name ) . '.<br/>';
						$out .= __( 'You are ranked', 'wp-championship' ) . ' ' . esc_attr( $i ) . '., ' . __( 'having', 'wp-championship' ) . ' ';
						$out .= esc_attr( $row->points ) . ' ' . __( 'points', 'wp-championship' ) . '.';
						if ( '1' == $showusertendence ) {
							$out .= ' (' . __( 'trend', 'wp-championship' ) . ':' . "<img src='" . esc_attr( $trend ) . "' alt='trend' />" . ')';
						}
						$out .= '</br>';

						if ( '1' == $showaverage ) {
							// The placeholder ignores can be removed when %i is supported by WPCS.
                            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
							$tipps_count = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where result1 != -1 AND result2 != -1', $cs_match ) );
							$pds         = 0;
							if ( $tipps_count->anz > 0 ) {
								$pds = round( $row->points / $tipps_count->anz, 1 );
							}
							$out .= __( 'Your average is', 'wp-championship' ) . ' ' . esc_attr( $pds ) . '.</br>';
						}
						if ( '1' == $showamounttipps ) {
							// The placeholder ignores can be removed when %i is supported by WPCS.
                            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
							$tipps_count = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where userid=%s AND result1 != -1 AND result2 != -1', $cs_tipp, $row->userid ) );
							$out        .= __( 'The number of your tips is', 'wp-championship' ) . ' ' . esc_attr( $tipps_count->anz ) . '.</br>';
						}
						$out .= '</div>';
					}

					// gruppenwechsel versorgen.
					$pointsbefore = $row->points;
				}

				if ( '1' == $showfullranking && $fullrankingurl ) {
					$out .= '<div style="text-align:center"><a href="http://' . esc_attr( $fullrankingurl ) . '">' . esc_attr( $fullrankingurltitle ) . '</a></div>';
				}
			} else {
				$out .= '<div>' . esc_attr__( 'There are no game results yet.', 'wp-championship' ) . '</div>';
			}

			// let it out.
			echo wp_kses( $out, wpc_allowed_tags() );
			echo wp_kses( $args['after_widget'], wpc_allowed_tags() );
		}


		/**
		 * Methode zum Updaten.
		 *
		 * @param object $new_instance neue Instanz des Widgets.
		 * @param object $old_instance alte Instanz des Widgets.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title']               = strip_tags( stripslashes( $new_instance['title'] ) );
			$instance['showAverage']         = strip_tags( stripslashes( $new_instance['showAverage'] ) );
			$instance['showAmountTipps']     = strip_tags( stripslashes( $new_instance['showAmountTipps'] ) );
			$instance['showUserTendence']    = strip_tags( stripslashes( $new_instance['showUserTendence'] ) );
			$instance['showFullRanking']     = strip_tags( stripslashes( $new_instance['showFullRanking'] ) );
			$instance['FullRankingURL']      = strip_tags( stripslashes( $new_instance['FullRankingURL'] ) );
			$instance['FullRankingURLTitle'] = strip_tags( stripslashes( $new_instance['FullRankingURLTitle'] ) );

			return $instance;

		}


		/**
		 * Methode für das Formular des Widgets.
		 *
		 * @param object $instance Instanz des Widgets.
		 */
		public function form( $instance ) {
			// Vorbelegung.
			$defaults = array(
				'title'               => 'wp-Championship User',
				'showAverage'         => '1',
				'showAmountTipps'     => '1',
				'showUserTendence'    => '1',
				'showFullRanking'     => '1',
				'FullRankingURL'      => 'www.yoursite.com',
				'FullRankingURLTitle' => 'mehr...',
			);

			$instance = wp_parse_args( $instance, $defaults );

			$title               = htmlspecialchars( $instance['title'] );
			$showaverage         = htmlspecialchars( $instance['showAverage'] );
			$showamounttipps     = htmlspecialchars( $instance['showAmountTipps'] );
			$showusertendence    = htmlspecialchars( $instance['showUserTendence'] );
			$showfullranking     = htmlspecialchars( $instance['showFullRanking'] );
			$fullrankingurl      = htmlspecialchars( $instance['FullRankingURL'] );
			$fullrankingurltitle = htmlspecialchars( $instance['FullRankingURLTitle'] );

			//
			// Einstellungsdialog des Widgets ausgeben.
			//
			?>
<p>
	<label><?php echo esc_attr__( 'Title', 'wp-championship' ); ?>:</label> 
	<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
		name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
		value="<?php echo esc_attr( $title ); ?>" 
	/>
</p>
<p>
	<label><?php echo esc_attr__( 'Show average?', 'wp-championship' ); ?></label>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showAverage' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showAverage' ) ); ?>" 
		value="1"
			<?php checked( '1', $showaverage ); ?>
	>
			<?php echo esc_attr__( 'Yes', 'wp-championship' ); ?>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showAverage' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showAverage' ) ); ?>" 
		value="0"
			<?php checked( '0', $showaverage ); ?>
	>
			<?php echo esc_attr__( 'No', 'wp-championship' ); ?>
</p>
<p>
	<label><?php echo esc_attr__( 'Number of tips?', 'wp-championship' ); ?>
	</label> <input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showAmountTipps' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showAmountTipps' ) ); ?>" value="1"
			<?php checked( '1', $showamounttipps ); ?>
	>
			<?php echo esc_attr__( 'Yes', 'wp-championship' ); ?>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showAmountTipps' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showAmountTipps' ) ); ?>" 
		value="0"
			<?php checked( '0', $showamounttipps ); ?>
	>
			<?php echo esc_attr__( 'No', 'wp-championship' ); ?>
</p>
<p>
	<label><?php echo esc_attr__( 'Show tendency?', 'wp-championship' ); ?>
	</label> <input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showUserTendence' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showUserTendence' ) ); ?>" 
		value="1"
			<?php checked( '1', $showusertendence ); ?>
	>
			<?php echo esc_attr__( 'Yes', 'wp-championship' ); ?>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showUserTendence' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showUserTendence' ) ); ?>" 
		value="0"
			<?php checked( '0', $showusertendence ); ?>
	>
			<?php echo esc_attr__( 'No', 'wp-championship' ); ?>
</p>
<p>
	<label><?php echo esc_attr__( 'Show link?', 'wp-championship' ); ?></label> 
	<input type="radio" 
		name="<?php echo esc_attr( $this->get_field_name( 'showFullRanking' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showFullRanking' ) ); ?>" 
		value="1"
			<?php checked( '1', $showfullranking ); ?>
	>
			<?php echo esc_attr__( 'Yes', 'wp-championship' ); ?>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showFullRanking' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showFullRanking' ) ); ?>" 
		value="0"
			<?php checked( '0', $showfullranking ); ?>
	>
			<?php echo esc_attr__( 'No', 'wp-championship' ); ?>
</p>
<p>
	<label for="widget_wp_championship_points-FullRankingURL"><?php echo esc_attr__( 'Link URL', 'wp-championship' ); ?>
		http://</label> <input type="text"
		id="<?php echo esc_attr( $this->get_field_name( 'FullRankingURL' ) ); ?>"
		name="<?php echo esc_attr( $this->get_field_name( 'FullRankingURL' ) ); ?>"
		value="<?php echo esc_attr( $fullrankingurl ); ?>" />
</p>
<p>
	<label for="widget_wp_championship_points-FullRankingURLTitle"><?php echo esc_attr__( 'Link label', 'wp-championship' ); ?>
	</label> <input type="text"
		id="<?php echo esc_attr( $this->get_field_name( 'FullRankingURLTitle' ) ); ?>"
		name="<?php echo esc_attr( $this->get_field_name( 'FullRankingURLTitle' ) ); ?>"
		value="<?php echo esc_attr( $fullrankingurltitle ); ?>" />
</p>
			<?php

		}

	} // end of class
} // endif class exists
?>
