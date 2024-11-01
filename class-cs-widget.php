<?php
/** This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2010-2024  Hans Matzen  (email : webmaster at tuxlog.de)
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

if ( ! class_exists( 'cs_widget' ) ) {
	/**
	 * Klasse für das wp-championship widget.
	 */
	class CS_Widget extends WP_Widget {
		/**
		 * Konstruktor des cs Widgets.
		 */
		public function __construct() {
			$widget_ops  = array(
				'classname'   => 'cs_widget',
				'description' => 'WP Championship Widget',
			);
			$control_ops = array(
				'width'  => 300,
				'height' => 150,
			);
			// $this->WP_Widget('wp-championship', 'WP Championship',
			// $widget_ops, $control_ops);
			parent::__construct( 'wp-championship', 'WP Championship', $widget_ops, $control_ops );
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
			$amountusers         = empty( $instance['AmountUsers'] ) ? '5' : $instance['AmountUsers'];
			$showfullranking     = empty( $instance['showFullRanking'] ) ? 'no' : $instance['showFullRanking'];
			$fullrankingurl      = empty( $instance['FullRankingURL'] ) ? 'www.yoursite.com' : $instance['FullRankingURL'];
			$fullrankingurltitle = empty( $instance['FullRankingURLTitle'] ) ? 'more' : $instance['FullRankingURLTitle'];

			echo wp_kses( $args['before_widget'], wpc_allowed_tags() );
			if ( $title ) {
				echo wp_kses( $args['before_title'] . $title . $args['after_title'], wpc_allowed_tags() );
			}

			// Query User and Points.
			$limit = $amountusers;
			$res   = cs_get_ranking();

			if ( ! empty( $res ) ) {
				// Table Head.
				echo "<table  class='widgettable'>";
				echo '<tr>';
				echo '<td>&nbsp;</td>';
				echo "<td align='left'>" . esc_attr__( 'Name', 'wp-championship' ) . '</td>';
				echo "<td align='center'>P</td>";
				if ( '1' == $showaverage ) {
					echo "<td align='center'>&Oslash;</td>";
				}
				if ( '1' == $showamounttipps ) {
					echo "<td align='center'>T</td>";
				}
				if ( '1' == $showusertendence ) {
					echo "<td align='center'>&nbsp;</td>";
				}
				echo '</tr>';

				// Table Content.
				$pointsbefore = -1;
				$i            = 0;
				$j            = 1;
				$k            = 0;
				foreach ( $res as $row ) {
					if ( $k >= $limit ) {
						break;
					}
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
						$trend = plugins_url( 'same.png', __FILE__ );
					}

					echo '<tr>';
					echo "<td align='left'>" . esc_attr( $i ) . '.</td>';
					echo "<td align='left'>" . esc_attr( $row->vdisplay_name ) . '</td>';
					echo "<td align='center'>" . esc_attr( $row->points ) . '</td>';
					if ( '1' == $showaverage ) {
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						$tipps_count = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where result1 != -1 AND result2 != -1', $cs_match ) );
						$pds         = 0;
						if ( $tipps_count->anz > 0 ) {
							$pds = round( $row->points / $tipps_count->anz, 1 );
						}
						echo "<td align='center'>" . esc_attr( $pds ) . '</td>';
					}
					if ( '1' == $showamounttipps ) {
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						$tipps_count = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where userid=%s AND result1 != -1 AND result2 != -1', $cs_tipp, $row->userid ) );
						echo "<td align='center'>" . esc_attr( $tipps_count->anz ) . '</td>';
					}
					if ( '1' == $showusertendence ) {
						echo "<td align='center'><img src='" . esc_attr( $trend ) . "' alt='trend' /></td>";
					}

					// gruppenwechsel versorgen.
					$pointsbefore = $row->points;

					// Ausgabezaehler erhoehen.
					++$k;
				}

				// Table foot.
				echo '</tr></table>';
				if ( '1' == $showfullranking && $fullrankingurl ) {
					echo '<div style="text-align:center"><a href="http://' . esc_attr( $fullrankingurl ) . '">' . esc_attr( $fullrankingurltitle ) . '</a></div>';
				}
			} else {
				echo esc_attr__( 'There are no game results yet.', 'wp-championship' );
			}
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
			$instance['AmountUsers']         = strip_tags( stripslashes( $new_instance['AmountUsers'] ) );
			$instance['showFullRanking']     = strip_tags( stripslashes( $new_instance['showFullRanking'] ) );
			$instance['FullRankingURL']      = strip_tags( stripslashes( $new_instance['FullRankingURL'] ) );
			$instance['FullRankingURLTitle'] = strip_tags( stripslashes( $new_instance['FullRankingURLTitle'] ) );

			return $instance;

		}


		/**
		 * Methode für das Formukar des Widgets.
		 *
		 * @param object $instance Instanz des Widgets.
		 */
		public function form( $instance ) {
			// Vorbelegung.
			$defaults = array(
				'title'               => 'wp-Championship',
				'showAverage'         => '1',
				'showAmountTipps'     => '1',
				'showUserTendence'    => '1',
				'AmountUsers'         => '5',
				'showFullRanking'     => '1',
				'FullRankingURL'      => 'www.yoursite.com',
				'FullRankingURLTitle' => 'mehr...',
			);

			$instance = wp_parse_args( $instance, $defaults );

			$title               = htmlspecialchars( $instance['title'] );
			$showaverage         = htmlspecialchars( $instance['showAverage'] );
			$showamounttipps     = htmlspecialchars( $instance['showAmountTipps'] );
			$showusertendence    = htmlspecialchars( $instance['showUserTendence'] );
			$amountusers         = htmlspecialchars( $instance['AmountUsers'] );
			$showfullranking     = htmlspecialchars( $instance['showFullRanking'] );
			$fullrankingurl      = htmlspecialchars( $instance['FullRankingURL'] );
			$fullrankingurltitle = htmlspecialchars( $instance['FullRankingURLTitle'] );

			//
			// Einstellungsdialog des Widgets ausgeben.
			//
			?>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_attr__( 'Title', 'wp-championship' ); ?>:
	</label> <input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
		name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
		value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
	<label for="widget_wp_championship_points-showAverage"><?php echo esc_attr__( 'Show average?', 'wp-championship' ); ?>
	</label> <input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showAverage' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showAverage' ) ); ?>" value="1"
			<?php
			if ( '1' == $showaverage ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'Yes', 'wp-championship' ); ?>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showaverage' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showaverage' ) ); ?>" value="0"
			<?php
			if ( '0' == $showaverage ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'No', 'wp-championship' ); ?>
