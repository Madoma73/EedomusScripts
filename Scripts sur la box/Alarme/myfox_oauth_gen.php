<?php

// script créé par Pierre Pollet pour eedomus
// librement inspiré du script netatmo écrit par Connected Object 
// généré à partir des données Myfox récupérées via Oauth
// basé sur l'auth  "Resource Owner Password Credentials Grant"
// une auth basée sur un "authorization code" serait mieux, mais ce n'est faisable
// que par Connected Objects (à cause de la callback)

// encodage iso-8859-1 pour les accents

// Version 1 / 22 mai 2014		/ 1ère version disponible


$GLOBALS['api_url']  = 'https://api.myfox.me:443/v2/';
$api_url = 'https://api.myfox.me:443/v2/';
$ClientId ='<votre client id>';
$ClientSecret='<votre client secret>';
$getToken_url = 'https://'.$ClientId.':'.$ClientSecret.'@api.myfox.me';
$UserName ='<username Myfox>'  ;
$UserPassword='<password Myfox>';
$access_token='';

// on reprend le dernier refresh_token seulement s'il correspond au même code
$refresh_token = loadVariable('refresh_token');
$expire_time = loadVariable('expire_time');
// s'il n'a pas expiré, on peut reprendre l'access_token
if (time() < $expire_time)
 {
   $access_token = loadVariable('access_token');
   //echo "re-utilisation du token existant";
 }


// on a déjà un token d'accés non expiré pour le code demandée
if ($access_token == '')
{
  if (strlen($refresh_token) > 1)
  {
    // on peut juste rafraichir le token
    $grant_type = 'refresh_token';
    $postdata = 'grant_type='.$grant_type.'&refresh_token='.$refresh_token;
    $url=$getToken_url;
  }
  else
  {
    // 1ère utilisation aprés obtention du code
    $grant_type = 'password';
    $postdata = 'grant_type='.$grant_type.'&username='.$UserName.'&password='.$UserPassword;
    $url=$getToken_url;
  }

  $response = httpQuery($url.'/oauth2/token', 'POST', $postdata);
  $params = sdk_json_decode($response);
  //var_dump($params);
  
    
  if ($params['error'] != '')
  {
    die("Erreur lors de l'authentification: <b>".$params['error'].'</b> (grant_type = '.$grant_type.')');
  }

  // on sauvegarde l'access_token et le refresh_token pour les authentifications suivantes
  if (isset($params['refresh_token']))
  {
    $access_token = $params['access_token'];
    saveVariable('access_token', $access_token);
    saveVariable('refresh_token', $params['refresh_token']);
    saveVariable('expire_time', time()+$params['expires_in']);

  }
  else if ($access_token == '')
  {
    die("Erreur lors de l'authentification");
  } 
  
  //extraire le code du site
  $siteId=loadVariable('siteId');
  if ($siteId == '') 
  {
    $request="client/site/items";
    $response = httpQuery($api_url.$request."?access_token=".$access_token, 'GET');
    $params2 = sdk_json_decode($response);
    $siteId=$params2['siteId'];
    saveVariable('siteId', $siteId);
   }
}  
 
 function sdk_myfox_query($request, $method = 'GET', $post = NULL, $return_xml = true)
{
  $access_token= loadVariable('access_token');
  if ($post == '')
  {
    $response = httpQuery($GLOBALS['api_url'].$request."?access_token=".$access_token, 'GET');
    //echo  "GET: ". $GLOBALS['api_url'].$request."?access_token=".$access_token;
  }
	else
  {
     $response = httpQuery($GLOBALS['api_url'].$request."?access_token=".$access_token, 'POST');
     echo  "GET: ". $GLOBALS['api_url'].$request."?access_token=".$access_token;
  }
  
  $json = sdk_json_decode($response);
		
	if ($return_xml)
	{
		// permet d'avoir une mise en forme plus lisible dans un browser
		sdk_header('text/xml');
		echo jsonToXML($response);
	}
	else
	{
		return $json;
	}         
}

switch($_GET['action'])
{
	case 'api_get':
		$query = getArg('query');
		sdk_myfox_query($query,'');
    break;
  case 'api_post':
    $query = getArg('query');
	  sdk_myfox_query($query,'POST');
	  break;
}
?>