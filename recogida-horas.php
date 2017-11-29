ç<?php
require_once('header.php');
$semana_activa = date('W');
?>

    <div class="page-header recogida-horas">
        <a class="logo" href="index.php">
            <h3>Recogida horas diarias (semana <?=$semana_activa?>)</h3>
        </a>
    </div>

<?php

include 'lib/database.php';
$pdo = Database::connect('stack_bbgest');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//SQL lista usuarios
$sql_usuarios = "select id, nombre from usuarios order by nombre asc";
$q_usuarios = $pdo->prepare($sql_usuarios);
$q_usuarios->execute(array());
$data_usuarios = $q_usuarios->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="row">
    <form id="input_horas" method="post">
        <div class="col-md-4">
            <div class="form-group">
                <label for="deli_camp">Trabajador</label>
                <select id="dusuario" class="form-control" name="dusuario" required>
                    <?php
                    foreach ($data_usuarios as $usuario):
                        ?>
                        <option value="<?= $usuario['id'] ?>"><?= $usuario['nombre'] ?></option>
                        <?php
                    endforeach;
                    ?>
                </select>
            </div>
            <!-- Proyecto -->
            <div class="form-group">
                <label for="dproyecto">Proyecto</label>
                <input type="text" autocomplete="off" id="dproyecto" name="dproyecto" placeholder="Nombre proyecto" class="form-control input-md">
                <input type="hidden" id="did_proyecto" name="did_proyecto" class="form-control input-md"">
            </div>
            <!-- Deliverable -->
            <div class="form-group">
                <label for="ndeliverable">Deliverable</label>
                <input type="text" id="ndeliverable" name="ndeliverable" placeholder="Deliverable" class="form-control input-md">
            </div>
            <!-- Fecha -->
            <div class="form-group">
                <label for="dfecha">Fecha</label>
                <input id="dfecha" name="dfecha" placeholder="dd-mm-yyyy" class="form-control input-md date" type="text">
            </div>
            <!-- Horas -->
            <div class="form-group">
                <label for="nhoras">Horas</label>
                <input type="text" id="nhoras" name="nhoras" placeholder="hh" class="form-control input-md">
            </div>
            <button type="submit" class="btn btn-primary btn-md" id="insertar-horas">
                Añadir
            </button>
        </div>
    </form>
</div>
<br><br>
<div class="row">
    <div class="col-md-4">
        <div id="horas_guardadas"></div>
    </div>
</div>

<?php
Database::disconnect();
include 'lib/footer.php';
?>