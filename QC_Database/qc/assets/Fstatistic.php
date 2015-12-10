<?php

function Fstatistic($x, $nd1, $nd2)
{
  $pvalue = 1.0;

  if ($x!=0.0){

    $u=$x;

    if ($nd1 + $nd2 > 69 || ($nd1 > 10 && $nd2 > 15)) {
      /* use approximation for large degrees of freedom */
		$a1=2.0/(9.0*$nd1);
		$a2=2.0/(9.0*$nd2);
		$u=((1.0-$a2)*pow($x,0.333333)-1.0+$a1)/sqrt(pow($x,0.666667)*$a2+$a1);
		$t=1.0/(1.0+.2316419*abs($u));
		$pvalue=$t*(0.31938153-$t*(0.35656378-$t*(1.7814779-$t*(1.82125598-1.33027443*$t))))*0.3989423*exp(-$u*$u/2.0);
    } else {
      /* degrees of freedom are within reasonable range */
      	$nn=$nd1;
		$f=$nd2/($nd2+$nd1*$x);
		$t=1.0-2.0*$f;
		$q=array(
		   1 => 0.5+atan($t/sqrt(1.0-$t*$t))/3.1415927,
		   2 => 1.0-sqrt($f)
		   );
		$gb=sqrt(3.1415927);
		$gab=array(
		 1 => 1.0,
		 2 => $gb
		 );
		$b=0.5;
		$y=1.0-$f;
      
	    for($jj=1; $jj<=2; $jj++) {
			$ga = array(
				  1 => sqrt(3.1415927),
				  2 => 1.0
				);

			if ($nn>2) {
			  	for($i=3;$i<=$nn;$i++) {
			    	$j=(($i+1) % 2)+1;
			    	$a=($i-2.0)*0.5;
			    	$ga[$j]=$a*$ga[$j];
			    	$gab[$j]=($a+$b-1.)*$gab[$j];
			    	if ($gab[$j]<=0.0) {
			      		$gab[$j]=1.0;
			    	}
			    	$q[$j]=$q[$j]-$gab[$j]/($ga[$j]*$gb)*pow($y,$a)*pow(1.0-$y,$b);
			  	}
			  	if ($jj==2) break;
			} else {
			  	$j=$nn;
			}

			$b=$nn*0.5;
			
			$q=array(
				 1 => 1.0-$q[$j],
				 2 => 1.0-pow($y,$b)
				 );
			
			$nn=$nd2;
			
			if ($nn<=2) {
			  $j=$nn;
			  break;
			}
			
			$gb=$ga[$j];
			$gab=array(
				   1 => $gab[$j],
				   2 => $gb
				   );
			$y=$f;	   
	    }
      	$pvalue=$q[$j];
    }

    /* invert p-value if test statistic is negative */
    if ($u < 0.0) {
      	$pvalue = 1.0 - $pvalue;
    }
    /* make sure we do not report p-values larger than 1 */
    if ($pvalue > 1.0) {
      	$pvalue = 1.0;
    }
  }
  return $pvalue;
}

?>