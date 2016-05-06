<?php

// based on http://www.phpsources.org/scripts312-PHP.htm
$limits= array('/12/21'=>'Hiver',
         '/09/21'=>'Automne',
         '/06/21'=>'Eté',
         '/03/21'=>'Printemps',
         '/01/01'=>'Hiver');
$adate = date('Y/m/d');
sdk_header('text/xml');
foreach ($limits AS $key => $value) 
      {
         $limit=date('Y').$key;
         if (strtotime($adate)>=strtotime($limit)) 
            {
          
          echo "<root>";
          echo "<date>".utf8_encode($adate)."</date>";
          echo "<saison>".utf8_encode($value)."</saison>";
          echo "</root>";
          break;

            }
      }
   


?>