<?php

include 'database.php';

//Por lo general guardaremos cada semana los datos de la semana anterior
//Lo guardaremos el lunes a las 12pm, para tener los datos del viernes introducidos
$semanaIni = date('W') -1; //= 1
if(date('W')>1) {
    $semanaFin = date('W') -1;
}
else {
    $semanaFin = 1;
}

$year = date('Y');

$pdo = Database::connect('stack_bbgest');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>

<?php
    for ($i = $semanaIni; $i <= $semanaFin; $i++):
        //Sumatorio de Euros por semana y proyecto
        $sql_euros = "SELECT co.id_proyecto, sum(co.horas*(us.salario/1400)) as suma_euros from coeficiente co 
                      left join usuarios us on us.id=co.id_usuario 
                      where co.numSemana=".$i." and year=".$year." group by co.id_proyecto;";
        $q_euros = $pdo->prepare($sql_euros);
        $q_euros->execute(array());
        $data_euros = $q_euros->fetchAll(PDO::FETCH_ASSOC);
        echo "SEMANA ".$i."<br>";

        foreach ($data_euros as $euros):
            try {
                $sql_insert = "INSERT INTO coste_proyectos (id_proyecto,numSemana,year,coste) values(?, ?, ?, ?)";
                $q_insert = $pdo->prepare($sql_insert);
                echo $euros['id_proyecto']."---".$i."---".$year."----".$euros['suma_euros'].'<br>';
                $q_insert->execute(array($euros['id_proyecto'], $i, $year, $euros['suma_euros']));
            } catch (Exception $e) {
                Database::disconnect();
                echo "Error al actualizar los datos ".$e;
                die;
            }
        endforeach;
        ?>
    <?php
    endfor;
    ?>

<?php

Database::disconnect();
?>