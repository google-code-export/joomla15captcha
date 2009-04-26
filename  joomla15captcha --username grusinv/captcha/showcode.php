<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/* 16:34 25.10.2008 */
/* 4.3.0 */

/**
 * Captcha show core for Joomla! 1.5
 * 
 * Long description for file see http://code.google.com/p/joomla15captcha/
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; either version 2.1 of the
 * License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category Captcha
 * @package Joomla
 * @author Victor Grusin <joomlacode@kupala.net>
 * @copyright Copyright (C) 2008 Victor Grusin. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL.
 * @version CVS: $Id: showcode.php 15 2008-09-19 00:00:00Z kupala $
 * @link http://code.google.com/p/joomla15captcha/
 * @since File available since Joomla Release 1.5
 * @deprecated none
 * @see http://www.joomla.org/
 * @see http://en.wikipedia.org/wiki/CAPTCHA
 */


@$captchasuffix = '' . $_GET['suf'];
if ( !$captchasuffix ) $captchasuffix = '';

@$sid = '' . $_GET['sid'];
if ( !$sid ) $sid = 'joomlacaptcha';

@session_id( $sid );
session_start();

$captchalayout = '';
@$captchalayout .= $_SESSION[ 'layout' ];
// image imagesound sound

$captchacode = '';
$captchavoice = '';
@$captchaslist = (array) $_SESSION[ 'captchaslist' . $captchasuffix ]; // (array)
if ($captchalayout == 'sound') {
	$currentcaptchas = $captchaslist[ max( 0, (count($captchaslist) - 1) ) ];
} else {
	$currentcaptchas = array_pop( $captchaslist ); //
}
if (array_key_exists( 'imagecode', (array) $currentcaptchas ) ) { //
	$captchacode .= $currentcaptchas[ 'imagecode' ];
	$captchavoice .= $currentcaptchas[ 'soundcode' ];
	@$_SESSION [ 'acaptcha' . $captchasuffix ] = $captchacode;
	@$_SESSION [ 'ncaptcha' . $captchasuffix ] = $captchavoice;
	@$_SESSION [ 'captchaslist' . $captchasuffix ] = $captchaslist;
} else {
	@$_SESSION [ 'acaptcha' . $captchasuffix ] = '';
	@$_SESSION [ 'ncaptcha' . $captchasuffix ] = '';
	@$_SESSION [ 'captchaslist' . $captchasuffix ] = array();
	$captchacode = 'joomlacaptcha';
	$xsize = 200;
}
$length = strlen( $captchacode );

@$attffile = '' . $_SESSION['attffile' . $captchasuffix];
if ( !$attffile ) $attffile = '';

session_write_close();

@$crypttype = 0 + $_GET['crt'];
if ( !$crypttype ) $crypttype = 0;

// image only >>>>>>>

@$clr = '' . $_GET['clr'];
if ( !$clr ) $clr = '0';

@$bgr = '' . $_GET['bgr'];
if ( !$bgr ) $bgr = '196,196,196';

@$xsize = 0 + $_GET['xsize'];
if ( !$xsize ) $xsize = 100;

@$ysize = 0 + $_GET['ysize'];
if ( !$ysize ) $ysize = 40;

$xsize = min( 200, $xsize );
$ysize = min( 200, $ysize );
$xsize = max( 100, $xsize );
$ysize = max( 40, $ysize );

// <<<<<<< image only			  

switch ( $crypttype ) {
	case '0' :
		$rndstring = $captchacode;
		break;
}

$fontfile      = 'files/DessinImmortel.ttf';
if (file_exists($attffile)) {
	$fontfile   = $attffile;
}
$output_type   = 'jpeg';

//////////
// get extend parameters from a session if they exists

@$min_font_size = 0 + $_SESSION['min_font_size']; // 18 - 24
@$max_font_size = 0 + $_SESSION['max_font_size']; // 18 - 24
$min_font_size = min( 24, $min_font_size );
$min_font_size = max( 18, $min_font_size );
$max_font_size = min( 24, $max_font_size );
$max_font_size = max( $min_font_size, $max_font_size );

