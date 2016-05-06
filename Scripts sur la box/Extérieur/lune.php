<?php

$ConsumerId="HLFEmZfOScKaSWMeakxbo";
$ConsumerSecret="fcgvNXEn7yT3uwB9bgu5LLYR1PMU6XtTI0A2Rib0";
$City="Crolles,France";
$ApiUrl = 'http://api.aerisapi.com/sunmoon/'.$City.'?client_id='.$ConsumerId.'&client_secret='.$ConsumerSecret;
$ApiUrl2= 'http://api.aerisapi.com/sunmoon/moonphases/'.$City.'?client_id='.$ConsumerId.'&client_secret='.$ConsumerSecret;

$CACHE_DURATION = 720; // minutes



$last_xml_success = loadVariable('last_xml_success');
if ((time() - $last_xml_success) / 60 < $CACHE_DURATION)
{
	sdk_header('text/xml');
	$cached_xml = loadVariable('cached_xml');
  $cached_xml = str_replace('<Cachestatus>0</Cachestatus>', '<Cachestatus>1</Cachestatus>', $cached_xml);
	echo $cached_xml;
	die();
}
else
{
 $response = httpQuery($ApiUrl);
 
 $responsePhase = httpQuery($ApiUrl2);
 $XmlResultPhase=jsonToXML($responsePhase);
 
 sdk_header('text/xml');
 $XmlResult=jsonToXML($response);
 $XmlResult = str_replace('</root>', '', $XmlResult);
 $XmlResult.='<phaseName>'.xpath($XmlResultPhase,'/root/response/response/name').'</phaseName>';
 $XmlResult.='<cached>';
 $XmlResult.='<Cachestatus>0</Cachestatus>';
 $XmlResult.='<CacheTime>'.date(DATE_RFC2822).'</CacheTime>';
 $XmlResult.='</cached></root>';

 #set last_xml_success
 saveVariable('last_xml_success', time());
  
 #set cached_xml
 saveVariable('cached_xml', $XmlResult);
 
 // XML de sortie
 echo $XmlResult;
 
}

?>