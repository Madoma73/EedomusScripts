<?php

// script créé par Pierre Pollet pour eedomus
// librement inspiré du script netatmo écrit par Connected Object 
// généré à partir des données Flower Power récupérées via Oauth

// encodage iso-8859-1 pour les accents

// Version 1 / 25 septembre 2014		/ 1ère version disponible


$GLOBALS['api_url']  = 'https://apiflowerpower.parrot.com/';
$api_url = 'https://apiflowerpower.parrot.com/';
$ClientId ='<CLIENT_ID>';
$ClientSecret='<CLIENT_SECRET>';
$UserName ='<USERNAME>';
$UserPassword='<PASSWORD>';
$access_token='';
$refresh_token='';
$GLOBALS['plants']=array();

// on reprend le dernier refresh_token seulement s'il correspond au même code
$refresh_token = loadVariable('refresh_token');
// problème si le script est utilisé alors que le refresh token est lui aussi expiré
$expire_time = loadVariable('expire_time');
// s'il n'a pas expiré, on peut reprendre l'access_token
if (time() < $expire_time)
 {
   $access_token = loadVariable('access_token');
   //echo "re-utilisation du token existant";
 }

if ($access_token == '')
{
  if (strlen($refresh_token) > 1)
  {
    // on peut juste rafraichir le token
    $grant_type = 'refresh_token';
    $postdata = 'grant_type='.$grant_type.'&refresh_token='.$refresh_token;
    $url=$api_url;
  }
  else
  {
    // 1ère utilisation aprés obtention du code
    $grant_type = 'password';
    $postdata = 'grant_type='.$grant_type.'&username='.$UserName.'&password='.$UserPassword.'&client_id='.$ClientId.'&client_secret='.$ClientSecret;
    $url=$api_url;
  }

  $response = httpQuery($url.'user/v1/authenticate', 'POST', $postdata);
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
  
}  
// récupération des données flower power
 
 function sdk_flowerPower_query($request, $method = 'GET', $post = NULL, $return_xml = true)
{
  $access_token= loadVariable('access_token');
  
  if ($post == '')
  {
    $response = httpQuery($GLOBALS['api_url'].$request."?access_token=".$access_token, 'GET',NULL,NULL ,array( 'Authorization: Bearer ' . $access_token ));
    //echo  "GET: ". $GLOBALS['api_url'].$request."?access_token=".$access_token;
  }
	else
  {
     $response = httpQuery($GLOBALS['api_url'].$request."?access_token=".$access_token, 'POST');
     //echo  "GET: ". $GLOBALS['api_url'].$request."?access_token=".$access_token;
  }
  
  $json = sdk_json_decode($response);
	
	if ($return_xml)
	{
    
		// permet d'avoir une mise en forme plus lisible dans un browser
		sdk_header('text/xml');	
    
    if ($request=='sensor_data/v3/sync')
    {
        foreach ($json["locations"] as $item) 
          {
            $id=$item["location_identifier"];
            $GLOBALS['plants'][$id] = $item["plant_nickname"];
          }
    }
    else    
    { 
      //echo var_dump($json);
      echo "<root>";
      foreach ($json["locations"] as $item) 
      {   
          $id=$item["location_identifier"];
          $percentage_temperature1=$item["air_temperature"]["gauge_values"]["current_value"]-$item["air_temperature"]["gauge_values"]["min_threshold"];
          $percentage_temperature2=$item["air_temperature"]["gauge_values"]["max_threshold"]-$item["air_temperature"]["gauge_values"]["min_threshold"];
          $percentage_temperature=$percentage_temperature1/$percentage_temperature2;
          
          $percentage_light1=$item["light"]["gauge_values"]["current_value"]-$item["light"]["gauge_values"]["min_threshold"];
          $percentage_light2=$item["light"]["gauge_values"]["max_threshold"]-$item["light"]["gauge_values"]["min_threshold"];
          $percentage_light=$percentage_light1/$percentage_light2;
          
          $percentage_soil1=$item["soil_moisture"]["gauge_values"]["current_value"]-$item["soil_moisture"]["gauge_values"]["min_threshold"];
          $percentage_soil2=$item["soil_moisture"]["gauge_values"]["max_threshold"]-$item["soil_moisture"]["gauge_values"]["min_threshold"];
          $percentage_soil=$percentage_soil1/$percentage_soil2;
          
          $percentage_fertilizer1=$item["fertilizer"]["gauge_values"]["current_value"]-$item["fertilizer"]["gauge_values"]["min_threshold"];
          $percentage_fertilizer2=$item["fertilizer"]["gauge_values"]["max_threshold"]-$item["fertilizer"]["gauge_values"]["min_threshold"];
          $percentage_fertilizer=$percentage_fertilizer1/$percentage_fertilizer2;
          
          echo "<plant name=\"".$GLOBALS['plants']["$id"]."\">";
          echo "<identifier>".$item["location_identifier"]."</identifier>";
          
          echo "<air_temperature>";
            echo "<status>".$item["air_temperature"]["status_key"]."</status>";
            echo "<instruction>".$item["air_temperature"]["instruction_key"]."</instruction>";
            echo "<current_value>".round($item["air_temperature"]["gauge_values"]["current_value"],1)."</current_value>";
            echo "<current_value_percentage>".round($percentage_temperature*100,1)."</current_value_percentage>";
            echo "<min_threshold>".$item["air_temperature"]["gauge_values"]["min_threshold"]."</min_threshold>";
            echo "<max_threshold>".$item["air_temperature"]["gauge_values"]["max_threshold"]."</max_threshold>";
          echo "</air_temperature>";
          
          echo "<light>";
            echo "<status>".$item["light"]["instruction_key"]."</status>";
            echo "<instruction>".$item["light"]["instruction_key"]."</instruction>";
            echo "<current_value>".round($item["light"]["gauge_values"]["current_value"],1)."</current_value>";
            echo "<current_value_percentage>".round($percentage_light*100,1)."</current_value_percentage>";
            echo "<min_threshold>".$item["light"]["gauge_values"]["min_threshold"]."</min_threshold>";
            echo "<max_threshold>".$item["light"]["gauge_values"]["max_threshold"]."</max_threshold>";
          echo "</light>";
          
          echo "<soil_moisture>";
            echo "<status>".$item["soil_moisture"]["instruction_key"]."</status>";
            echo "<instruction>".$item["soil_moisture"]["instruction_key"]."</instruction>";
            echo "<current_value>".round($item["soil_moisture"]["gauge_values"]["current_value"],1)."</current_value>";
            echo "<current_value_percentage>".round($percentage_soil*100,1)."</current_value_percentage>";
            echo "<min_threshold>".$item["soil_moisture"]["gauge_values"]["min_threshold"]."</min_threshold>";
            echo "<max_threshold>".$item["soil_moisture"]["gauge_values"]["max_threshold"]."</max_threshold>";
          echo "</soil_moisture>";  
            
          echo "<fertilizer>";
            echo "<status>".$item["fertilizer"]["instruction_key"]."</status>";
            echo "<instruction>".$item["fertilizer"]["instruction_key"]."</instruction>";
            echo "<current_value>".round($item["fertilizer"]["gauge_values"]["current_value"],1)."</current_value>";
            echo "<current_value_percentage>".round($percentage_fertilizer*100,1)."</current_value_percentage>";
            echo "<min_threshold>".$item["fertilizer"]["gauge_values"]["min_threshold"]."</min_threshold>";
            echo "<max_threshold>".$item["fertilizer"]["gauge_values"]["max_threshold"]."</max_threshold>";
          echo "</fertilizer>";
          
          echo "</plant>";
      }
      echo "</root>" ;
     }
    
	}
	else
	{ 
		return "not supported";
	}         
}

//sdk_flowerPower_query('/user/v4/profile','');
sdk_flowerPower_query('sensor_data/v3/sync');
sdk_flowerPower_query('sensor_data/v3/garden_locations_status');

?>