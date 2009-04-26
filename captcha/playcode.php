<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/* 16:34 25.10.2008 */
/* 4.3.0 */

/**
 * Captcha voice play core for Joomla! 1.5
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
 * @version CVS: $Id: playcode.php 15 2008-09-19 00:00:00Z kupala $
 * @link http://code.google.com/p/joomla15captcha/
 * @since File available since Joomla Release 1.5
 * @deprecated none
 * @see http://www.joomla.org/
 * @see http://en.wikipedia.org/wiki/CAPTCHA
 */

$captchasuffix = '';
@$captchasuffix = '' . $_GET['suf'];

@$sid = '' . $_GET['sid'];
if ( !$sid ) $sid = 'joomlacaptcha';

@$stamp = '' . $_GET['stamp'];
if ( !$stamp ) $stamp = 'joomlacaptcha';

@session_id( $sid );
session_start();

$captchalayout = '';
@$captchalayout .= $_SESSION[ 'layout' ];
// image imagesound sound

$captchacode = '';

$currentstamp = ''; // kill noise sessions
@$currentstamp .= $_SESSION[ 'stamp' ];
$_SESSION[ 'stamp' ] = $stamp;
if (($captchalayout == 'sound') && ($currentstamp != $stamp)) {
	@$captchaslist = (array) $_SESSION[ 'captchaslist' . $captchasuffix ];
	$currentcaptchas = array_pop( $captchaslist );
	if (array_key_exists( 'soundcode', (array) $currentcaptchas ) ) {
		$captchacode .= $currentcaptchas[ 'soundcode' ];
		@$_SESSION [ 'ncaptcha' . $captchasuffix ] = $captchacode;
	} else {
		$captchacode .= $currentcaptchas[ 'soundcode' ];
		@$_SESSION [ 'ncaptcha' . $captchasuffix ] = '';
		@$_SESSION [ 'acaptcha' . $captchasuffix ] = '';
	}
	@$_SESSION [ 'captchaslist' . $captchasuffix ] = $captchaslist;
} else {
	@$captchacode .= $_SESSION[ 'ncaptcha' . $captchasuffix  ]; // set up by voice
}

if (!$captchacode) $captchacode = '0000000000';

session_write_close();

@$lng = $_GET['lng'];
if ( !$lng ) $lng = 'en-gb';

$captchafilename = "joomlacaptcha.mp3";
$captchalength = strlen( $captchacode );

$outlength = 0;
$reallength = 0;
$currsize = 0;
$outstream = '';

if ($captchalength > 0) {
	for ($i = 0; $i < $captchalength; $i++) {
		$soundfiles[$i] = 'files/' . $lng . '.' . strtolower( substr( $captchacode, $i, 1 ) ) . '.mp3';
	}
	foreach ($soundfiles as $onefile){
		if (file_exists( $onefile )) {
			$instream = fopen( $onefile, 'rb' );
			$currsize = filesize( $onefile );
			$outstream .= fread( $instream, $currsize );
			$outlength += $currsize;
			fclose( $instream );
			$reallength += 1;
		}
	}
}

if (($outstream == '') || ($captchalength != $reallength)) {
		$outstream = 0; $outlength = 1;
}

ob_start();
header( 'Content-Type: audio/x-mpeg');
header( "Content-Disposition: attachment; filename=$captchafilename;");
header( 'Content-Transfer-Encoding: binary');
header( 'Content-Length: '.$outlength);
echo $outstream ;
ob_end_flush();