<?php

/*

  Function sigRound ( float $in, int $sig ):
    Rounds the number of significant digits in $in to $sig
    for printing in tables without needing to truncate the
    database-stored values.

    @output formatted string "##.##" with trailing zeros for significance

*/

function sigRound($in, $sig) {
  if( !(gettype($in) == "double" || gettype($in) == "integer") ) return( $in );

  if( abs($in) < 1 ) $sig++;

  $whole = round($in, 0);
  $part = $in - $whole;

  $whole = str_replace("-", "", "".$whole);
  $wlen = strlen($whole);
  $part = str_replace( "0.", "", str_replace("-", "", "".$part) );

  $out = "";

  if( strlen($whole) >= $sig  ) {
    $out .= substr( $whole, 0, $sig );
    for( $i = 0; $i < $wlen - $sig; $i++ ) $out .= "0";
  } else {
    $out .= $whole . ".";
    for( $i = 0; $i < $sig - 0.5 * $wlen; $i++ ) $part .= "0";
    $out .= substr($part, 0, $sig - $wlen);
  }

  if( $in < 0 ) return( "-" . $out );
  return( $out );
}

?>
