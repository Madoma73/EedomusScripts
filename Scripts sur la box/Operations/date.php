<?php
#
#
#set_value
$typejour=date('w')%2;
$parite=date('d')%2;
sdk_header('text/xml');
          
echo "<root>";
echo "<date>";
echo "<jour>".date('d')."</jour>";
echo "<typejour>".$typejour."</typejour>";
echo "<parite>".$parite."</parite>";
echo "<semaine>".date('W')."</semaine>";
echo "<mois>".date('m')."</mois>";
echo "<annee>".date('Y')."</annee>";
echo "</date>";
echo "</root>";
?>