@$max_angle = 0 + $_SESSION['max_angle']; // 0 - 30
$max_angle = max( 0, $max_angle );
$max_angle = min( 30, $max_angle );

@$im_padding = 0 + $_SESSION['im_padding']; // 1 - 10
if ( !$im_padding ) $im_padding = 5;
$im_padding = max( 0, $im_padding );
$im_padding = min( 10, $im_padding );

@$char_padding = 0 + $_SESSION['char_padding']; // 1 - 10
if ( !$char_padding ) $char_padding = 5;
$char_padding = max( 0, $char_padding );
$char_padding = min( 10, $char_padding );

@$char_filling = '' . $_SESSION['char_filling']; // '/'
if ( !$char_filling ) $char_filling = '/';

//////////
// make an image

$data          = array();
$image_width = $image_height = 0;
 
for ( $i = 0; $i < $length; $i++ ) {
    $char        = substr( $rndstring, $i, 1 );
	if (rand( 0, 1 )) $char = strtoupper ( $char ); 
    $size        = mt_rand( $min_font_size, $max_font_size );
    $angle       = mt_rand( -$max_angle, $max_angle );
    $bbox        = ImageTTFBBox( $size, $angle, $fontfile, $char );
    $char_width  = max( $bbox[2], $bbox[4] ) - min( $bbox[0], $bbox[6] );
    $char_height = max( $bbox[1], $bbox[3] ) - min( $bbox[7], $bbox[5] );

    $image_width += $char_width + $char_padding;
    $image_height = max( $image_height, $char_height );

    $data[] = array(
        'char'   => $char,
        'size'   => $size,
        'angle'  => $angle,
        'height' => $char_height,
        'width'  => $char_width,
    );
}

$image_width += ($im_padding * 2);
$image_height = ($image_height * 1.5) + 2;
$im = ImageCreate( $image_width, $image_height );

$backcolors = array_pad( explode( ',', $bgr ), 3, 0 );
$back_r = (int) $backcolors[0];
$back_g = (int) $backcolors[1];
$back_b = (int) $backcolors[2];
$textcolors = array_pad( explode( ',', $clr ), 3, 0 );
$text_r = (int) $textcolors[0];
$text_g = (int) $textcolors[1];
$text_b = (int) $textcolors[2];
$color_border = ImageColorAllocate( $im, $back_r, $back_g, $back_b );
$color_text = ImageColorAllocate( $im, $text_r, $text_g, $text_b );

//////////
// fill a noise

for ($i = 1; $i <= 20; $i++) {
	$pos_x = mt_rand( 1, $image_width );
    $pos_y = mt_rand( 1, $image_height );
    ImageTTFText( $im, mt_rand( 8, 10 ), mt_rand( -45, 45 ), $pos_x, $pos_y, $color_text, $fontfile, $char_filling );}

//////////
// draw the captcha

$pos_x = max( $im_padding, $max_angle / 3 );
foreach ($data as $d) {
    $pos_y = ( ( $image_height + $d['height'] ) / 2 );
    ImageTTFText( $im, $d['size'], $d['angle'], $pos_x, $pos_y, $color_text, $fontfile, $d ['char'] );
    $pos_x += $d ['width'] + $char_padding;    
}

if (function_exists( "ImageCreateTrueColor" ))
	$im_out = ImageCreateTrueColor( $xsize, $ysize );
else
	$im_out = ImageCreate( $xsize, $ysize );
			  
if (function_exists( "ImageCopyResampled" ))
	ImageCopyResampled( $im_out, $im, 0, 0, 0, 0, $xsize, $ysize, imagesx( $im ), imagesy( $im ) );
else
	ImageCopyResized( $im_out, $im, 0, 0, 0, 0, $xsize, $ysize, imagesx( $im ), imagesy( $im ) );
	
//////////
// output
			  
ob_start();
header( 'Expires: Thu, 01 Jan 1980 00:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
header( 'Content-type: image/jpeg' );
ImageJPEG( $im_out );
ImageDEstroy( $im);
ImageDEstroy( $im_out);
ob_end_flush();