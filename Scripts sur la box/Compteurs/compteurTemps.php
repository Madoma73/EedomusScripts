<?
// script créé par Pierre Pollet pour eedomus
// pour gérer des compteurs
// Version 1   / 17 Juillet 2014		/ 1ère version disponible


// paramètres de configuration
$action = getArg('action');
$compteurType=getArg('type');
$compteurPeriphId=getArg('id');

$TimeNow=microtime(true);

// Définition du noms des variables en fct de l'ID du périphérique compteur
$DateName='LastKnownDate'.$compteurPeriphId;
$PreviousTimeName='StartTime'.$compteurPeriphId;
$CompteurValueName='CompteurValue'.$compteurPeriphId;
$CompteurStateName='CompteurState'.$compteurPeriphId;

//Chargement des variables    
$LastKnownDate=loadVariable($DateName);
$CompteurValue=loadVariable($CompteurValueName);
$CompteurState=loadVariable($CompteurStateName);
$PreviousTime=LoadVariable($PreviousTimeName);
$InitialValue=$CompteurValue;

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
 $CompteurValue=0;
 saveVariable($DateName,$CurrentDate);
}

// gestion des actions
switch(strtolower($action))
{
	case 'start':
    saveVariable($PreviousTimeName,$TimeNow);
    $CompteurState=1;
    $xmloutput .="<action>";
    $xmloutput .="start counting";
    $xmloutput .="</action>";
    $xmloutput .="</root>";
    echo $xmloutput;
    break;
    
	case 'stop':
		//Calcul du temps à ajouter
    $CompteurState=0;
    $TimeElapsed=round($TimeNow-$PreviousTime,0);
    $CompteurValue = $CompteurValue+ $TimeElapsed;
    $xmloutput .="<action>";
    $xmloutput .="stop counting:".$TimeToAdd."s";
    $xmloutput .="</action>";  
    $xmloutput .="<NewValue>".$CompteurValue."</NewValue>";
    $xmloutput .="</root>";
    echo $xmloutput;
    
    //par securite, sauvegarde du temps courant dans la variable.
    saveVariable($PreviousTimeName,$TimeNow);
    break;
    
  case 'reset':
		$CompteurValue=0;
    $CompteurState=0;
    $xmloutput .="<action>";
    $xmloutput .="reset done";
    $xmloutput .="</action>";
    $xmloutput .="</root>";
    echo $xmloutput;
	  break;
    
  case 'read':
    //Calcul du temps à ajouter
    if ($CompteurState)
    {
      $TimeElapsed=round($TimeNow-$PreviousTime,0);
      $CompteurValue = $CompteurValue + $TimeElapsed;
      $xmloutput .="<action>";
      $xmloutput .="count still on going";
      $xmloutput .="</action>";
      // on continue à compter:
      saveVariable($PreviousTimeName,$TimeNow);
    }
    
    $xmloutput .="<value>".$CompteurValue."</value>";
    $xmloutput .="</root>";
    echo $xmloutput;
	  break;
    
  default:
    $xmloutput .="<action>";
    $xmloutput .=strtolower($action)." is an unknown action";
    $xmloutput .="</action>";
    $xmloutput .="</root>";
    echo $xmloutput;
	  break;
}


saveVariable($CompteurStateName,$CompteurState);
// Mise à jour du compteur
if ($CompteurValue<>$InitialValue)
{
  saveVariable($CompteurValueName,$CompteurValue);
  
}

?>