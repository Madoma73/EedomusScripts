<?php
/*************************************************************************************/
/*         ### Recupération des horaires de train de la Gare SNCF ###                */
/*                                                                                   */
/*                     Développement par eedomusbox@gmail.com                        */
/*                            Version 2.0                                            */
/*************************************************************************************/
// Chargement des données et fonctions
chdir(__DIR__);	require_once( __DIR__.'/include/variable.php' ); require_once( __DIR__.'/include/fonction.php' );

/*************************************** API eedomus ********************************/
$periph_id = '393249'; 

// Identifiants de l'API SNCF https://data.sncf.com/api/fr/documentation
$api_sncf_user = '302109e0-b900-420c-9b66-aaaaaaaaaaa';
$api_sncf_mdp = '';

// Initialiation des variables
// Pour trouver l'id de la ville rechercher "id" dans la requete suivante https://api.sncf.com/v1/coverage/sncf/places?q=LYON
$VilleDepart = 'admin:139203extern';
$VilleArrive = 'admin:117905extern';
$result = '';

// Récupération des paramères de la requète : 2 methodes, une Web et une Raspberry
if (isset($_GET['trajet']))  { $SensTrajet = $_GET['trajet'];     $rChariot = "<BR>";} 
						else { $SensTrajet = $_SERVER['argv'][1]; $rChariot = "\n";}
 
//******************************** Contruction du trajet *****************************/
switch ($SensTrajet) {
	case 'a':
		$gareDepart = $VilleDepart;
		$gareArrive = $VilleArrive;
		break;
	case 'r':
		$gareDepart = $VilleArrive;
		$gareArrive = $VilleDepart; 
		break;
	default:
		$gareDepart = $VilleDepart;
		$gareArrive = $VilleArrive;
		break;
}

 
//******************************** Date de heures d'exection *****************************/
$date = date("Ymd\TH:i"); 
echo $rChariot."Date et heure d'execution: ".$date.$rChariot;

//********************************  RÃ©cupÃ©ration des donnÃ©es *****************************/
$query = 'https://'.$api_sncf_user.'@api.sncf.com/v1/coverage/sncf/journeys?from='.$gareDepart.'&to='.$gareArrive.'&datetime='.$date.'&datetime_represents=departure&min_nb_journeys=4';
echo $rChariot.'Requete: '.$query.$rChariot.$rChariot;

$response=file_get_contents($query);
$json = json_decode($response, true);

// Boucle sur les trajets
echo $rChariot."************TRAINS*************";

foreach($json['journeys'] as $trains) // Lecture des trajets
	{  	
		$dateDepart = $trains['departure_date_time']; echo $rChariot.'Heure de depart: '.$dateDepart; // Date de départ
		$HeuredeDepart = substr($dateDepart,9,4);  // Heure de départ
		
		$dateArrive = $trains['arrival_date_time']; echo $rChariot."Heure d'arrive: ".$dateArrive; // Date d'arrivée
		$heuredarrive = substr($dateArrive,9,4);  // Heure d'arrivé
		$numtrain = $trains['sections'][1]['display_informations']['headsign']; //Numero du train
		echo $rChariot.'Numero de train: '.$numtrain;
		
		if ( $trains['status'] != "" )  // Cas du train en retard
			{ 	$retard = '';
				$text = '';
				echo $rChariot.'Statut du train: '.$trains['status'] ;
				if ($trains['status'] == 'NO_SERVICE'){ 
					echo $rChariot."************TRAINS*************";
					continue; //Affichage du statut}
				}
				$numdisrup = $trains['sections'][1]['display_informations']['links'][0]['id']; //Affichage de l'ID de retard
				echo $rChariot.'ID de retard: '.$numdisrup;
				
				//Récupération des retards
				$retards = $json['disruptions']; 
				//Recherche des retards
				foreach($retards as $retard)
				{   echo $rChariot.'ID de retard dans les retards: '.$retard['disruption_id'];
				
					if ( $retard['disruption_id']== $numdisrup )
						{ 	// Boucle sur l'ensemble des impacts du retard			
							foreach($retard['impacted_objects'][0]['impacted_stops'] as $impactStop)
								{ if ( substr($impactStop['base_departure_time'],0,4) == $HeuredeDepart )  // Recherche du lieu que l'on veut
									{   
										$updatetime = $impactStop['amended_departure_time']; // Nouvel Horaire
										echo $rChariot.'Nouvelle heure de depart: '; echo substr($updatetime,0,2);echo substr($updatetime,2,2);
										// Calcul du retard
										$retard =  ( substr($updatetime,0,2) * 60 + substr($updatetime,2,2)  ) - 
												   ( substr($HeuredeDepart,0,2) * 60 + substr($HeuredeDepart,2,2) ); //En minutes
										echo $rChariot.'Retard en mn: '.$retard; // Affichage du retard
										$text = '-'.$retard.'mn, '; 
										break;
									}
								}
						}
					
				}
			}
			else 
			{ $text = ', ';} // Train est à l'heure 
			
		    $result = $result.substr($HeuredeDepart,0,2).'h'.substr($HeuredeDepart,2,2).$text;
			echo $rChariot."************TRAINS*************";
	}
	
	$result = substr($result,0,-2);; // Supprime les deux derniers caractères
	echo $rChariot.$rChariot."Resultat: ".$result.$rChariot;  // Affichage des réultats
		
	$json_result = maj_periph($periph_id,$result); //Mise à jour Eedomus

/*
/usr/bin/php /var/www/eedomus/scripts/gare2.php a
*/
?>
