<?
// script créé par Pierre Pollet pour eedomus
// pour gérer des compteurs
// Version 1   / 03 Juillet 2014		/ 1ère version disponible


// paramètres de configuration
$action = getArg('action');
$compteurType=getArg('type');
$compteurPeriphId=getArg('id');
$CurrentValueArray=getValue($compteurPeriphId);
$CurrentValue=(float) $CurrentValueArray['value'];
$InitialValue=$CurrentValue;

$VarName='LastKnownDate'.$compteurPeriphId;
$LastKnownDate=loadVariable($VarName);
sdk_header('text/xml');
$xmloutput="<root>";

// gestion de la date en fonction du type de compteur
switch(strtolower($compteurType))
{
	case 'quotidien':
		$CurrentDate = date('d');
    break;
  case 'mensuel':
		$CurrentDate = date('m');
	  break;
  case 'annuel':
		$CurrentDate = date('Y');
	  break;
}
$xmloutput .="<LastKnownDate>".$LastKnownDate."</LastKnownDate>";
$xmloutput .="<CurrentDate>".$CurrentDate."</CurrentDate>";
$xmloutput .="<PreviousValue>".$InitialValue."</PreviousValue>";

//comparaison de date pour savoir si il faut reseter le compteur
if ($LastKnownDate <> $CurrentDate) 
{
 $CurrentValue=0;
 saveVariable($VarName,$CurrentDate);
}

// gestion des actions
switch($action)
{
	case 'add':
    $ValueToAdd=getValue($_GET['AddId']);
		$CurrentValue = $CurrentValue +  $ValueToAdd['value'];
    break;
	case 'inc':
		$CurrentValue = $CurrentValue + 1;
    $xmloutput .="<action>";
    $xmloutput .="increment done";
    $xmloutput .="</action>";  
    $xmloutput .="<NewValue>".$CurrentValue."</NewValue>";
    $xmloutput .="</root>";
    echo $xmloutput;
    break;
  case 'dec':
		$CurrentValue = $CurrentValue - 1;
    $xmloutput .="<action>";
    $xmloutput .="decrement done";
    $xmloutput .="</action>";
    $xmloutput .="<NewValue>".$CurrentValue."</NewValue>";
    $xmloutput .="</root>";
    echo $xmloutput;
	  break;
  case 'reset':
		$CurrentValue=0;
    $xmloutput .="<action>";
    $xmloutput .="reset done";
    $xmloutput .="</action>";
    $xmloutput .="</root>";
    echo $xmloutput;
	  break;
  case 'read':
    $xmloutput .="<value>";
    $xmloutput .="$CurrentValue";
    $xmloutput .="</value>";
    $xmloutput .="</root>";
    echo $xmloutput;
	  break;
}

// Mise à jour du compteur
if ($CurrentValue<>$InitialValue)
{
  setValue($compteurPeriphId,(float) $CurrentValue);
}

?>