</p>
<p>
	<label for="widget_wp_championship_points-showAmountTipps"><?php echo esc_attr__( 'Number of tips?', 'wp-championship' ); ?>
	</label> <input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showamounttipps' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showamounttipps' ) ); ?>" value="1"
			<?php
			if ( '1' == $showamounttipps ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'Yes', 'wp-championship' ); ?>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showamounttipps' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showamounttipps' ) ); ?>" value="0"
			<?php
			if ( '0' == $showamounttipps ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'No', 'wp-championship' ); ?>
</p>
<p>
	<label for="widget_wp_championship_points-showUserTendence"><?php echo esc_attr__( 'Show tendency?', 'wp-championship' ); ?>
	</label> <input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showusertendence' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showusertendence' ) ); ?>" value="1"
			<?php
			if ( '1' == $showusertendence ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'Yes', 'wp-championship' ); ?>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showusertendence' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showusertendence' ) ); ?>" value="0"
			<?php
			if ( '0' == $showusertendence ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'No', 'wp-championship' ); ?>
</p>
<p>
	<label for="widget_wp_championship_points-AmountUsers"><?php echo esc_attr__( 'Number of players on display', 'wp-championship' ); ?>
	</label> <input style="width: 30px;" maxlength="2" type="text"
		name="<?php echo esc_attr( $this->get_field_name( 'AmountUsers' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'AmountUsers' ) ); ?>"
		value="<?php echo esc_attr( $amountusers ); ?>" />
</p>
<p>
	<label for="widget_wp_championship_points-showFullRanking"><?php echo esc_attr__( 'Show link?', 'wp-championship' ); ?>
	</label> <input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showfullranking' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showfullranking' ) ); ?>" value="1"
			<?php
			if ( '1' == $showfullranking ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'Yes', 'wp-championship' ); ?>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showfullranking' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showfullranking' ) ); ?>" value="0"
			<?php
			if ( '0' == $showfullranking ) {
				echo 'checked="checked"';}
			?>
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
