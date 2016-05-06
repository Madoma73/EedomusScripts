<?php
   $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>";      
   //***********************************************************************************************************************
   // V1.0 : Script qui fournit l'etat des ouvertures parametrees et le message vocal associe
   //*************************************** API eedomus ******************************************************************
   //*************************************** Messages personnels***********************************************************
   $msg_allclosed = "Apres vérification, tout est bien fermé";
   $msg_open = "Je detecte que ";
   $tabouvertures=array();
      
   // recuperation des ID depuis la requete
   $periphs=getArg("periphIds", $mandatory = true, $default = '');
   $resultPeriphId=getArg("resultPeriphId", $mandatory = false, $default = '');
   $tabPeriphs=explode(",", $periphs);

   //reset de l'indicateur 'portes ouverte'
   if ($resultPeriphId) 
   {
      SetValue($resultPeriphId, 0);
   } 
   
   // recuperation du nom des peripheriques
   foreach($tabPeriphs as $periphId)
   {
     $urlValue =  "http://localhost/api/get?action=periph.caract&periph_id=$periphId";
     $arrValue = sdk_json_decode(utf8_encode(httpQuery($urlValue,'GET')));
     $name=utf8_decode($arrValue["body"]["name"]); 
     $tabouvertures[]=array("NAME" => $name, "API" => $periphId, "ETAT" => 0);
   }

   //**********************************************************************************************************************
   $xml .= "<OUVERTURES>";
   $idoors = 1;
   $nbouvert = 0;
   $annonce = $msg_allclosed;
   foreach($tabouvertures as $ouvertures) {
      $arrValue =  getValue($ouvertures["API"]);
      if ($arrValue["value"] <> 0) {
         $ouvertures["ETAT"] = 1;
         $nbouvert++;
         if ($nbouvert == 1) {
            $annonce = $msg_open.$ouvertures["NAME"];
         }
         else {
            $annonce = $annonce." et ".$ouvertures["NAME"];
         }
      }
      $xml .= "<OUVERTURE_".$idoors."><TYPE>".$ouvertures["NAME"]."</TYPE>";
      $xml .= "<ETAT>".$ouvertures["ETAT"]."</ETAT></OUVERTURE_".$idoors.">\n";
      $idoors++;
   }
   if ($nbouvert == 1) {
      $annonce .= " est ouverte.";
   } else if ($nbouvert > 1) {
      $annonce .= " sont ouvertes.";    
   }
   
   if (($nbouvert > 0) && $resultPeriphId) 
   {
      SetValue($resultPeriphId, 100);
   }
   
       
   $xml .= "<MESSAGE>".$annonce."</MESSAGE>";
   $xml .= "</OUVERTURES>";
   sdk_header('text/xml');
   echo $xml;
?>