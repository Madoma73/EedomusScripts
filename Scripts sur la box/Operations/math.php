<?

$idperiph = getArg('idperiph');

$operation = getArg('operation');

$val1 = getArg('val1');
$val2 = getArg('val2');

$precision = getArg('precision',false,NULL);

switch ( strtolower($operation) ) {
   case "addition" :
      $resultat = $val1 + $val2;
      break;
   case "soustraction" :
      $resultat = $val1 - $val2;
      break;
   case "multiplication" :
      $resultat = $val1 * $val2;
      break;
   case "division" :
      $resultat = $val1 / $val2;
      break;
  case  "dailyenergy":
      $ArrIndexMinuit=getValue($val1);
      $IndexMinuit=$ArrIndexMinuit["value"];
      $ArrIndexNow= getValue($val2);
      $IndexNow=$ArrIndexNow["value"];
      $resultat=$IndexNow-$IndexMinuit;
      $resultat=$resultat/1000;
      // Save new Index
      setValue($val1, $IndexNow);
      break;
}

$res = setValue($idperiph, round($resultat,$precision));
?>