<?php 
/*************************************************************************************/
/*                     ###   Fonction Utile eedomus      ###                         */
/*                                                                                   */
/*                     Développement par eedomusbox@gmail.com                        */
/*                            Version 1.0                                            */
/*************************************************************************************/


function maj_periph( $periphID, $val )
{
	global $api_user;
	global $IPeedomus;
	global $api_secret;
	global $IPLocal;
	global $rChariot;
	global $Ip;
	global $value_date;

// Conversion du résultat
	$val = str_replace('#',' ', $val);  // Remplacement par blanc
	$val = str_replace('[',' ', $val);  // Remplacement par blanc
	$val = str_replace('=',' ', $val);  // Remplacement par blanc
	$val = str_replace(']',' ', $val);  // Remplacement par blanc
	$val = str_replace('+',' ', $val);  // Remplacement par blanc
	$val = str_replace('*',' ', $val);  // Remplacement par blanc
	$val = str_replace('&',' ', $val);  // Remplacement par blanc
	$val = str_replace(';',' ', $val);  // Remplacement par blanc
	$val = str_replace('°',' ', $val);  // Remplacement par blanc
	$val = rawurlencode($val);
	
// Adresse IP : Locale ou globale?
	if ( $Ip == '' ) { if ( exec("ping -c 1 192.168.0.14") == '') { $Ip = $IPeedomus;} else {$Ip = $IPLocal;} }
	
//Contruction de l'url Local
    $url = "http://".$Ip."/set?action=periph.value"; 
	$url .= "&api_user=$api_user";
	$url .= "&api_secret=$api_secret";
	$url .= "&periph_id=$periphID";
	$url .= "&value=$val";
	
	echo $rChariot.$value_date;
	if ( $value_date != '') { $value_date = str_replace(' ','%20', $value_date); $url .= "&value_date=$value_date";} //
	
	echo $rChariot.$url;
	
// Mis à jour du périphérique
	$result = @file_get_contents($url);
	if (strpos($result, '"success": 1') == false)
		 { echo $rChariot."Une erreur est survenue lors de la mise àjour [".$result."]".$rChariot;}
	else { echo $rChariot."Mis Ã  jour OK";}
	
	return $result;
} 

function supprimer_accents($str, $encoding='ISO-8859-1')
{
    // transformer les caractères accentués en entitÃ©s HTML
    $str = htmlentities($str, ENT_NOQUOTES, $encoding);
  
    // remplacer les entités HTML pour avoir juste le premier caractères non accentués
    // Exemple : "&ecute;" => "e", "&Ecute;" => "E", "Ãƒ " => "a" ...
    $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
  
    // Remplacer les ligatures tel que : Å’, Ã† ...
    // Exemple "Ã…â€œ" => "oe"
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    // Supprimer tout le reste
    $str = preg_replace('#&[^;]+;#', '', $str);
    // Supprimer les espaces
    $str = preg_replace('/\s/', '-', $str);
  
    return $str;
}

?>
