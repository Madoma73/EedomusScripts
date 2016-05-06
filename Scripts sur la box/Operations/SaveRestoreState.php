<?
// script créé par Pierre Pollet pour eedomus
// pour sauvegarde/restaurer des états
// Version 1   / 08 Novembre 2014		/ 1ère version disponible
// Version 1.1 / 17 Novembre 2014   / Ajout de la possibilité de sauvegarder la valeur vers un autre périphérique


// paramètres de configuration
$BackupPeriphId='';
$action = getArg('action');
$PeriphId=getArg('id');
$BackupPeriphId=$_GET['backup'];
$BackupOnPeriph=False;

// Définition du noms des variables en fct de l'ID du périphérique
$StateName='State'.$PeriphId;

if ($BackupPeriphId != '') $BackupOnPeriph=True;

sdk_header('text/xml');
$xmloutput="<root>";

// gestion des actions
switch(strtolower($action))
{
	case 'save':
    //Chargement des variables
    $ArrayValue=getvalue($PeriphId);
    $Value=$ArrayValue["value"];
    if  ($BackupOnPeriph)
    {
      setValue($BackupPeriphId, $Value);
    }
    else
    {
      saveVariable($StateName,$Value);
    }
    
    $xmloutput .="<action>";
    $xmloutput .="Value ".$Value." Saved";
    $xmloutput .="</action>";
    $xmloutput .="</root>";
    echo $xmloutput;
    break;
    
	case 'restore':
    if  ($BackupOnPeriph)
    {
        $ArrayValue=getvalue($BackupPeriphId);
        $Value=$ArrayValue["value"];
        setValue($PeriphId, $Value);
    }
    else
    {
        $Value=loadVariable($StateName);
        setValue($PeriphId, $Value);
    }

    $xmloutput .="<action>";
    $xmloutput .="Value ". $Value ." restored";
    $xmloutput .="</action>";  
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
?>