<?

// Script de pilotage ampli Pioneer VSX
// réalisé par Connected Object
// vous pouvez améliorer ce script et en faire profiter la communauté eedomus

/*Volume:

    VD = VOLUME DOWN
    MZ = MUTE ON/OFF
    VU = VOLUME UP
    ?V = QUERY VOLUME

Power control:

    PF = POWER OFF
    PO = POWER ON
    ?P = QUERY POWER STATUS

Input selection

    05FN = TV/SAT
    01FN = CD
    03FN = CD-R/TAPE
    04FN = DVD
    19FN = HDMI1
    05FN = TV/SAT
    00FN = PHONO
    03FN = CD-R/TAPE
    26FN = HOME MEDIA GALLERY(Internet Radio)
    15FN = DVR/BDR
    05FN = TV/SAT
    10FN = VIDEO 1(VIDEO)
    14FN = VIDEO 2
    19FN = HDMI1
    20FN = HDMI2
    21FN = HDMI3
    22FN = HDMI4
    23FN = HDMI5
    24FN = HDMI6
    25FN = BD
    17FN = iPod/USB
    FU = INPUT CHANGE (cyclic)
    ?F = QUERY INPUT
*/

$GLOBALS['ip'] = getArg('ip');
$GLOBALS['port'] = getArg('port');


function sdk_conv_vol($input)
{
  if($input == '81')
    return '000';

  $output = ((80-$input)*2+1);
  if(strlen($output) < 3)
    return '0'.$output;
  else
    return $output;
}

function sdk_vol_db($input)
{
  $volume = str_replace('VOL', '', $input);

  $volume = 80 - sdk_sub($volume, 1)/2;
  return $volume;
}

function sdk_sub($a,$b)
{
    return $a - $b;
}

function sdk_get_vol()
{
  $output = netSend($GLOBALS['ip'], $GLOBALS['port'], '?V');
  return $output[0];
}

if ($_GET['power'] != '')
{
  if ($_GET['power'] == 'off')
  {
    $output = netSend($GLOBALS['ip'], $GLOBALS['port'], 'PF');
    if($output[0] == 'PWR1')
        $exit = 'OK';
    else
       $exit = 'ECHEC - '.$output[0];
  }
  else if ($_GET['power'] == 'on')
  {
    $output = netSend($GLOBALS['ip'], $GLOBALS['port'], 'PO');
  }
  $output = sdk_get_vol();
  $volume = sdk_vol_db($output);
}
else if ($_GET['input'] != '')
{
  $output = netSend($GLOBALS['ip'], $GLOBALS['port'], $_GET['input']);
  $output = sdk_get_vol();
  $volume = sdk_vol_db($output[0]);
}
else if ($_GET['vol_offset'] != '')
{
  $output = sdk_get_vol();
  $volume = sdk_vol_db($output);
  
  if ($_GET['vol_offset'] > 0)
  {
    $c = 'VU';
  }
  else
  {
    $c = 'VD';
  }
  
  for ($i = 0; $i < abs($_GET['vol_offset']); $i++)
  {
    $output = netSend($GLOBALS['ip'], $GLOBALS['port'], $c);
  }
  
  $volume = $volume + $_GET['vol_offset'];
}
else if ($_GET['volume'] != '')
{
  $output = netSend($GLOBALS['ip'], $GLOBALS['port'], sdk_conv_vol($_GET['volume']).'VL');

  $volume = sdk_vol_db($output[0]);
}
else if ($_GET['mute'] != '')
{
  if($_GET['mute']== 'on')
    $output = netSend($GLOBALS['ip'], $GLOBALS['port'], 'MO');
  else 
    $output = netSend($GLOBALS['ip'], $GLOBALS['port'], 'MF');

  $output = sdk_get_vol();
  $volume = sdk_vol_db($output);
}
else
{
  $output = sdk_get_vol();
  $volume = $output;//sdk_vol_db($output[0]);
}

echo '{"status":"'.$exit.'", "volume":"'.$volume.'"}';

?>