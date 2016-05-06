<?php

$connection = ssh2_connect('<@ip>', 22);

if (!ssh2_auth_password($connection, 'root', '<password>')) {
  die('Echec de l\'identification...');
}

$stream = ssh2_exec($connection, 'poweroff');
$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

// Enable blocking for both streams
stream_set_blocking($errorStream, true);
stream_set_blocking($stream, true);

echo "OnGoing Shutdown";

?>

