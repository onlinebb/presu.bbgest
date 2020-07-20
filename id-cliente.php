<?php

include 'lib/database.php';

$pdo = Database::connect('bbgest');

$sql = "SELECT id_empresa, nombre FROM empresa order by nombre asc";

$pdo -> exec('SET NAMES utf8'); // METHOD #3

$q = $pdo->prepare($sql);
$result = array();
$q->execute();
$data = $q->fetchAll(PDO::FETCH_ASSOC);
Database::disconnect();

$search = array('.',' ','-','&','/','Á','É','Í','Ó','Ú','À','È','Ò','Ñ');
$replace = array('','','','','','A','E','I','O','U','A','E','O','N');

foreach ($data as $row) {

    echo $row['id_empresa'];
    echo ' | ';
    echo $row['nombre'];
    echo ' | ';

    $ref_cliente = strtoupper(substr(str_replace($search, $replace, $row['nombre']), 0, 3));
    $index = 0;
    while(!updateRef($ref_cliente, $row['id_empresa'], $index)) {
        $index++;
    }

    echo $ref_cliente;

    echo '<br>';
}

function updateRef($ref_cliente, $id, $index) {
    $pdo = Database::connect('presu14');
    $sql = "UPDATE empresa set ref_cliente = ? where id_empresa = ?";
    $q = $pdo->prepare($sql);

    try {

        if($index > 9) {
            $ref_cliente = substr_replace($ref_cliente, '', 2, 1);
            $ref_cliente = substr_replace($ref_cliente, $index, 1, 1);
        }
        else if($index > 0) {
            $ref_cliente = substr_replace($ref_cliente, $index, 2, 1);
        }

        echo $ref_cliente;
        $ok = $q->execute(array($ref_cliente, $id));

        if(!$ok)
            return false;

        return true;
    }
    catch (Exception $e) {
        //print $e;
        return false;
    }
}

