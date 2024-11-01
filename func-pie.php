<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2010-2023  Hans Matzen  (email : webmaster at tuxlog.de)
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

/* thanks to all authors on php.net for the pie chart example */

$bright_list = array(
	array( 255, 203, 3 ),
	array( 220, 101, 29 ),
	array( 189, 24, 51 ),
	array( 214, 0, 127 ),
	array( 98, 1, 96 ),
	array( 0, 62, 136 ),
	array( 0, 102, 179 ),
	array( 0, 145, 195 ),
	array( 0, 115, 106 ),
	array( 178, 210, 52 ),
	array( 137, 91, 74 ),
	array( 82, 56, 47 ),
);
$dark_list   = array(
	array( 205, 153, 0 ),
	array( 170, 51, 0 ),
	array( 139, 0, 1 ),
	array( 164, 0, 77 ),
	array( 48, 0, 46 ),
	array( 0, 12, 86 ),
	array( 0, 52, 129 ),
	array( 0, 95, 145 ),
	array( 0, 65, 56 ),
	array( 128, 160, 2 ),
	array( 87, 41, 24 ),
	array( 32, 6, 0 ),
);

$data    = array();
$angle   = array();
$fptitle = array();
$i       = 0;
foreach ( $_GET as $key => $value ) {
	$data[ $i ]      = intval( $value );
	$fptitle[ $i++ ] = str_replace( '_', ' ', strval( $key ) );
}
$sum = array_sum( $data );
if ( 0 == $sum ) {
	++ $sum;
}
$count = count( $data );
for ( $i = 0; $i < $count; ++ $i ) {
	$angle[ $i ] = floor( $data[ $i ] / $sum * 360 );
	if ( 0 == $angle[ $i ] ) {
		++ $angle[ $i ];
	}
}
$sum_angle = array_sum( $angle );
if ( $sum_angle < 360 ) {
	$angle[0] += 360 - $sum_angle;
}

$height = $count * 34;
if ( $height < 180 ) {
	$height = 180;
}

$im         = imagecreate( 350, $height );
$background = imagecolorallocate( $im, 226, 226, 226 );
$border     = imagecolorallocate( $im, 97, 97, 97 );
$font_color = imagecolorallocate( $im, 0, 0, 0 );
$font       = './ARIALMT.ttf';

$bright = array();
foreach ( $bright_list as $c ) {
	$bright[] = imagecolorallocate( $im, $c[0], $c[1], $c[2] );
}

$dark = array();
foreach ( $dark_list as $c ) {
	$dark[] = imagecolorallocate( $im, $c[0], $c[1], $c[2] );
}
$tmp = 0;
for ( $i = 0; $i < $count; ++ $i ) {
	for ( $j = 100; $j > 90; -- $j ) {
		imagefilledarc( $im, 100, $j, 180, 120, $tmp, $tmp + $angle[ $i ], $dark[ $i ], IMG_ARC_PIE );
	}
	$tmp += $angle[ $i ];
}

$tmp = 0;
for ( $i = 0; $i < $count; ++ $i ) {
	imagefilledarc( $im, 100, 90, 180, 120, $tmp, $tmp + $angle[ $i ], $bright[ $i ], IMG_ARC_PIE );
	$tmp += $angle[ $i ];
}
for ( $i = 0; $i < $count; ++ $i ) {
	imagefilledrectangle( $im, 209, 19 + ( $i * 30 ), 231, 41 + ( $i * 30 ), $border );
	imagefilledrectangle( $im, 210, 20 + ( $i * 30 ), 230, 40 + ( $i * 30 ), $bright[ $i ] );

	$fptitle[ $i ] = $fptitle[ $i ][0] . ':' . $fptitle[ $i ][1]; // tipp optisch aufbereiten.
	imagefttext( $im, 11, 0, 240, 34 + ( $i * 30 ), $font_color, $font, $fptitle[ $i ] . ' -- ' . $data[ $i ] . '%' );
}
header( 'Content-type: image/png' );
imagepng( $im );
imagedestroy( $im );

