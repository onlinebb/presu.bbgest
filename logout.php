<?php
session_start();

access_log('cierre sesión usuario: '.$_SESSION['valid']);

function access_log($msg){
    $logfile = 'access.log';
    file_put_contents($logfile,date("Y-m-d H:i:s")." | ".$msg."\n",FILE_APPEND);
}

session_destroy();
header("Location:index.php");
