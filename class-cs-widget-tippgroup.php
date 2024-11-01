<?php
/** This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2010-2021  Hans Matzen  (email : webmaster at tuxlog.de)
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

if ( ! class_exists( 'cs_widget_tippgroup' ) ) {
	/**
	 * Klasse für das Tippgruppenwidget.
	 */
	class Cs_Widget_Tippgroup extends WP_Widget {

		/**
		 * Konstruktor für das Tippgruppenwidget.
		 */
		public function __construct() {
			$widget_ops  = array(
				'classname'   => 'cs_widget_tippgroup',
				'description' => 'WP Championship Tippgruppen Widget',
			);
			$control_ops = array(
				'width'  => 300,
				'height' => 150,
			);
			parent::__construct( 'wp-championship-tippgroup', 'WP Championship Tippgruppen', $widget_ops, $control_ops );
		}

		/**
		 * Wdiget Methode.
		 *
		 * @param array $args Parameter für das Widget.
		 * @param array $instance Widgetinstanz.
		 */
		public function widget( $args, $instance ) {
			include plugin_dir_path( __FILE__ ) . 'globals.php';
			global $wpdb;

			$title               = apply_filters( 'widget_title', empty( $instance['title'] ) ? '&nbsp;' : $instance['title'] );
			$showaverage         = empty( $instance['showAverage'] ) ? 'no' : $instance['showAverage'];
			$showamountusers     = empty( $instance['showAmountUsers'] ) ? 'yes' : $instance['showAmountUsers'];
			$amountgroups        = empty( $instance['AmountGroups'] ) ? '5' : $instance['AmountGroups'];
			$showfullranking     = empty( $instance['showFullRanking'] ) ? 'no' : $instance['showFullRanking'];
			$fullrankingurl      = empty( $instance['FullRankingURL'] ) ? 'www.yoursite.com' : $instance['FullRankingURL'];
			$fullrankingurltitle = empty( $instance['FullRankingURLTitle'] ) ? 'more' : $instance['FullRankingURLTitle'];

			echo wp_kses( $args['before_widget'], wpc_allowed_tags() );
			if ( $title ) {
				echo wp_kses( $args['before_title'] . $title . $args['after_title'], wpc_allowed_tags() );
			}

			// Query User and Points.
			$limit = $amountgroups;
			$res   = cs_get_tippgroup_ranking();

			if ( ! empty( $res ) ) {
				// Table Head.
				echo "<table  class='widgettable'>";
				echo '<tr>';
				echo '<td>&nbsp;</td>';
				echo "<td align='left' >" . esc_attr__( 'N', 'wp-championship' ) . '</td>';
				echo "<td align='center'>P</td>";
				if ( '1' == $showaverage ) {
					echo "<td align='center'>&Oslash;</td>";
				}
				if ( '1' == $showamountusers ) {
					echo "<td align='center'>T</td>";
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

					echo '<tr>';
					echo "<td align='left'>" . esc_attr( $i ) . '</td>';
					echo "<td align='left'>" . esc_attr( $row->name ) . '</td>';
					echo "<td align='center'>" . esc_attr( $row->points ) . '</td>';
					if ( '1' == $showaverage ) {
						echo "<td align='center'>" . esc_attr( round( $row->average, 1 ) ) . '</td>';
					}
					if ( '1' == $showamountusers ) {
						echo "<td align='center'>" . esc_attr( $row->numusers ) . '</td>';
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
		 * Funktion um die Widgetparameter upzudaten.
		 *
		 * @param object $new_instance Neue Instanz des Widgets.
		 * @param object $old_instance Alte Instanz des Widgets.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title']               = strip_tags( stripslashes( $new_instance['title'] ) );
			$instance['showAverage']         = strip_tags( stripslashes( $new_instance['showAverage'] ) );
			$instance['showAmountUsers']     = strip_tags( stripslashes( $new_instance['showAmountUsers'] ) );
			$instance['AmountGroups']        = strip_tags( stripslashes( $new_instance['AmountGroups'] ) );
			$instance['showFullRanking']     = strip_tags( stripslashes( $new_instance['showFullRanking'] ) );
			$instance['FullRankingURL']      = strip_tags( stripslashes( $new_instance['FullRankingURL'] ) );
			$instance['FullRankingURLTitle'] = strip_tags( stripslashes( $new_instance['FullRankingURLTitle'] ) );

			return $instance;

		}


		/**
		 * Methode um das PArameterformukar anzuzeigen.
		 *
		 * @param array $instance Widgetinstanz.
		 */
		public function form( $instance ) {
			// Vorbelegung.
			$defaults = array(
				'title'               => 'wp-Championship Tippgruppen',
				'showAverage'         => '1',
				'showAmountUsers'     => '1',
				'AmountGroups'        => '5',
				'showFullRanking'     => '1',
				'FullRankingURL'      => 'www.yoursite.com',
				'FullRankingURLTitle' => 'mehr...',
			);

			$instance = wp_parse_args( $instance, $defaults );

			$title               = htmlspecialchars( $instance['title'] );
			$showaverage         = htmlspecialchars( $instance['showAverage'] );
			$showamountusers     = htmlspecialchars( $instance['showAmountUsers'] );
			$amountgroups        = htmlspecialchars( $instance['AmountGroups'] );
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
		name="<?php echo esc_attr( $this->get_field_name( 'showAverage' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showAverage' ) ); ?>" value="0"
			<?php
			if ( '0' == $showaverage ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'No', 'wp-championship' ); ?>
</p>
<p>
	<label for="widget_wp_championship_points-showAmountUsers"><?php echo esc_attr__( 'Number of tips?', 'wp-championship' ); ?>
	</label> <input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showAmountUsers' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showAmountUsers' ) ); ?>" value="1"
			<?php
			if ( '1' == $showamountusers ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'Yes', 'wp-championship' ); ?>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showAmountUsers' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showAmountUsers' ) ); ?>" value="0"
			<?php
			if ( '0' == $showamountusers ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'No', 'wp-championship' ); ?>
</p>
<p>
	<label for="widget_wp_championship_points-AmountGroups"><?php echo esc_attr__( 'Number of tip groups on display', 'wp-championship' ); ?>
	</label> <input style="width: 30px;" maxlength="2" type="text"
		name="<?php echo esc_attr( $this->get_field_name( 'AmountGroups' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'AmountGroups' ) ); ?>"
		value="<?php echo esc_attr( $amountgroups ); ?>" />
</p>
<p>
	<label for="widget_wp_championship_points-showFullRanking"><?php echo esc_attr__( 'Show link?', 'wp-championship' ); ?>
	</label> <input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showFullRanking' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showFullRanking' ) ); ?>" value="1"
			<?php
			if ( '1' == $showfullranking ) {
				echo 'checked="checked"';}
			?>
			>
			<?php echo esc_attr__( 'Yes', 'wp-championship' ); ?>
	<input type="radio"
		name="<?php echo esc_attr( $this->get_field_name( 'showFullRanking' ) ); ?>"
		id="<?php echo esc_attr( $this->get_field_name( 'showFullRanking' ) ); ?>" value="0"
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
