<?php session_start(); ?>
<?php
/**
 * Created by PhpStorm.
 * User: judit
 * Date: 2/04/14
 * Time: 10:31
 */

include 'database.php';

/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

if (isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case "searchClient":
            searchClient($_GET['text']);
            break;
        case "savePresu":
            savePresupuesto();
            break;
        case "loadPresu":
            loadPresupuesto($_GET['id']);
            break;
        case "copyPresu":
            copyPresupuesto($_POST['id'], $_POST['origen']);
            break;
        case "updatePresu":
            savePresupuesto(true);
            break;
        case "deletePresu":
            deletePresupuesto($_POST['id']);
            break;
        case "denyPresu":
            denegarPresupuesto($_POST['id']);
            break;
        case "searchCatalog":
            searchCatalog($_POST['text']);
            break;
        case "searchArchive":
            searchArchive($_POST['text']);
            break;
        case "getPO":
            getPO($_POST['id']);
            break;
        case "savePO":
            savePO($_POST['id'], $_POST['po-ref'], $_POST['presu-ref']);
            break;
        case "saveObservacionesFactura":
            saveObservacionesFactura($_POST['id'], $_POST['obs-text']);
            break;
        case "saveFact":
            saveFactura();
            break;
        case "updateFact":
            saveFactura(true);
            break;
        case "updateClienteFact":
            updateClienteFact();
            break;
        case "deleteFact":
            deleteFactura($_POST['id'], $_POST['presu']);
            break;
        case "cobrarFact":
            cobrarFactura($_POST['id'], $_POST['presu']);
            break;
        case "logExcel":
            logExcel();
            break;
        case "logExcelPerformance":
            logExcelPerformance();
            break;
        case "logExcelByOwner":
            logExcelByOwner();
            break;
        case "logExcelByProyecto":
            logExcelByProyecto();
            break;
        case "logExcelByClient":
            logExcelByClient();
            break;
        case "bbgest":
            bbgest();
            break;
        case "exportExcel":
            exportExcel($_GET['tipo']);
            break;
        case "searchProyecto":
            searchProyecto($_GET['text'], $_GET['cliente']);
            break;
        case "searchProyecto2":
            searchProyecto2($_GET['text']);
            break;
        case "saveHonorarios":
            saveHonorarios($_POST['id_cliente'], $_POST['honorarios']);
            break;
        case "uploadFiles":
            uploadFiles();
            break;
        case "updateHoras":
            updateHoras();
            break;
        case "updateSalario":
            updateSalario();
            break;
        case "updateCostes":
            updateCostes();
            break;
        case "saveHoras":
            saveHoras($_POST['dusuario'],$_POST['did_proyecto'],$_POST['ndeliverable'],$_POST['dfecha'],$_POST['nhoras']);
            break;
        case "altaCliente":
            altaCliente();
            break;
        case "updateCliente":
            updateCliente();
            break;
    }
}

/**
 * Buscar cliente que su nombre contenga el texto
 * @param $text
 */
function searchClient($text)
{
    $pdo = Database::connect();

    $sql = "SELECT * FROM empresa where nombre like ?";

    $pdo -> exec('SET NAMES utf8'); // METHOD #3

    $q = $pdo->prepare($sql);
    $q->bindValue(1, "%$text%", PDO::PARAM_STR);
    $result = array();
    $count = 0;
    $q->execute();
    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as $row) {

        $result[$count]['nombre'] = $row['nombre'];
        $result[$count]['direccion'] = $row['direccion'];
        $result[$count]['cp'] = $row['cp'];
        $result[$count]['cif'] = $row['cif'];
        $result[$count]['cp'] = $row['cp'];
        $result[$count]['id'] = $row['id_empresa'];
        $result[$count]['ref_cliente'] = $row['ref_cliente'];

        $count++;
    }
    print json_encode($result);

    Database::disconnect();
}

/**
 * Guardar presupuesto y conceptos
 */
function savePresupuesto($isUpdate = false)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $english = $_POST['english'];

    if (!$isUpdate) {

        //buscar si hay algun presupuesto para este cliente y sacar el último id
        $sql = "SELECT ref from presupuesto where ref like ? order by id desc";
        $q = $pdo->prepare($sql);

        $curYear = date('y');
        $q->bindValue(1, "PR$curYear%", PDO::PARAM_STR);
        $q->execute();
        $data = $q->fetch();

        if ($data) {
            $currentId = (int)(substr($data[0], 4, 3));
            $id = str_pad($currentId + 1, 3, "0", STR_PAD_LEFT);
        } else {
            $id = "001";
        }

        //construimos ref presupuesto
        //$ref_cliente = strtoupper(substr(str_replace(array('.',' ','-', '&', '/'),'',$_POST['cliente']), 0, 3));
        $ref_cliente = $_POST['ref_cliente'];
        $ref_presupuesto = "PR" . $curYear . $id . $ref_cliente;

        //guardar datos del presupuesto
        $sql = "INSERT INTO presupuesto (
                                    ref,
                                    fecha,
                                    id_empresa,
                                    ref_cliente,
                                    nombre_cliente,
                                    direccion_cliente,
                                    cif_cliente,
                                    cp_cliente,
                                    contacto_cliente,
                                    id_proyecto,
                                    nombre_proyecto,
                                    suma,
                                    autor,
                                    fecha_emision,
                                    english
                                  )
          values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $q = $pdo->prepare($sql);

        $fecha = date('Y-m-d', strtotime($_POST['fecha']));

        try {
            $q->execute(
                array(
                    $ref_presupuesto,
                    $fecha,
                    $_POST['empresa'],
                    $ref_cliente,
                    $_POST['cliente'],
                    $_POST['direccion'],
                    $_POST['cif'],
                    $_POST['cp'],
                    $_POST['contacto'],
                    $_POST['id_proyecto'],
                    $_POST['propuesta'],
                    str_replace(array('.',','),array('','.'),$_POST['suma']),
                    $_SESSION['valid'],
                    date('Y-m-d'),
                    $english)
            );
            $idPresu = $pdo->lastInsertId();
        } catch (Exception $e) {
            print $e;
        }

    } else {

        //actualizar datos del presupuesto
        //actualizar ref presupuesto si hemos modificado el cliente
        $nuevoCliente = false;
        if($_POST['empresa_orig'] != $_POST['empresa']) {
            $ref_cliente = $_POST['ref_cliente'];
            $ref_presupuesto = substr($_POST['ref_presu'], 0, 7).$ref_cliente;
        }
        else {
            $ref_cliente = $_POST['ref_cliente'];
            $ref_presupuesto = $_POST['ref_presu'];
        }
        //$ref_cliente = strtoupper(substr(str_replace(array('.',' ','-', '&', '/'),'',$_POST['cliente']), 0, 3));
        $idPresu = $_POST['id'];

        $sql = "UPDATE presupuesto SET fecha = ?,
                                   ref = ?,
                                   id_empresa = ?,
                                   ref_cliente = ?,
                                   nombre_cliente = ?,
                                   direccion_cliente = ?,
                                   cif_cliente = ?,
                                   cp_cliente = ?,
                                   contacto_cliente = ?,
                                   id_proyecto = ?,
                                   nombre_proyecto = ?,
                                   suma = ?,
                                   english = ?
          where id = ?";
        $q = $pdo->prepare($sql);

        $fecha = date('Y-m-d', strtotime($_POST['fecha']));

        try {
            $q->execute(
                array(
                    $fecha,
                    $ref_presupuesto,
                    $_POST['empresa'],
                    $ref_cliente,
                    $_POST['cliente'],
                    $_POST['direccion'],
                    $_POST['cif'],
                    $_POST['cp'],
                    $_POST['contacto'],
                    $_POST['id_proyecto'],
                    $_POST['propuesta'],
                    str_replace(array('.',','),array('','.'),$_POST['suma']),
                    $english,
                    $idPresu)
            );

        } catch (Exception $e) {
            //print $e;
        }

    }

    $output = array();
    $output['id_presu'] = $idPresu;
    $output['ref_presu'] = $ref_presupuesto;
    $output['ref_presu_orig'] = $_POST['ref_presu'];

    if ($isUpdate) {

        //borrar conceptos existentes primero

        $sql = "DELETE from concepto where id_presupuesto = ?";
        $q = $pdo->prepare($sql);

        $q->execute(array($idPresu));

        $sql = "SELECT GROUP_CONCAT(ref_factura SEPARATOR ', ') FROM factura WHERE presupuesto_asoc = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($_POST['ref_presu']));
        $data = $q->fetch();

        if($data && $data[0]) {
            //hay facturas asociadas al presu
            $output['facturas'] = $data[0];
        }
    }

    //guardar todos los conceptos

    $count = 1;
    $sql = "INSERT INTO concepto (
                                id_presupuesto,
                                id_concepto,
                                orden,
                                concepto,
                                concepto_subtitulo,
                                titulo1,
                                titulo2,
                                titulo3,
                                texto,
                                precio_concepto,
                                precio_concepto_subtitulo,
                                precio_titulo1,
                                precio_titulo2,
                                precio_titulo3,
                                precio_texto
                             )
        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $q = $pdo->prepare($sql);

    foreach ($_POST['conceptos'] as $item) {
        $q->execute(
            array(
                $idPresu,
                $count,
                !empty($item['orden']) ? $item['orden']:"",
                !empty($item['concepto']) ? $item['concepto']:"",
                !empty($item['concepto_subtitulo']) ? $item['concepto_subtitulo']:"",
                !empty($item['titulo1']) ? $item['titulo1']:"",
                !empty($item['titulo2']) ? $item['titulo2']:"",
                !empty($item['titulo3']) ? $item['titulo3']:"",
                !empty($item['texto']) ? $item['texto']:"",
                !empty($item['precio_concepto']) ? $item['precio_concepto']:0,
                !empty($item['precio_concepto_subtitulo']) ? $item['precio_concepto_subtitulo']:0,
                !empty($item['precio_titulo1']) ? $item['precio_titulo1']:0,
                !empty($item['precio_titulo2']) ? $item['precio_titulo2']:0,
                !empty($item['precio_titulo3']) ? $item['precio_titulo3']:0,
                !empty($item['precio_texto']) ? $item['precio_texto']:0
            )
        );

        $count++;
    }

    Database::disconnect();

    if($output) print json_encode($output);
    else print 1;
}

function copyPresupuesto($id, $origen = false)
{
    $id_origen = $id;

    $datosPresupuesto = loadPresupuesto($id);
    $conceptosPresupuesto = loadConceptos($id);

    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //buscar si hay algun presupuesto para este cliente y sacar el último id
    $sql = "SELECT ref from presupuesto where ref like ? order by id desc";
    $q = $pdo->prepare($sql);

    // ref presu origen
    if($origen) {
        $ref_presu_origen = $datosPresupuesto['ref'];
    }
    else {
        $ref_presu_origen = null;
    }

    $curYear = date('y');
    $q->bindValue(1, "PR$curYear%", PDO::PARAM_STR);
    $q->execute();
    $data = $q->fetch();

    if ($data) {
        $currentId = (int)(substr($data[0], 4, 3));
        $id = str_pad($currentId + 1, 3, "0", STR_PAD_LEFT);
    } else {
        $id = "001";
    }

    //construimos ref presupuesto
    $ref_cliente = $datosPresupuesto['ref_cliente'];
    $ref_presupuesto = "PR" . $curYear . $id . $ref_cliente;

    //guardar datos del presupuesto
    $sql = "INSERT INTO presupuesto (
                                ref,
                                fecha,
                                id_empresa,
                                ref_cliente,
                                nombre_cliente,
                                direccion_cliente,
                                cif_cliente,
                                cp_cliente,
                                contacto_cliente,
                                id_proyecto,
                                nombre_proyecto,
                                suma,
                                autor,
                                presu_origen,
                                fecha_emision
                              )
      values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $q = $pdo->prepare($sql);

    $fecha = date('Y-m-d');

    try {
        $q->execute(
            array(
                $ref_presupuesto,
                $fecha,
                $datosPresupuesto['id_empresa'],
                $ref_cliente,
                $datosPresupuesto['nombre_cliente'],
                $datosPresupuesto['direccion_cliente'],
                $datosPresupuesto['cif_cliente'],
                $datosPresupuesto['cp_cliente'],
                $datosPresupuesto['contacto_cliente'],
                $datosPresupuesto['id_proyecto'],
                $datosPresupuesto['nombre_proyecto'],
                $datosPresupuesto['suma'],
                $_SESSION['valid'],
                $ref_presu_origen,
                $fecha
            )
        );
        $idPresu = $pdo->lastInsertId();


        // ref presu origen
        if($origen) {
            //Presu origen ponemos fecha negociacion = fecha de la copia
            $sql = "UPDATE presupuesto SET fecha_negociacion = ?, estado='no aceptado', fecha_noaceptacion= ? where id = ?";
            $q = $pdo->prepare($sql);

            $q->execute(array($fecha, $fecha, $id_origen));
        }


    } catch (Exception $e) {
        print $e;
    }

    //guardar todos los conceptos

    $count = 1;
    $sql = "INSERT INTO concepto (
                                id_presupuesto,
                                id_concepto,
                                orden,
                                concepto,
                                concepto_subtitulo,
                                titulo1,
                                titulo2,
                                titulo3,
                                texto,
                                precio_concepto,
                                precio_concepto_subtitulo,
                                precio_titulo1,
                                precio_titulo2,
                                precio_titulo3,
                                precio_texto
                             )
        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $q = $pdo->prepare($sql);

    foreach ($conceptosPresupuesto as $item) {
        $q->execute(
            array(
                $idPresu,
                $count,
                !empty($item['orden']) ? $item['orden']:"",
                !empty($item['concepto']) ? $item['concepto']:"",
                !empty($item['concepto_subtitulo']) ? $item['concepto_subtitulo']:"",
                !empty($item['titulo1']) ? $item['titulo1']:"",
                !empty($item['titulo2']) ? $item['titulo2']:"",
                !empty($item['titulo3']) ? $item['titulo3']:"",
                !empty($item['texto']) ? $item['texto']:"",
                !empty($item['precio_concepto']) ? $item['precio_concepto']:0,
                !empty($item['precio_concepto_subtitulo']) ? $item['precio_concepto_subtitulo']:0,
                !empty($item['precio_titulo1']) ? $item['precio_titulo1']:0,
                !empty($item['precio_titulo2']) ? $item['precio_titulo2']:0,
                !empty($item['precio_titulo3']) ? $item['precio_titulo3']:0,
                !empty($item['precio_texto']) ? $item['precio_texto']:0
            )
        );

        $count++;
    }

    Database::disconnect();

    print $idPresu;
}

/**
 * Carga los datos del presupuesto
 * @param $id
 */
function loadPresupuesto($id)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * from listado_presus where id = ?";
    $q = $pdo->prepare($sql);

    $q->execute(array($id));
    $data = $q->fetch();

    Database::disconnect();

    return $data;
}

/**
 * Carga los conceptos del presupuesto
 * @param $id
 */
function loadConceptos($id)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * from concepto where id_presupuesto = ?";
    $q = $pdo->prepare($sql);

    $q->execute(array($id));
    $data = $q->fetchAll();

    Database::disconnect();

    return $data;
}

/**
 * Elimina el presupuesto y sus conceptos asociados
 * @param $id
 */
function deletePresupuesto($id)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Eliminar presupuesto
    $sql = "DELETE from presupuesto where id = ?";
    $q = $pdo->prepare($sql);

    $q->execute(array($id));

    //Eliminar conceptos
    $sql = "DELETE from concepto where id_presupuesto = ?";
    $q = $pdo->prepare($sql);

    $q->execute(array($id));

    Database::disconnect();

    return 1;
}

/**
 * Cambia el estado del presupuesto a "no aceptado"
 * @param $id
 */
function denegarPresupuesto($id)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Cambiar estado presupuesto
    $sql = "UPDATE presupuesto SET estado = 'no aceptado', fecha_noaceptacion = ? where id = ?";
    $q = $pdo->prepare($sql);

    $q->execute(array(date('Y-m-d'),$id));

    Database::disconnect();

    return 1;
}

/**
 * Buscar conceptos en el catálogo que contengan el texto
 * @param $text
 */
function searchCatalog($text)
{
    $pdo = Database::connect();

    $sql = "SELECT * FROM catalogo where concepto like ?";
    $q = $pdo->prepare($sql);
    $q->bindValue(1, "%$text%", PDO::PARAM_STR);
    $q->execute();
    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    $rows_per_page = 4;
    $total_records = count($data);
    $total_pages = ceil($total_records / $rows_per_page);
    $current_page = 1;
    $counter = 1;

    foreach ($data as $row) {
        ?>

        <tr class="cat-concepto <?php if($current_page > 1): echo 'hide'; endif; ?>" data-pagina="<?php echo $current_page; ?>" data-paginas="<?php echo $total_pages; ?>">
            <td>
                <button title="Añadir" class="add btn btn-primary btn-sm" data-id="<?php echo $row['id'] ?>" data-concepto="<?php echo $row['concepto'] ?>" data-precio_concepto="<?php echo $row['precio'] ?>">
                    <span class="glyphicon glyphicon-plus-sign"></span>
                </button><?php echo $row['concepto'] ?>
            </td>
            <td>
                <div class="check-column">
                    <?php echo $row['precio'] ?>
                </div>
            </td>
        </tr>

        <?php

        if($counter >= $rows_per_page)
        {
            $current_page++;
            $counter = 1;
        }
        else
            $counter++;
    }


    Database::disconnect();
}

/**
 * Buscar conceptos en los presupuestos existentes que contengan el texto
 * @param $text
 */
function searchArchive($text)
{
    $pdo = Database::connect();

    //$sql = "SELECT * FROM concepto where concat(concepto,concepto_subtitulo,titulo1,titulo2,titulo3,texto) like ?";
    $sql = "SELECT * FROM presupuesto p 
            left join concepto c on c.id_presupuesto=p.id 
            where concat(c.concepto,c.concepto_subtitulo,c.titulo1,c.titulo2,c.titulo3,c.texto,p.ref,p.ref_cliente,p.nombre_cliente, p.contacto_cliente, p.nombre_proyecto) like ? 
            order by p.id, c.id_concepto";
    $q = $pdo->prepare($sql);
    $q->bindValue(1, "%$text%", PDO::PARAM_STR);

    try {
        $q->execute();
    }
    catch (Exception $e) {
        print $e;
    }

    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    $rows_per_page = 2;
    $total_records = count($data);
    $total_pages = ceil($total_records / $rows_per_page);
    $current_page = 1;
    $counter = 1;

    foreach ($data as $row) {
        ?>

        <tr class="cat-concepto <?php ///if($current_page > 1): echo 'hide'; endif; ?>" data-pagina="<?php //echo $current_page; ?>" data-paginas="<?php //echo $total_pages; ?>">
            <td>
                <!--<div class="checkbox">
                    <label>
                        <input class="check-concepto" type="checkbox" data-id="<?php echo $row['id_concepto'] ?>"
                               data-concepto="<?php echo $row['concepto'] ?>"
                               data-precio_concepto="<?php echo $row['precio_concepto'] ?>"
                               data-concepto_subtitulo="<?php echo $row['concepto_subtitulo'] ?>"
                               data-precio_concepto_subtitulo="<?php echo $row['precio_concepto_subtitulo'] ?>"
                               data-titulo1="<?php echo $row['titulo1'] ?>"
                               data-precio_titulo1="<?php echo $row['precio_titulo1'] ?>"
                               data-titulo2="<?php echo $row['titulo2'] ?>"
                               data-precio_titulo2="<?php echo $row['precio_titulo2'] ?>"
                               data-titulo3="<?php echo $row['titulo3'] ?>"
                               data-precio_titulo3="<?php echo $row['precio_titulo3'] ?>"
                               data-texto="<?php echo $row['texto'] ?>"
                               data-precio_texto="<?php echo $row['precio_texto'] ?>">

                        <?php if(!empty($row['concepto'])): echo '<b>Concepto: </b>'.$row['concepto'].'<br>'; endif; ?>
                        <?php if(!empty($row['concepto_subtitulo'])): echo '<b>Concepto subtítulo: </b>'.$row['concepto_subtitulo'].'<br>'; endif; ?>
                        <?php if(!empty($row['titulo1'])): echo '<b>Título 1: </b>'.$row['titulo1'].'<br>'; endif; ?>
                        <?php if(!empty($row['titulo2'])): echo '<b>Título 2: </b>'.$row['titulo2'].'<br>'; endif; ?>
                        <?php if(!empty($row['titulo3'])): echo '<b>Título 3: </b>'.$row['titulo3'].'<br>'; endif; ?>
                        <?php if(!empty($row['texto'])): echo '<b>Texto: </b>'.$row['texto']; endif; ?>
                    </label>
                </div>-->
                <button title="Añadir" class="add btn btn-primary btn-sm pull-left" data-id="<?php echo $row['id_concepto'] ?>"
                        data-concepto="<?php echo $row['concepto'] ?>"
                        data-precio_concepto="<?php echo $row['precio_concepto'] ?>"
                        data-concepto_subtitulo="<?php echo $row['concepto_subtitulo'] ?>"
                        data-precio_concepto_subtitulo="<?php echo $row['precio_concepto_subtitulo'] ?>"
                        data-titulo1="<?php echo $row['titulo1'] ?>"
                        data-precio_titulo1="<?php echo $row['precio_titulo1'] ?>"
                        data-titulo2="<?php echo $row['titulo2'] ?>"
                        data-precio_titulo2="<?php echo $row['precio_titulo2'] ?>"
                        data-titulo3="<?php echo $row['titulo3'] ?>"
                        data-precio_titulo3="<?php echo $row['precio_titulo3'] ?>"
                        data-texto="<?php echo $row['texto'] ?>"
                        data-precio_texto="<?php echo $row['precio_texto'] ?>">
                    <span class="glyphicon glyphicon-plus-sign"></span>
                </button>

                <div class="pull-left">
                    <?php if(!empty($row['concepto'])): echo '<b>Concepto: </b>'.$row['concepto'].'<br>'; endif; ?>
                    <?php if(!empty($row['concepto_subtitulo'])): echo '<b>Concepto subtítulo: </b>'.$row['concepto_subtitulo'].'<br>'; endif; ?>
                    <?php if(!empty($row['titulo1'])): echo '<b>Título 1: </b>'.$row['titulo1'].'<br>'; endif; ?>
                    <?php if(!empty($row['titulo2'])): echo '<b>Título 2: </b>'.$row['titulo2'].'<br>'; endif; ?>
                    <?php if(!empty($row['titulo3'])): echo '<b>Título 3: </b>'.$row['titulo3'].'<br>'; endif; ?>
                    <?php if(!empty($row['texto'])): echo '<b>Texto: </b>'.$row['texto']; endif; ?>
                </div>
            </td>
            <td>
                <div class="check-column">
                    <?php if(!empty($row['precio_concepto'])): echo $row['precio_concepto'].'<br>'; endif; ?>
                    <?php if(!empty($row['precio_concepto_subtitulo'])): echo $row['precio_concepto_subtitulo'].'<br>'; endif; ?>
                    <?php if(!empty($row['precio_titulo1'])): echo $row['precio_titulo1'].'<br>'; endif; ?>
                    <?php if(!empty($row['precio_titulo2'])): echo $row['precio_titulo2'].'<br>'; endif; ?>
                    <?php if(!empty($row['precio_titulo3'])): echo $row['precio_titulo3'].'<br>'; endif; ?>
                    <?php if(!empty($row['precio_texto'])): echo $row['precio_texto']; endif; ?>
                </div>
            </td>
        </tr>

        <?php

        if($counter >= $rows_per_page)
        {
            $current_page++;
            $counter = 1;
        }
        else
            $counter++;

    }


    Database::disconnect();
}

/**
 * Carga el PO si tiene
 * @param $id
 * @return mixed $data
 */
function getPO($id)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT po_ref, po_file from presupuesto where id = ?";
    $q = $pdo->prepare($sql);

    $q->execute(array($id));
    $data = $q->fetch();

    Database::disconnect();

    if($data) {
        print json_encode(array('po_ref' => $data['po_ref'], 'po_file' => $data['po_file']));
    }
    else {
        print 0;
    }
}

/**
 * Guarda los datos del PO
 * @param $id
 * @param $ref
 */
function savePO($id, $ref, $presu_ref)
{
    $error = false;
    $new_file_name = '';

    if(isset($_FILES['po-file']))
    {
        $max_upload = (int)(ini_get('upload_max_filesize'));

        if($_FILES['po-file']['error'] != 0) {
            switch($_FILES['po-file']['error'])
            {
                case 1:
                    $error = true;
                    $message = 'El fichero que intentas adjuntar es demasiado grande. Tamaño máximo '.$max_upload.'MB.';
                    break;
                default:
                    $error = true;
                    $message = 'Se ha producido un error al procesar el fichero adjunto.';
                    break;
            }
        }
        else {
            $ext = pathinfo($_FILES['po-file']['name'], PATHINFO_EXTENSION);
            //now is the time to modify the future file name and validate the file
            $new_file_name = $presu_ref.'.'.$ext; //rename file
            //move it to where we want it to be
            $ok = move_uploaded_file($_FILES['po-file']['tmp_name'], '../po/'.$new_file_name);
            if($ok) {
                $message = 'Se ha guardado el fichero.';
            }
            else {
                $error = true;
                $message = 'Se ha producido un error al procesar el fichero adjunto.';
            }
        }

    }

    if(!$error) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "UPDATE presupuesto set po_ref = ?, po_file = ?, estado = 'aceptado', fecha_aceptacion = ? where id = ?";
        $q = $pdo->prepare($sql);

        try {
            $q->execute(array($ref, $new_file_name, date('Y-m-d'), $id));
        }
        catch (Exception $e) {
            print $e;
        }

        Database::disconnect();

        print 1;
    }
    else {
        print 'Error: '.$message;
    }

}

function saveObservacionesFactura($id, $obs)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "UPDATE factura set observaciones = ? where id = ?";
    $q = $pdo->prepare($sql);

    try {
        $q->execute(array($obs, $id));
    }
    catch (Exception $e) {
        print $e;
    }

    Database::disconnect();

    print 1;
}

/**
 * Guardar factura y conceptos
 */
function saveFactura($isUpdate = false)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $noiva = $_POST['noiva'];
    $english = $_POST['english'];

    if($noiva) {
        $iva = 0;
        $total = $_POST['subtotal'];
    }
    else {
        $iva = $_POST['iva'];
        $total = $_POST['total'];
    }

    //recuperar id del project owner
    $id_owner = 0;
    if(empty($_POST['owner'])) {
        try {
            $qo = $pdo->prepare("select us.id as owner from presupuesto p 
                                        left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto 
                                        left join stack_bbgest.usuarios us on pr.project_owner=us.id where p.ref=?");
            $qo->bindValue(1,  $_POST['presupuesto_asoc'], PDO::PARAM_STR);
            $qo->execute();
            $data_owner = $qo->fetch();
            if($data_owner) {
                $id_owner=$data_owner[0];
            }
        } catch (Exception $e) {
            print $e;
        }
    }
    else {
        $id_owner = $_POST['owner'];
    }

    if (!$isUpdate) {

        //buscar id de la última factura
        $sql = "SELECT ref_factura from factura where ref_factura like ? order by id desc";
        $q = $pdo->prepare($sql);

        $curYear = date('y');
        $q->bindValue(1, "FA$curYear%", PDO::PARAM_STR);
        $q->execute();
        $data = $q->fetch();

        if ($data) {
            $currentId = (int)(substr($data[0], 4, 3));
            $id = str_pad($currentId + 1, 3, "0", STR_PAD_LEFT);
        } else {
            $id = "001";//TODO posar el numero actual, després tornar-ho a deixar com 001
        }

        //construimos ref factura
        $ref_cliente = $_POST['ref_cliente'];
        $ref_factura = "FA" . $curYear . $id . $ref_cliente;

        //guardar datos del presupuesto
        $sql = "INSERT INTO factura (
                                    ref_factura,
                                    fecha_emision,
                                    fecha_vencimiento,
                                    condiciones_pago,
                                    datos_bancarios,
                                    presupuesto_asoc,
                                    subtotal,
                                    iva,
                                    total,
                                    cliente,
                                    direccion,
                                    cif,
                                    cp,
                                    ref_po,
                                    autor,
                                    noiva,
                                    english,
                                    id_owner
                                  )
          values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $q = $pdo->prepare($sql);

        $fecha_emision = date('Y-m-d', strtotime($_POST['fecha_emision']));
        $fecha_vencimiento = date('Y-m-d', strtotime($_POST['fecha_vencimiento']));

        try {
            $q->execute(
                array(
                    $ref_factura,
                    $fecha_emision,
                    $fecha_vencimiento,
                    $_POST['condiciones_pago'],
                    $_POST['datos_bancarios'],
                    $_POST['presupuesto_asoc'],
                    str_replace(array('.',','),array('','.'),$_POST['subtotal']),
                    str_replace(array('.',','),array('','.'),$iva),
                    str_replace(array('.',','),array('','.'),$total),
                    $_POST['nombre_cliente'],
                    $_POST['direccion_cliente'],
                    $_POST['cif_cliente'],
                    $_POST['cp_cliente'],
                    $_POST['ref_compras'],
                    $_SESSION['valid'],
                    $noiva,
                    $english,
                    $id_owner)
            );
            $idFactura = $pdo->lastInsertId();
        } catch (Exception $e) {
            print $e;
        }

    } else {
        //actualizar datos de la factura
        $sql = "UPDATE factura SET fecha_emision = ?,
                                   fecha_vencimiento = ?,
                                   condiciones_pago = ?,
                                   datos_bancarios = ?,
                                   subtotal = ?,
                                   iva = ?,
                                   total = ?,
                                   cliente = ?,
                                   direccion = ?,
                                   cif = ?,
                                   cp = ?,
                                   ref_po = ?,
                                   noiva = ?,
                                   english = ?,
                                   id_owner = ?
          where id = ?";
        $q = $pdo->prepare($sql);

        $fecha_emision = date('Y-m-d', strtotime($_POST['fecha_emision']));
        $fecha_vencimiento = date('Y-m-d', strtotime($_POST['fecha_vencimiento']));
        $idFactura = $_POST['id'];

        try {
            $q->execute(
                array(
                    $fecha_emision,
                    $fecha_vencimiento,
                    $_POST['condiciones_pago'],
                    $_POST['datos_bancarios'],
                    str_replace(array('.',','),array('','.'),$_POST['subtotal']),
                    str_replace(array('.',','),array('','.'),$iva),
                    str_replace(array('.',','),array('','.'),$total),
                    $_POST['nombre_cliente'],
                    $_POST['direccion_cliente'],
                    $_POST['cif_cliente'],
                    $_POST['cp_cliente'],
                    $_POST['ref_compras'],
                    $noiva,
                    $english,
                    $id_owner,
                    $idFactura
                )
            );
        } catch (Exception $e) {
            //print $e;
        }

    }

    if ($isUpdate) {

        //borrar conceptos existentes primero

        $sql = "DELETE from concepto_factura where id_factura = ?";
        $q = $pdo->prepare($sql);

        $q->execute(array($idFactura));
    }

    /*****/
    //guardar todos los conceptos

    $count = 1;
    $sql = "INSERT INTO concepto_factura (
                                id_factura,
                                id_concepto,
                                orden,
                                concepto,
                                concepto_subtitulo,
                                titulo1,
                                titulo2,
                                titulo3,
                                texto,
                                precio_concepto,
                                precio_concepto_subtitulo,
                                precio_titulo1,
                                precio_titulo2,
                                precio_titulo3,
                                precio_texto
                             )
        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $q = $pdo->prepare($sql);

    foreach ($_POST['conceptos'] as $item) {
        $q->execute(
            array(
                $idFactura,
                $count,
                !empty($item['orden']) ? $item['orden']:"",
                !empty($item['concepto']) ? $item['concepto']:"",
                !empty($item['concepto_subtitulo']) ? $item['concepto_subtitulo']:"",
                !empty($item['titulo1']) ? $item['titulo1']:"",
                !empty($item['titulo2']) ? $item['titulo2']:"",
                !empty($item['titulo3']) ? $item['titulo3']:"",
                !empty($item['texto']) ? $item['texto']:"",
                !empty($item['precio_concepto']) ? $item['precio_concepto']:0,
                !empty($item['precio_concepto_subtitulo']) ? $item['precio_concepto_subtitulo']:0,
                !empty($item['precio_titulo1']) ? $item['precio_titulo1']:0,
                !empty($item['precio_titulo2']) ? $item['precio_titulo2']:0,
                !empty($item['precio_titulo3']) ? $item['precio_titulo3']:0,
                !empty($item['precio_texto']) ? $item['precio_texto']:0
            )
        );

        $count++;
    }

    //Get suma precios presupuesto
    $presu = $_POST['presupuesto_asoc'];

    $sql = "SELECT suma FROM presupuesto WHERE ref = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($presu));
    $data = $q->fetch();
    if($data)
        $suma_presu = $data[0];
    else
        $suma_presu = 0;

    //Si la suma de subtotales de las facturas cobradas asociadas al presu >= que suma del presu -> facturado totalmente, sino parcialmente
    $sql = "SELECT SUM(subtotal) FROM factura WHERE presupuesto_asoc = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($presu));
    $data = $q->fetch();

    if($suma_presu && $data && $data[0]) {
        $cobrado = $data[0];

        if(round($cobrado,0) >= round($suma_presu,0))
            $estado_presu = 'facturado totalmente';
        else
            $estado_presu = 'facturado parcialmente';
    }
    else
        $estado_presu = 'facturado parcialmente';

    //Actualizar presupuesto
    $sql = "UPDATE presupuesto SET estado = ? WHERE ref = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($estado_presu, $presu));

    Database::disconnect();

    print $idFactura;
}

/**
 * Actualizar datos del cliente y referencia de la factura
 */
function updateClienteFact()
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //obtener id facturas asociadas al presu $ref_presu_orig
    $id_presu = $_POST['id_presu'];
    $ref_presu_orig = $_POST['ref_presu_orig'];
    $ref_presu = $_POST['ref_presu'];
    $cliente = $_POST['cliente'];
    $ref_cliente = $_POST['ref_cliente'];
    $direccion = $_POST['direccion'];
    $cif = $_POST['cif'];
    $cp = $_POST['cp'];

    $sql = "SELECT * FROM factura where presupuesto_asoc= ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($ref_presu_orig));
    $q->execute();
    $facturas = $q->fetchAll(PDO::FETCH_ASSOC);

    //actualizar datos de las facturas
    foreach ($facturas as $fact) {
        $ref_factura_new = substr($fact['ref_factura'], 0, 7).$ref_cliente;

        $sql = "UPDATE factura SET ref_factura = ?,
                               cliente = ?,
                               direccion = ?,
                               cif = ?,
                               cp = ?,
                               presupuesto_asoc = ?
                where id = ?";

        $q = $pdo->prepare($sql);
        try {
            $q->execute(
                array(
                    $ref_factura_new,
                    $cliente,
                    $direccion,
                    $cif,
                    $cp,
                    $ref_presu,
                    $fact['id']
                )
            );
        } catch (Exception $e) {
            //print $e;
        }
    }

    //Get suma precios presupuesto
    $sql = "SELECT suma FROM presupuesto WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($id_presu));
    $data = $q->fetch();
    if($data)
        $suma_presu = $data[0];
    else
        $suma_presu = 0;

    //Si la suma de subtotales de las facturas cobradas asociadas al presu >= que suma del presu -> facturado totalmente, sino parcialmente
    $sql = "SELECT SUM(subtotal) FROM factura WHERE presupuesto_asoc = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($ref_presu));
    $data = $q->fetch();

    if($suma_presu && $data && $data[0]) {
        $cobrado = $data[0];

        if(round($cobrado,0) >= round($suma_presu,0))
            $estado_presu = 'facturado totalmente';
        else
            $estado_presu = 'facturado parcialmente';
    }
    else
        $estado_presu = 'facturado parcialmente';

    //Actualizar presupuesto
    $sql = "UPDATE presupuesto SET estado = ? WHERE ref = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($estado_presu, $ref_presu));

    Database::disconnect();

    print 1 ;
}

/**
 * Carga los datos de la factura
 * @param $id
 */
function loadFactura($id)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT *, factura.fecha_emision AS fecha_emision_factura, factura.estado as estadof FROM factura left join presupuesto on factura.presupuesto_asoc = presupuesto.ref where factura.id = ?";
    $q = $pdo->prepare($sql);

    $q->execute(array($id));
    $data = $q->fetch();

    Database::disconnect();

    return $data;
}

/**
 * Carga los conceptos de la factura
 * @param $id
 */
function loadConceptosFactura($id)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * from concepto_factura where id_factura = ?";
    $q = $pdo->prepare($sql);

    $q->execute(array($id));
    $data = $q->fetchAll();

    Database::disconnect();

    return $data;
}

/**
 * Abonar factura pero mantener el registro y los conceptos
 * @param $id
 */
function deleteFactura($id_fact, $presu)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Si la factura a borrar es la ultima insertada, la borramos directamente sin pasarla a abonada
    $sql = "SELECT MAX(id) from factura";
    $q = $pdo->prepare($sql);
    $q->execute();
    $data = $q->fetch();

    if ($data && $id_fact == $data[0]) {
        //Borrar factura
        $sql = "DELETE FROM factura where id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id_fact));
    }
    else {
        //Buscar Id ultima factura abonada
        $sql = "SELECT ref_abono from factura where ref_abono like ? order by ref_abono desc";
        $q = $pdo->prepare($sql);

        $curYear = date('y');
        $q->bindValue(1, "AB$curYear%", PDO::PARAM_STR);
        $q->execute();
        $data = $q->fetch();

        if ($data) {
            $currentId = (int)(substr($data[0], 4, 3));
            $id = str_pad($currentId + 1, 3, "0", STR_PAD_LEFT);
        } else {
            $id = "001";
        }

        //construimos ref abono
        $ref_cliente = substr($_POST['ref'], 7);
        $ref_abono = "AB" . $curYear . $id . $ref_cliente;

        //Abonar factura
        $sql = "UPDATE factura set estado = 'abonada', ref_abono = ?, fecha_abono = ?, razon_abono = ?, user_ultima_accion = ? where id = ?";
        $q = $pdo->prepare($sql);

        $fecha = date('Y-m-d');
        $q->execute(array($ref_abono, $fecha, $_POST['razon'], $_SESSION['valid'], $id_fact));
    }

    //Eliminar conceptos
    /*$sql = "DELETE from concepto_factura where id_factura = ?";
    $q = $pdo->prepare($sql);

    $q->execute(array($id));*/

    if(empty($presu)) {
        Database::disconnect();
        return 1;
    }

    //Si existen facturas asociadas al presu --> facturado parcialmente, sino aceptado
    $sql = "SELECT SUM(subtotal) FROM factura WHERE presupuesto_asoc = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($presu));
    $data = $q->fetch();

    if($data && $data[0]) {
        //Actualizar presupuesto
        $sql = "UPDATE presupuesto SET estado = 'facturado parcialmente' WHERE ref = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($presu));
    }
    else {
        //Actualizar presupuesto
        $sql = "UPDATE presupuesto SET estado = 'aceptado' WHERE ref = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($presu));
    }

    Database::disconnect();

    return 1;
}

/**
 * Marca la factura como cobrada
 * @param $id
 */
function cobrarFactura($id, $presu)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Actualizar factura
    $sql = "UPDATE factura SET estado = 'cobrada', fecha_cobro = NOW(), user_ultima_accion = ? WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($_SESSION['valid'], $id));

    if(empty($presu)) {
        Database::disconnect();
        return 1;
    }

    //Get suma precios presupuesto
    $sql = "SELECT suma FROM presupuesto WHERE ref = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($presu));
    $data = $q->fetch();
    if($data)
        $suma_presu = $data[0];
    else
        $suma_presu = 0;

    //Si la suma de subtotales de las facturas cobradas asociadas al presu >= que suma del presu -> cobrado, sino facturado parcialmente
    $sql = "SELECT SUM(subtotal) FROM factura WHERE presupuesto_asoc = ? AND estado = 'cobrada'";
    $q = $pdo->prepare($sql);
    $q->execute(array($presu));
    $data = $q->fetch();

    if($suma_presu && $data && $data[0]) {
        $cobrado = $data[0];

        if(round($cobrado,0) >= round($suma_presu,0)) {
            //Actualizar presupuesto
            $sql = "UPDATE presupuesto SET estado = 'cobrado' WHERE ref = ?";
            $q = $pdo->prepare($sql);
            $q->execute(array($presu));
        }
        else {
            //Actualizar presupuesto
            $sql = "UPDATE presupuesto SET estado = 'facturado parcialmente' WHERE ref = ?";
            $q = $pdo->prepare($sql);
            $q->execute(array($presu));
        }
    }

    Database::disconnect();
    return $suma_presu;
}

/**
 * Exportar tabla log en archivo excel
 */
function logExcel() {
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT DATE_FORMAT(fecha,'%d/%m/%Y'),
                   REPLACE(REPLACE(REPLACE(FORMAT(aceptado,2), ',', ':'), '.', ','), ':', '.'),
                   REPLACE(REPLACE(REPLACE(FORMAT(pendiente,2), ',', ':'), '.', ','), ':', '.'),
                   REPLACE(REPLACE(REPLACE(FORMAT(presupuestado,2), ',', ':'), '.', ','), ':', '.'),
                   REPLACE(REPLACE(REPLACE(FORMAT(facturas_pendientes,2), ',', ':'), '.', ','), ':', '.'),
                   REPLACE(REPLACE(REPLACE(FORMAT(facturado_total,2), ',', ':'), '.', ','), ':', '.'),
                   ratio3,
                   ratio6,
                   ratio12,
                   ratio24,
                   ratio3b,
                   ratio6b,
                   ratio12b,
                   ratio24b
            FROM log";

    $q = $pdo->prepare($sql);
    $q->execute();
    //$data = $q->fetchAll(PDO::FETCH_ASSOC);

    // Create array
    $list = array ();

    // Append results to array
    array_push($list, array("Fecha", "Aceptado", "Pendiente", "Presupuestado", "Fact. pendientes (sin f.origen)", "Fact. Total (en el año)",
        "Ratio3 ([aceptados + emitidos] en el periodo)/([acept, no acep, fact. parcial, fact.total]  emitidos en el periodo)",
        "Ratio6",
        "Ratio12",
        "Ratio24",
        "Ratio3b ([aceptados en el periodo + emitidos cuando sea])/([acept, no acep, fact. parcial, fact.total]  emitidos en el periodo)",
        "Ratio6b",
        "Ratio12b",
        "Ratio24b"));
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        array_push($list, array_values($row));
    }

    // Output array into CSV file
    $fp = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="log.csv"');
    foreach ($list as $ferow) {
        fputcsv($fp, $ferow, ';');
    }
    Database::disconnect();
}

/**
 * Exportar tabla log_performance en archivo excel
 */
function logExcelPerformance() {
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT DATE_FORMAT(fecha,'%d/%m/%Y') as fecha,
                   id_owner,
                   REPLACE(REPLACE(REPLACE(FORMAT(costes,2), ',', ':'), '.', ','), ':', '.') as costes,
                   REPLACE(REPLACE(REPLACE(FORMAT(facturado,2), ',', ':'), '.', ','), ':', '.') as facturado 
            FROM log_performance order by fecha desc, id_owner asc";

    $q = $pdo->prepare($sql);
    $q->execute();
    $data = $q->fetchAll(\PDO::FETCH_GROUP);

//    echo '<pre>';
//    print_r($data);
//    echo '</pre><br>';

//    // Create array
    $list = array ();

    $q_owners = $pdo->prepare("select distinct id_owner, nombre from log_performance l left join stack_bbgest.usuarios us on us.id=l.id_owner order by id_owner");
    $q_owners->execute();
    $d_owners = $q_owners->fetchAll(PDO::FETCH_ASSOC);

    $table_headers = array("Fecha");
//    $index = 1;

    foreach ($d_owners as $row) {
//        $table_headers .= "Costes_".$row['nombre'].", Facturado_".$row['nombre'];
        array_push($table_headers, "Costes_".$row['nombre'], "Facturado_".$row['nombre']);
//        if($index < count($d_owners)) {
//            $table_headers .= ', ';
//        }
//        $index++;
    }

//    echo $table_headers.'<br>';

    // Append results to array
    array_push($list, $table_headers);

    foreach ($data as $fecha=>$datos_fecha) {
        $temp = array($fecha);
//        $index = 1;
        foreach ($datos_fecha as $persona) {
            array_push($temp, $persona['costes'], $persona['facturado']);
//            if($index < count($datos_fecha)) {
//                $temp .= ', ';
//            }
//            $index++;
        }
//        echo $temp.'<br>';
        array_push($list, $temp);
    }

//     Output array into CSV file
    $fp = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="log_performance.csv"');
    foreach ($list as $ferow) {
        fputcsv($fp, $ferow, ';');
    }
    Database::disconnect();
}

//Exportar archivo excel con el histórico de facturado vs costes por año hasta 2017
function logExcelByOwner(){
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $currentYear = date('Y');

    $list = array ();

    try {
        $qOwners = $pdo->prepare("select u.id as id_project_owner, u.nombre as nombre from (
                                                SELECT  * FROM presu14.factura
                                                UNION ALL
                                                SELECT  * FROM presuetal.factura
                                            ) as f 
                                            left join (
                                                SELECT  * FROM presu14.presupuesto
                                                UNION ALL
                                                SELECT  * FROM presuetal.presupuesto
                                            ) as p on p.ref=f.presupuesto_asoc 
                                            left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto 
                                            left join stack_bbgest.usuarios u on u.id=f.id_owner 
                                            left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                            WHERE f.estado <> 'abonada' and YEAR(f.fecha_emision)>=2017 group by u.id");
        $qOwners->execute();
        $listOwners = $qOwners->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        print_r($e);
    }

    $header = [];
    $header[] = "Responsable";

    $dataFacturado = [];
    $dataCostes = [];

    for($y=2017;$y<=$currentYear;$y++){
        $header[] = "Facturado ".$y;
        $header[] = "Costes ".$y;

        $result = $pdo->prepare("select u.id as id_project_owner, u.nombre as project_owner, sum(f.subtotal) acumulado from (
                                                    SELECT  * FROM presu14.factura
                                                    UNION ALL
                                                    SELECT  * FROM presuetal.factura
                                                ) as f 
                                                left join (
                                                    SELECT  * FROM presu14.presupuesto
                                                    UNION ALL
                                                    SELECT  * FROM presuetal.presupuesto
                                                ) as p on p.ref=f.presupuesto_asoc 
                                                left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto 
                                                left join stack_bbgest.usuarios u on u.id=f.id_owner 
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=".$y." group by u.id order by u.nombre");
        $result->execute();
        $dataFacturado[$y] = $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

        $result2 = $pdo->prepare("SELECT p.project_owner as id_project_owner, co.id_proyecto, p.nombre, 
        sum(co.horas*(select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400) as coste
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto 
                                                WHERE co.year=".$y." group by p.project_owner");
        $result2->execute();
        $dataCostes[$y] = $result2->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
    }
    array_push($list, $header);

    $fila = [];
    foreach ($listOwners as $row){
        if($row['id_project_owner'] != null && $row['id_project_owner'] != 0){
            $fila[] = $row['nombre'];
            for($y=2017;$y<=$currentYear;$y++){
                $fila[] = isset($dataFacturado[$y][$row['id_project_owner']])?number_format($dataFacturado[$y][$row['id_project_owner']]['acumulado'], 2, ',', ''):"";
                $fila[] = isset($dataCostes[$y][$row['id_project_owner']])?number_format(isnull($dataCostes[$y][$row['id_project_owner']]['coste']), 2, ',', ''):"";
            }
            array_push($list, $fila);
            $fila = [];
        }
    }

    // Output array into CSV file
    $fp = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="log_performance_by_owner.csv"');
    foreach ($list as $ferow) {
        fputcsv($fp, $ferow, ';');
    }

    Database::disconnect();
}

//Exportar archivo excel con el histórico de facturado por proyecto vs costes por año hasta 2017
function logExcelByProyecto(){
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $currentYear = date('Y');

    $list = array ();

    try {
        $qProjects = $pdo->prepare("SELECT pr.id as id_proyecto, pr.nombre as proyecto, e.nombre as cliente, u.nombre as project_owner, sum(fact.subtotal) acumulado FROM (
                                                    SELECT * FROM presu14.factura
                                                    UNION ALL
                                                    SELECT * FROM presuetal.factura
                                                ) as fact
                                                left join (
                                                    SELECT * FROM presu14.presupuesto
                                                    UNION ALL
                                                    SELECT * FROM presuetal.presupuesto
                                                ) as p on p.ref=fact.presupuesto_asoc 
                                                left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto  
                                                left join stack_bbgest.usuarios u on u.id=pr.project_owner  
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                WHERE fact.estado <> 'abonada' and YEAR(fact.fecha_emision)>=2017 group by pr.id,u.nombre");
        $qProjects->execute();
        $listProjects = $qProjects->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        print_r($e);
    }

    $header = [];
    $header[] = "Proyecto";
    $header[] = "Cliente";
    $header[] = "Project Owner";

    $dataFacturado = [];
    $dataCostes = [];

    for($y=2017;$y<=$currentYear;$y++){
        $header[] = "Facturado ".$y;
        $header[] = "Costes ".$y;

        $result = $pdo->prepare("SELECT pr.id as id_proyecto, pr.nombre as proyecto, e.nombre as cliente, sum(fact.subtotal) acumulado, u.nombre as project_owner FROM (
                                                    SELECT * FROM presu14.factura
                                                    UNION ALL
                                                    SELECT * FROM presuetal.factura
                                                ) as fact
                                                left join (
                                                    SELECT * FROM presu14.presupuesto
                                                    UNION ALL
                                                    SELECT * FROM presuetal.presupuesto
                                                ) as p on p.ref=fact.presupuesto_asoc 
                                                left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto  
                                                left join stack_bbgest.usuarios u on u.id=pr.project_owner  
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                WHERE fact.estado <> 'abonada' and YEAR(fact.fecha_emision)=".$y." group by pr.id,u.nombre");
        $result->execute();
        $dataFacturado[$y] = $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

        $result2 = $pdo->prepare("SELECT co.id_proyecto, p.nombre, 
        sum(co.horas*(select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400) as coste 
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto
                                                WHERE co.year = ".$y." group by co.id_proyecto");
        $result2->execute();
        $dataCostes[$y] = $result2->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
    }
    array_push($list, $header);

    $fila = [];
    foreach ($listProjects as $row){
        if($row['id_proyecto'] != null && $row['id_proyecto'] != 0){
            $fila[] = $row['proyecto'];
            $fila[] = $row['cliente'];
            $fila[] = $row['project_owner'];
            for($y=2017;$y<=$currentYear;$y++){
                $fila[] = isset($dataFacturado[$y][$row['id_proyecto']])?number_format($dataFacturado[$y][$row['id_proyecto']]['acumulado'], 2, ',', ''):"";
                $fila[] = isset($dataCostes[$y][$row['id_proyecto']])?number_format(isnull($dataCostes[$y][$row['id_proyecto']]['coste']), 2, ',', ''):"";
            }
            array_push($list, $fila);
            $fila = [];
        }
    }

    // Output array into CSV file
    header('Content-Encoding: UTF-8');
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="log_performance_by_proyecto.csv"');
    echo "\xEF\xBB\xBF";
    $fp = fopen('php://output', 'w');
    foreach ($list as $ferow) {
        fputcsv($fp, $ferow, ';');
    }

    Database::disconnect();
}

//Exportar archivo excel con el histórico de facturado por cliente vs costes por año hasta 2017
function logExcelByClient(){
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $currentYear = date('Y');

    $list = array ();

    try {
        $qClients = $pdo->prepare("select e.nombre as cliente, e.id_empresa as id_cliente, sum(f.subtotal) as acumulado from (
                                                    SELECT  * FROM presu14.factura
                                                    UNION ALL
                                                    SELECT  * FROM presuetal.factura
                                                ) as f 
                                                left join (
                                                    SELECT  * FROM presu14.presupuesto
                                                    UNION ALL
                                                    SELECT  * FROM presuetal.presupuesto
                                                ) as p on p.ref=f.presupuesto_asoc 
                                                left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto  
                                                left join stack_bbgest.usuarios u on u.id=pr.project_owner  
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)>=2017 group by e.id_empresa");
        $qClients->execute();
        $listClients = $qClients->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        print_r($e);
    }

    $header = [];
    $header[] = "Cliente";

    $dataFacturado = [];
    $dataCostes = [];

    for($y=2017;$y<=$currentYear;$y++){
        $header[] = "Facturado ".$y;
        $header[] = "Costes ".$y;

        $result = $pdo->prepare("select e.id_empresa as id_cliente, e.nombre as cliente, sum(f.subtotal) as acumulado from (
                                                    SELECT  * FROM presu14.factura
                                                    UNION ALL
                                                    SELECT  * FROM presuetal.factura
                                                ) as f 
                                                left join (
                                                    SELECT  * FROM presu14.presupuesto
                                                    UNION ALL
                                                    SELECT  * FROM presuetal.presupuesto
                                                ) as p on p.ref=f.presupuesto_asoc 
                                                left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto  
                                                left join stack_bbgest.usuarios u on u.id=pr.project_owner  
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=".$y." group by e.id_empresa");
        $result->execute();
        $dataFacturado[$y] = $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

        $result2 = $pdo->prepare("SELECT e.id_empresa as id_cliente, e.nombre as cliente, co.id_proyecto, p.nombre, 
        sum(co.horas*(select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400) as coste 
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto 
                                                left join presu14.empresa e on e.id_empresa=p.id_cliente 
                                                WHERE co.year = ".$y." group by e.id_empresa");
        $result2->execute();
        $dataCostes[$y] = $result2->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
    }
    array_push($list, $header);

    $fila = [];
    foreach ($listClients as $row){
        if($row['cliente'] != null){
            $fila[] = $row['cliente'];
            for($y=2017;$y<=$currentYear;$y++){
                $fila[] = isset($dataFacturado[$y][$row['id_cliente']])?number_format($dataFacturado[$y][$row['id_cliente']]['acumulado'], 2, ',', ''):"";
                $fila[] = isset($dataCostes[$y][$row['id_cliente']])?number_format(isnull($dataCostes[$y][$row['id_cliente']]['coste']), 2, ',', ''):"";
            }
            array_push($list, $fila);
            $fila = [];
        }
    }

    // Output array into CSV file
    header('Content-Encoding: UTF-8');
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="log_performance_by_cliente.csv"');
    echo "\xEF\xBB\xBF";
    $fp = fopen('php://output', 'w');
    foreach ($list as $ferow) {
        fputcsv($fp, $ferow, ';');
    }

    Database::disconnect();
}

function isnull($var, $default=0) {
    return is_null($var) ? $default : $var;
}

/**
 * Limpieza bbgest
 */
function bbgest() {
    $empresas = array("%(ALLIANCE%",
        "%(ASAC%",
        "%(Amneal%",
        "%(Compañía%",
        "%(FEDERACION%",
        "%(GR.%",
        "%(GRUPO%",
        "%(J&J)%",
        "%(LACTALIS%",
        "%(PARTNER%",
        "%(REIG%",
        "%(SUPERFICIES%",
        "%(VEGA%",
        "%-SCCE%",
        "%21%",
        "%2202%",
        "%3M%",
        "%50%",
        "%A.%",
        "%A2C%",
        "%ABADIA%",
        "%ABBEL%",
        "%ABBOTT%",
        "%ABBVIE%",
        "%ABC%",
        "%ABELLO%",
        "%AC%",
        "%ACCORD%",
        "%ACQUISITIONS%",
        "%ACTAFARMA%",
        "%ACTELION%",
        "%ACTIVE%",
        "%ADAMED%",
        "%ADDENDIA%",
        "%ADECCO%",
        "%ADESLAS%",
        "%ADOLFO%",
        "%AFFINITY%",
        "%AG%",
        "%AGRUPACIÓ%",
        "%AGUINAGA%",
        "%AIR%",
        "%AIR-VAL%",
        "%ALCÓN%",
        "%ALCON%",
        "%ALDO-UNION%",
        "%ALIMENTACIÓ%",
        "%ALIMENTACIÓN)%",
        "%ALIMENTARIA%",
        "%ALIMENTS%",
        "%ALK%",
        "%ALLERGAN%",
        "%ALLERGY%",
        "%ALLIANCE%",
        "%ALLIANZ%",
        "%ALMIRALL%",
        "%ALMUS%",
        "%ALPARGATERIA%",
        "%ALTER%",
        "%ALTIMA%",
        "%ALTRAN%",
        "%AMEUR%",
        "%AMGEN%",
        "%ANETO%",
        "%ANGELINI%",
        "%ANGULAS%",
        "%ANIMAL%",
        "%ANTONIO%",
        "%APIVITA%",
        "%APOTEX%",
        "%AQUARIUS%",
        "%ARBORA%",
        "%ARCHIMEDES%",
        "%ARDEN%",
        "%ARENAS%",
        "%ARGAL%",
        "%ARIAS%",
        "%ARKOPHARMA%",
        "%ART%",
        "%ARTIACH%",
        "%ASAC%",
        "%ASEGURADORA%",
        "%ASISA%",
        "%ASOCIACIÓN%",
        "%ASOFRIO%",
        "%ASTELLAS%",
        "%ASTILLEROS%",
        "%ASTRAZENECA%",
        "%ASTURIANA%",
        "%ATELIER%",
        "%AUDIOTRONICS%",
        "%AUROBINDO%",
        "%AUSONIA%",
        "%AVENTIS%",
        "%AXA%",
        "%AYUNTAMIENTO%",
        "%AZUCARERA%",
        "%Aboca%",
        "%Acofarma%",
        "%B-GRUP%",
        "%B.%",
        "%B.BRAUN%",
        "%BACARDI%",
        "%BADALONA%",
        "%BAILLEUL%",
        "%BALLERINAS%",
        "%BAMA-GEVE%",
        "%BANC%",
        "%BANCO%",
        "%BAQUERO%",
        "%BARCELÓ%",
        "%BARCELONA%",
        "%BARRIÉ%",
        "%BASF%",
        "%BATALLA%",
        "%BAUSCH%",
        "%BAXTER%",
        "%BAYER%",
        "%BBRAUN%",
        "%BDF%",
        "%BEBE%",
        "%BEBIDAS%",
        "%BEIERSDORF%",
        "%BELUCHI%",
        "%BENCKISER%",
        "%BI%",
        "%BIAL%",
        "%BICENTURY%",
        "%BILBAO%",
        "%BIMBA%",
        "%BIMBO%",
        "%BIOCODEX%",
        "%BIODERMA%",
        "%BIOGEN%",
        "%BIOIBÉRICA%",
        "%BIOKIT%",
        "%BIOMERIEUX%",
        "%BIOMNIS%",
        "%BIORGA%",
        "%BISCUITS%",
        "%BLANCA%",
        "%BLOSS%",
        "%BLUSENS%",
        "%BMW%",
        "%BOCK%",
        "%BODACLICK.COM%",
        "%BODEGA%",
        "%BODEGAS%",
        "%BOEHRINGER%",
        "%BOIRON%",
        "%BORGES%",
        "%BOSCH%",
        "%BRAINPHARMA%",
        "%BRANDS%",
        "%BRILL%",
        "%BRISTOL-MYERS%",
        "%BRUDYAB%",
        "%BUBBLEGUM%",
        "%BURGER%",
        "%BYASS%",
        "%BYLY-ICART.%",
        "%Braun%",
        "%C-TRADE%",
        "%CABRAS%",
        "%CACAOLAT%",
        "%CAFÈS%",
        "%CAIXA%",
        "%CAIXA%",
        "%CAJAMAR%",
        "%CALIER%",
        "%CALIFORNIA%",
        "%CALVO%",
        "%CAMPOFRÍO%",
        "%CANCER%",
        "%CANDY%",
        "%CANIN%",
        "%CAOLAND,%",
        "%CARE%",
        "%CARRY%",
        "%CASA%",
        "%CASADEMONT%",
        "%CASEN-FLEET%",
        "%CASER%",
        "%CASH%",
        "%CASTY,%",
        "%CATALÁN%",
        "%CATALANA%",
        "%CATRAL%",
        "%CEDERROTH%",
        "%CEDRO%",
        "%CEGEDIM%",
        "%CELGENE%",
        "%CENTER%",
        "%CENTRAL%",
        "%CENTRE%",
        "%CENTRO%",
        "%CENTROS%",
        "%CENTRUM%",
        "%CEPA%",
        "%CEVA%",
        "%CGA%",
        "%CHEVROLET%",
        "%CHIESI%",
        "%CHIK%",
        "%CHILDREN%",
        "%CHOCOLATES%",
        "%CHRISTIAN%",
        "%CHUPA%",
        "%CHUPS%",
        "%CIENCIAS%",
        "%CILAG%",
        "%CINFA%",
        "%CIS%",
        "%CLESA%",
        "%CLICKSEGUROS%",
        "%CLINICUM%",
        "%COCA-COLA%",
        "%CODORNIU%",
        "%COFARES%",
        "%COFIDIS%",
        "%COLGATE%",
        "%COLHOGAR%",
        "%COLOMER%",
        "%COMBINO%",
        "%COMBIX%",
        "%COMERÇ%",
        "%COMERCIAL%",
        "%COMERCIALES%",
        "%COMERCIO%",
        "%COMPONENTS%",
        "%COMUNICACIÓN%",
        "%CONFORTEL%",
        "%CONSERVAS%",
        "%CONSULTING%",
        "%CONSUMER%",
        "%CONTACT%",
        "%CONTORNI%",
        "%CONTRA%",
        "%CORPORACIÓN%",
        "%CORTEFIEL%",
        "%CORTEINGLÉS%",
        "%COSMETIC-%",
        "%COSMETICS%",
        "%COSMETIQUES%",
        "%COTNSA%",
        "%COTY%",
        "%CUATRO%",
        "%CUSI%",
        "%Company%",
        "%DAIICHI%",
        "%DAMM%",
        "%DANI%",
        "%DANONE%",
        "%DAZS%",
        "%DECHRA%",
        "%DEITERS%",
        "%DELI.CAT%",
        "%DELOITTE%",
        "%DELPHARM%",
        "%DENTAID%",
        "%DENTAL%",
        "%DERMATOLOGY%",
        "%DERMOFARM%",
        "%DESIGUAL%",
        "%DEVELOPMENT%",
        "%DFV%",
        "%DHU%",
        "%DIAFARM%",
        "%DIAGNOSTICS%",
        "%DIALOGUE%",
        "%DIOR%",
        "%DIRECT%",
        "%DIRECTA%",
        "%DISCAU%",
        "%DISTREX%",
        "%DISTRITO%",
        "%DIVASA%",
        "%DJO%",
        "%DKV%",
        "%DNET%",
        "%DOHME%",
        "%DOMÍNGUEZ%",
        "%DOUGLAS%",
        "%DR.%",
        "%DRINK%",
        "%DULCESOL%",
        "%EASYJET%",
        "%EBRO%",
        "%ECHOSENS%",
        "%ECKES%",
        "%EDITORIAL%",
        "%EFFIK%",
        "%ELADIET%",
        "%ELANCO%",
        "%ELECTRODOMESTICOS%",
        "%ELIZABETH%",
        "%EMERGIA%",
        "%EMILIO%",
        "%ENTERTAINMENT%",
        "%EQUMEDIA%",
        "%ERN%",
        "%ESPAÑOLA%",
        "%ESTEE%",
        "%ESTEVE%",
        "%EU%",
        "%EUROBANAN%",
        "%EUROGALENUS%",
        "%EUROPASTRY%",
        "%EUROPE%",
        "%EUROPEA%",
        "%EV3%",
        "%EXHIBITION%",
        "%EXPANSCIENCE%",
        "%EXPORT%",
        "%Endesa%",
        "%Energy%",
        "%Española%",
        "%FABRE%",
        "%FACTORY%",
        "%FACULTAD%",
        "%FAES%",
        "%FARLINE%",
        "%FARMA%",
        "%FARMA-LEPORI%",
        "%FARMACÉUTICA,%",
        "%FARMACEUTICA%",
        "%FARMADIET%",
        "%FARMASIERRA%",
        "%FARMAVIC%",
        "%FASHION%",
        "%FELIX%",
        "%FERRER%",
        "%FERRERO%",
        "%FERRING%",
        "%FHARMONAT%",
        "%FHER%",
        "%FILORGA%",
        "%FINDIRECT%",
        "%FINDUS%",
        "%FINI%",
        "%FLORETTE%",
        "%FNAC%",
        "%FONT%",
        "%FOOD%",
        "%FOODS%",
        "%FORTÉ%",
        "%FRANCE%",
        "%FREIXENET%",
        "%FRESENIUS%",
        "%FRIGO%",
        "%FRIT%",
        "%FROZEN%",
        "%FRUIT%",
        "%FUJISAWA%",
        "%FUNDACIÓ%",
        "%FUNDACIÓN%",
        "%FUNDACION%",
        "%FUNERARIS%",
        "%Fisiocrem%",
        "%GAES%",
        "%GALLINA%",
        "%GALLO%",
        "%GAMBLE%",
        "%GARAVILLA%",
        "%GARCÍA%",
        "%GEBRO%",
        "%GELOS%",
        "%GEMSBOK%",
        "%GENERAL%",
        "%GENEREAL%",
        "%GENESIS%",
        "%GENOVÉ%",
        "%GENTALIA%",
        "%GEORGIA%",
        "%GESTESA%",
        "%GILEAD%",
        "%GIMENEZ%",
        "%GIRABEBE%",
        "%GLAXOSMITHKLINE%",
        "%GLOBAL%",
        "%GODÓ%",
        "%GOLOSINAS%",
        "%GONZALEZ%",
        "%GRABALFA%",
        "%GRAN%",
        "%GRANINI%",
        "%GREEN%",
        "%GRIFOLS%",
        "%GROUP%",
        "%GRUNENTHAL%",
        "%GRUP%",
        "%GRUPO%",
        "%GSK%",
        "%GUM%",
        "%GYNEA%",
        "%Genocosmetics%",
        "%Group%",
        "%HÄAGEN%",
        "%HACKETT%",
        "%HARIBO%",
        "%HEALTH%",
        "%HEALTHCARE%",
        "%HEALTHCARE)%",
        "%HEEL%",
        "%HEINEKEN%",
        "%HEINZ%",
        "%HENKEL%",
        "%HERO%",
        "%HIJOS%",
        "%HILLS%",
        "%HIPRA%",
        "%HISPANIA%",
        "%HOJIBLANCA%",
        "%HOLLISTER%",
        "%HORNIMANS%",
        "%HOSPITALARIOS%",
        "%HOTELES%",
        "%HOTELS%",
        "%HOUSE%",
        "%HUYNDAI%",
        "%HYGIENE%",
        "%IBÉRICA%",
        "%IBERIA%",
        "%IBERICA%",
        "%IDEC%",
        "%IDESA%",
        "%IFC%",
        "%IKEA%",
        "%IMAZ%",
        "%IMC%",
        "%IMPERIAL%",
        "%INDAS%",
        "%INDITEX%",
        "%INDO%",
        "%INDUSTEX%",
        "%INDUSTRIA%",
        "%INDUSTRIAL%",
        "%INDUSTRIALS%",
        "%ING%",
        "%INGELHEIM%",
        "%INGRAM%",
        "%INIBSA%",
        "%INKEMA%",
        "%INNOTHERA%",
        "%INNOVA%",
        "%INNOVATIVE%",
        "%INQUITEX%",
        "%INSURANCE%",
        "%INTEDOCS%",
        "%INTEGRALS%",
        "%INTERMÓN%",
        "%INTERNACIONAL%",
        "%INTERNATIONAL%",
        "%INVERVANTE%",
        "%IPSEN%",
        "%IRIS%",
        "%ISDIN%",
        "%ITALFÁRMACO%",
        "%Ibérica%",
        "%Innos%",
        "%Integtral%",
        "%JANSSEN%",
        "%JOFRE)%",
        "%JOHNSON%",
        "%JUVER%",
        "%Jofre%",
        "%KABI%",
        "%KAIKU%",
        "%KARIZOO%",
        "%KELLOGGS%",
        "%KERN%",
        "%KIA%",
        "%KIN%",
        "%KING%",
        "%KORHISPANA%",
        "%LÁCER%",
        "%LAB%",
        "%LABORATORIES%",
        "%LABORATORIO%",
        "%LABORATORIOS%",
        "%LACASA%",
        "%LACTEOS%",
        "%LAINCO%",
        "%LANJARON%",
        "%LANTARES%",
        "%LARCOVI%",
        "%LAUDER%",
        "%LBORATORIOS%",
        "%LECHE%",
        "%LECHERA%",
        "%LECITRAILER%",
        "%LEGO%",
        "%LEO%",
        "%LETI%",
        "%LETONA%",
        "%LEX%",
        "%LEYA%",
        "%LIBERTY%",
        "%LILLY%",
        "%LINDT%",
        "%LINEA%",
        "%LITAPHAR%",
        "%LOEWE%",
        "%LOGITRAVEL%",
        "%LOLA%",
        "%LOMB%",
        "%LOTTUSSE%",
        "%LUGA%",
        "%LUNDBECK%",
        "%LVMH%",
        "%Labs%",
        "%Ltd%",
        "%MÀGIC%",
        "%MÉDICA%",
        "%MÚTUA%",
        "%MAGNUM%",
        "%MAHOU%",
        "%MAHOU-SAN%",
        "%MAKRO%",
        "%MANAGEMENT%",
        "%MANAUTA%",
        "%MANGO%",
        "%MANTEQUERIAS%",
        "%MANUAL%",
        "%MAQUINISTA%",
        "%MARCA%",
        "%MARTI-TOR%",
        "%MASCARO&%",
        "%MATTEL%",
        "%MAXXIUM%",
        "%MAYMÓ%",
        "%MAYOR)%",
        "%MCDONALD%",
        "%MEAD%",
        "%MED%",
        "%MEDEA%",
        "%MEDICA%",
        "%MEDICAL%",
        "%MEMIMO%",
        "%MENARINI%",
        "%MERCEDES-BENZ%",
        "%MERCK%",
        "%MERIAL%",
        "%METROPOLITANA%",
        "%MGT%",
        "%MICRO%",
        "%MIGUELÁÑEZ%",
        "%MIGUEL%",
        "%MIGUEL.%",
        "%MILLS%",
        "%MIQUEL%",
        "%MONDADORI%",
        "%MONDELEZ%",
        "%MORO%",
        "%MOTOR%",
        "%MOTORS%",
        "%MOVELIA%",
        "%MUNRECO%",
        "%MURT%",
        "%MYLAN%",
        "%Medical%",
        "%NÉCTAR%",
        "%NATURAL%",
        "%NATURHOUSE%",
        "%NATYSAL%",
        "%NEPHEW%",
        "%NESTLÉ%",
        "%NIKE%",
        "%NINTENDO%",
        "%NIVEA%",
        "%NORDISK%",
        "%NORGINE%",
        "%NORMON%",
        "%NOVA%",
        "%NOVARTIS%",
        "%NOVICO%",
        "%NOVO%",
        "%NUCLETRON%",
        "%NUTRECO%",
        "%NUTREXPA%",
        "%NUTRICIÓN)%",
        "%NUTRICIA%",
        "%NUTRITION%",
        "%Neurología%",
        "%OBRA%",
        "%OCCIENT%",
        "%OCEANOGRÀFIC%",
        "%OETKER%",
        "%OMEGA%",
        "%ON%",
        "%OPTICA%",
        "%OPTICALIA%",
        "%ORANGINA-SCHWEPPES%",
        "%ORDESA%",
        "%ORGANON%",
        "%ORIFLAME%",
        "%ORTIZ%",
        "%OSBORNE%",
        "%OTICON%",
        "%OTSUKA%",
        "%OTTO%",
        "%OVEJERO%",
        "%OXFAM%",
        "%Ortopédicos%",
        "%PACIFIC%",
        "%PALAU%",
        "%PALMOLIVE%",
        "%PAMIES%",
        "%PANRICO%",
        "%PANRICO)%",
        "%PARFUMS%",
        "%PASCUAL%",
        "%PASTAS%",
        "%PASTEUR%",
        "%PATIENT%",
        "%PENSA%",
        "%PEPSICO%",
        "%PERDRALBES%",
        "%PEREZ%",
        "%PERNOD%",
        "%PET%",
        "%PETCARE%",
        "%PEUSEK%",
        "%PFIZER%",
        "%PHARM%",
        "%PHARMACEUTICAL%",
        "%PHARMACEUTICALS%",
        "%PHARMADUS%",
        "%PHARMAGENUS%",
        "%PHARMAMAR%",
        "%PHB%",
        "%PHERGAL%",
        "%PHI%",
        "%PHILLIPS%",
        "%PIERRE%",
        "%PIKOLIN%",
        "%PILLS%",
        "%PLAMECA%",
        "%PLOUGH%",
        "%PLUSULTRA%",
        "%POSAY%",
        "%POWER%",
        "%POWERADE%",
        "%POZO%",
        "%PRANAROM%",
        "%PREMIUM%",
        "%PRETTY%",
        "%PRIMA-DERM%",
        "%PRIMOR%",
        "%PROCTER%",
        "%PRODUCTS%",
        "%PRONOKAL%",
        "%PUIG%",
        "%PULEVA%",
        "%PULL&BEAR%",
        "%PULLMANTOUR%",
        "%PURINA%",
        "%PYC%",
        "%Pacífico%",
        "%Pharmaceuticals)%",
        "%Pharmadiet%",
        "%Pharmagenus,%",
        "%Picart%",
        "%Piensos%",
        "%Productos%",
        "%QUALIA%",
        "%QUALIGEN%",
        "%QUALIMEDIC%",
        "%QUELY%",
        "%RANBAXY%",
        "%RANDOM%",
        "%RAVICH%",
        "%RAYAS%",
        "%RBA%",
        "%RECKITT%",
        "%REPSOL%",
        "%RESORTS%",
        "%RETAIL%",
        "%RETUERTA%",
        "%REVERA%",
        "%REVISTAS%",
        "%REYSER%",
        "%RICARD%",
        "%RICHEMONT%",
        "%RINCÓN%",
        "%RN%",
        "%ROC%",
        "%ROCA%",
        "%ROCHE%",
        "%ROTARY%",
        "%ROTTAPHARM%",
        "%ROVI%",
        "%ROYAL%",
        "%RUBIÓ%",
        "%Reig%",
        "%SABADELL%",
        "%SALUD%",
        "%SALUT%",
        "%SALVAT%",
        "%SALVELOX%",
        "%SAN%",
        "%SANDOZ%",
        "%SANISHOP%",
        "%SANITAS%",
        "%SANKYO%",
        "%SANOFI%",
        "%SANTE%",
        "%SANTIVERI%",
        "%SANUTRI%",
        "%SAS%",
        "%SAVE%",
        "%SAWES%",
        "%SCA%",
        "%SCANDINAVIAN%",
        "%SCHERING%",
        "%SCIENCE%",
        "%SCOTTEX%",
        "%SEGUROS%",
        "%SELVA%",
        "%SENSALIA%",
        "%SENSES%",
        "%SENTAI%",
        "%SEPROMARK%",
        "%SERONO%",
        "%SERRA%",
        "%SERTEL%",
        "%SERVEIS%",
        "%SERVICES%",
        "%SERVIER%",
        "%SESDERMA%",
        "%SHARP%",
        "%SHELL%",
        "%SHERING%",
        "%SHIRE%",
        "%SHISEIDO%",
        "%SIGHORE%",
        "%SIGMA%",
        "%SIRENA%",
        "%SISTEMAS%",
        "%SITA%",
        "%SKEYNDOR%",
        "%SL%",
        "%SMITH%",
        "%SOCIAL%",
        "%SOLAN%",
        "%SOLER%",
        "%SOLIS%",
        "%SOLUCIONES%",
        "%SOLUTIONS%",
        "%SOLVAY%",
        "%SORLI%",
        "%SOS%",
        "%SPRÜNGLI%",
        "%SPRINGFIELD%",
        "%SQUIBB%",
        "%SSL%",
        "%STADA%",
        "%STAEDTLLER%",
        "%STALLERGENES%",
        "%STORE%",
        "%STRATECFARAMA%",
        "%STRAUMANN%",
        "%SUBSTIPHARM%",
        "%SUMINISTROS%",
        "%SUNSTAR%",
        "%SWATCH%",
        "%Seguridad)%",
        "%Sociedad%",
        "%Solar%",
        "%Surgical%",
        "%T-CUENTO%",
        "%TARRADELLAS%",
        "%TAU%",
        "%TECHNOLOGIES%",
        "%TECNOLÓGICO)%",
        "%TEKNEI%",
        "%TELEPIZZA%",
        "%TEVA%",
        "%TEXTIL%",
        "%TEXTURA%",
        "%THE%",
        "%THEA%",
        "%THERAPEUTICS%",
        "%TOBACCO%",
        "%TOMTOM%",
        "%TORRENS%",
        "%TORRES%",
        "%TOUS%",
        "%TRENDY%",
        "%TURIA%",
        "%TWIST%",
        "%UAB%",
        "%UCB%",
        "%UNILEVER%",
        "%UNIPAPEL%",
        "%UNITEX-HARTMANN%",
        "%UNIVERSIDAD%",
        "%UNIVET%",
        "%UNO%",
        "%URGO%",
        "%URIACH%",
        "%URIACH/AQUILEA%",
        "%URIATCH%",
        "%VACACIONES%",
        "%VALEANT%",
        "%VALLFORMOSA%",
        "%VALOR%",
        "%VARMA%",
        "%VAZA%",
        "%VELLA%",
        "%VENPHARMA%",
        "%VENTREVISTA%",
        "%VERTI%",
        "%VETCARE,%",
        "%VETPLUS%",
        "%VIÑAS%",
        "%VICHY%",
        "%VIDAL%",
        "%VIGNE%",
        "%VIPS%",
        "%VIRBAC%",
        "%VITA%",
        "%VODAFONE%",
        "%VOGEL%",
        "%VetCare,%",
        "%Viajes%",
        "%WALNUTS%",
        "%WATERS%",
        "%WELEDA%",
        "%WILKINSON%",
        "%WONDERBOX%",
        "%WORLDWIDE%",
        "%WRIGLEY%",
        "%WYETH%",
        "%YSONUT%",
        "%ZADIBE%",
        "%ZAMBON%",
        "%ZARAGOZANA%",
        "%ZELTIA%",
        "%ZENTIVA%",
        "%ZIMMER%",
        "%ZINKIA%",
        "%ZOETIS%",
        "%ZURICH%",
        "%ZYDUS%",
        "%eCUSTOMER%",
        "%spirits%",
        "%test%",
        "%test2%",
        "%wines&%");

    /*$test = array("%THEA%",
        "%ALMIRALL%",
        "%ZURICH%");*/
    $pdo = Database::connect('bbgest');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $list = array();
    array_push($list, array("BUSQUEDA","EMPRESA", "ID EMPRESA", "ESTADOS", "PM"));

    foreach($empresas as $texto){
        $sql = "SELECT ?, emp.nombre as empresa, pm.empresa as id_empresa, (select count(*) from estado where id_empresa=pm.empresa) as estados, pm.nombre as pm
            FROM pm
            left join empresa emp on pm.empresa=emp.id_empresa
            where pm.empresa in (select id_empresa from empresa where nombre like ?)";

        $q = $pdo->prepare($sql);
        try {

            $q->execute(array($texto, $texto
            ));
        }
        catch(PDOException $e) {
            print_r($e);
        }

        // Append results to array
        while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            array_push($list, array_values($row));
        }
    }

    // Output array into CSV file
    $fp = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="log.csv"');
    foreach ($list as $ferow) {
        fputcsv($fp, $ferow, ';');
    }
}

/**
 * Exportar tabla presus/facturas excel
 */
function exportExcel($tipo) {
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if($tipo == "presupuestos") {
        $sql = "SELECT ref,
                   ifnull(ref_cliente,''),
                   ifnull(nombre_cliente,''),
                   ifnull(contacto_cliente,''),
                   ifnull(proyecto,''),
                   ifnull(po_ref,''),
                   ifnull(po_file,''),
                   estado,
                   ifnull(REPLACE(REPLACE(REPLACE(FORMAT(suma,2), ',', ':'), '.', ','), ':', '.'),''),
                   ifnull(DATE_FORMAT(fecha_emision,'%d/%m/%Y'),''),
                   ifnull(DATE_FORMAT(fecha_aceptacion,'%d/%m/%Y'),''),
                   ifnull(DATE_FORMAT(fecha_negociacion,'%d/%m/%Y'),''),
                   ifnull(DATE_FORMAT(fecha_noaceptacion,'%d/%m/%Y'),''),
                   ifnull(DATE_FORMAT(fecha_facturacion,'%d/%m/%Y'),''),
                   ifnull(presu_origen,''),
                   autor
            FROM listado_presus order by fecha_emision desc";

        $q = $pdo->prepare($sql);
        $q->execute();
        //$data = $q->fetchAll(PDO::FETCH_ASSOC);

        // Create array
        $list = array ();

        // Append results to array
        array_push($list, array("Ref. presupuesto", "Ref. cliente", "Nombre cliente", "Contacto cliente", "Nombre proyecto", "Orden de compra", "Fichero orden de compra", "Estado", "Total", "Fecha emisión", "Fecha aceptación", "Fecha negociación", "Fecha no aceptación", "Fecha facturación", "Presu origen", "Autor"));
    }
    else if($tipo == "facturas") {
        $sql = "SELECT ref_factura,
                   ifnull(ref_abono,''),
                   ifnull(ref_po,''),
                   ifnull(presupuesto_asoc,''),
                   ifnull(DATE_FORMAT(fecha_emision,'%d/%m/%Y'),''),
                   ifnull(DATE_FORMAT(fecha_vencimiento,'%d/%m/%Y'),''),
                   ifnull(DATE_FORMAT(fecha_cobro,'%d/%m/%Y'),''),
                   ifnull(DATE_FORMAT(fecha_abono,'%d/%m/%Y'),''),
                   ifnull(datos_bancarios,''),
                   ifnull(REPLACE(REPLACE(REPLACE(FORMAT(subtotal,2), ',', ':'), '.', ','), ':', '.'),''),
                   ifnull(REPLACE(REPLACE(REPLACE(FORMAT(iva,2), ',', ':'), '.', ','), ':', '.'),''),
                   ifnull(REPLACE(REPLACE(REPLACE(FORMAT(total,2), ',', ':'), '.', ','), ':', '.'),''),
                   estado,
                   ifnull(cliente,''),
                   ifnull(direccion,''),
                   ifnull(cif,''),
                   ifnull(cp,''),
                   autor
            FROM factura order by ref_factura desc, fecha_emision desc";

        $q = $pdo->prepare($sql);
        $q->execute();
        //$data = $q->fetchAll(PDO::FETCH_ASSOC);

        // Create array
        $list = array ();

        // Append results to array
        array_push($list, array("Ref. factura", "Ref. abono", "Orden de compra", "Ref. presupuesto", "Fecha emisión", "Fecha vencimiento", "Fecha cobro", "Fecha abono", "Datos bancarios", "Subtotal", "IVA", "Total", "Estado", "Cliente", "Dirección", "CIF", "CP", "Autor"));
    }
    else {
        return;
    }

    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        array_push($list, array_values($row));
    }

    // Output array into CSV file
    $fp = fopen('php://output', 'w');
    header('Content-Encoding: UTF-8');
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$tipo.'.csv"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    foreach ($list as $ferow) {
        fputcsv($fp, $ferow, ';');
    }
}

/**
 * Buscar proyecto que su nombre contenga el texto y pertenezca al cliente en cuestión
 * @param $text
 * @param $cliente
 */
function searchProyecto($text, $cliente)
{
    $pdo = Database::connect('stack_bbgest');

    $sql = "SELECT p.*, date_format(p.kickoff, \"%Y\") as year FROM proyectos p where p.nombre like ? and id_cliente = ? and p.cerrado=0";

    $pdo -> exec('SET NAMES utf8'); // METHOD #3

    $q = $pdo->prepare($sql);
    $q->bindValue(1, "%$text%", PDO::PARAM_STR);
    $q->bindValue(2, $cliente, PDO::PARAM_INT);
    $result = array();
    $count = 0;
    $q->execute();
    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as $row) {

        $result[$count]['nombre'] = $row['nombre'];
        $result[$count]['id'] = $row['id'];
        $result[$count]['ref_proyecto'] = $row['ref'];
        $result[$count]['year'] = $row['year'];

        $count++;
    }
    print json_encode($result);

    Database::disconnect();
}

/**
 * Buscar proyecto que su nombre contenga el texto y pertenezca al cliente en cuestión
 * @param $text
 */
function searchProyecto2($text)
{
    $pdo = Database::connect('stack_bbgest');

    $sql = "SELECT p.*, date_format(p.kickoff, \"%Y\") as year FROM proyectos p where p.nombre like ? and p.cerrado=0";

    $pdo -> exec('SET NAMES utf8'); // METHOD #3

    $q = $pdo->prepare($sql);
    $q->bindValue(1, "%$text%", PDO::PARAM_STR);
    $result = array();
    $count = 0;
    $q->execute();
    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as $row) {

        $result[$count]['nombre'] = $row['nombre'];
        $result[$count]['id'] = $row['id'];
        $result[$count]['ref_proyecto'] = $row['ref'];
        $result[$count]['year'] = $row['year'];

        $count++;
    }
    print json_encode($result);

    Database::disconnect();
}

/**
 * @param $id_cliente, $precios
 * Guardar $precios personalizados en tabla de precios_honorarios para el cliente en cuestión
 */
function saveHonorarios($id_cliente, $precios) {
    //Si existen actualizar, sino crear nuevo
    $pdo = Database::connect('stack_bbgest');
    $sql = "SELECT * FROM precios_honorarios WHERE id_cliente = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($id_cliente));
    $data = $q->fetch();

    if($data && $data[0]) {
        //Update, borrar precios existentes antes de insertar
        $sql = "DELETE from precios_honorarios where id_cliente = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id_cliente));
    }
    //guardar todos los precios para el cliente
    $sql = "INSERT INTO precios_honorarios (
                            id_cliente,
                            id_perfil,
                            precio
                         )
    values(?, ?, ?)";
    $q = $pdo->prepare($sql);

    foreach ($precios as $item) {
        $q->execute(
            array(
                $id_cliente,
                $item['id_perfil'],
                $item['precio']
            )
        );
    }
    Database::disconnect();

    print 1;
}

function uploadFiles(){
    require('UploadHandler.php');

    $otherDir = dirname(__FILE__) . '/files/'.$_REQUEST['otherDir'].'/';
    $otherDir_url = 'lib/files/'.$_REQUEST['otherDir'].'/';

    $options = array(
        // This option will disable creating thumbnail images and will not create that extra folder.
        // However, due to this, the images preview will not be displayed after upload
        'image_versions' => array(),
        'upload_dir'=> $otherDir,
        'upload_url'=> $otherDir_url
    );
    $upload_handler = new UploadHandler($options);
}

function updateHoras()
{
    if (!empty($_POST)) {
        $currentYear = date('y');
        foreach ($_POST as $fields => $horas) {
            $split_fields = explode(':', $fields);
            $id_proyecto = $split_fields[0];
            $id_usuario = $split_fields[1];
            $numSemana = $split_fields[2];

            //echo $id_proyecto.' - '.$id_usuario.' - '.$numSemana.' - '.$horas.'<br>';
            if (!empty($id_proyecto) && !empty($id_usuario) && !empty($numSemana)) {
                $pdo = Database::connect('stack_bbgest');

                try {
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    //buscar si existe la entrada en la bbdd de coeficiente
                    $sql = "SELECT horas from coeficiente where id_proyecto=? and id_usuario=? and numSemana=? and year=?";
                    $q = $pdo->prepare($sql);

                    $q->execute(array($id_proyecto, $id_usuario, $numSemana, $currentYear));
                    $data = $q->fetch();
                } catch (Exception $e) {
                    Database::disconnect();
                    echo "Error al comprobar los datos ".$e;
                    return;
                }

                if ($data) {
                    //Update
                    try {
                        $sql_update = "UPDATE coeficiente SET horas = ? where id_proyecto=? and id_usuario=? and numSemana=? and year=?";
                        $q_update = $pdo->prepare($sql_update);
                        $q_update->execute(array($horas, $id_proyecto, $id_usuario, $numSemana, $currentYear));
                    } catch (Exception $e) {
                        Database::disconnect();
                        echo "Error al actualizar los datos ".$e;
                        return;
                    }
                } else {
                    //Insert
                    try {
                        $sql_insert = "INSERT INTO coeficiente (horas,id_proyecto,id_usuario,numSemana,year) values(?, ?, ?, ?, ?)";
                        $q_insert = $pdo->prepare($sql_insert);
                        $q_insert->execute(array($horas, $id_proyecto, $id_usuario, $numSemana, $currentYear));
                    } catch (Exception $e) {
                        Database::disconnect();
                        echo "Error al actualizar los datos ".$e;
                        return;
                    }
                }
                echo "Updated";
                Database::disconnect();
            } else {
                echo "Datos incompletos";
            }
        }
    } else {
        echo "No hay datos";
    }
}

function updateSalario()
{
    if (!empty($_POST)) {
        $id_usuario = $_POST['userid'];
        $salario = $_POST['salario'];
        $fecha = date('Y-m-d');

        if (!empty($id_usuario)) {
            $pdo = Database::connect('stack_bbgest');
            //Update
            try {
                $sql_update = "insert into salarios (id_usuario, salario, fecha) values (?,?,?)";
                $q_update = $pdo->prepare($sql_update);
                $q_update->execute(array($id_usuario, $salario, $fecha));
            } catch (Exception $e) {
                Database::disconnect();
                echo "Error al actualizar los datos " . $e;
                return;
            }

            echo "Updated";
            Database::disconnect();
        } else {
            echo "Datos incompletos";
        }
    } else {
        echo "No hay datos";
    }
}

function updateCostes()
{
    if (!empty($_POST)) {
        $currentYear = date('y');
        foreach ($_POST as $fields => $numSemana) {
            $fields = str_replace('_','.', $fields);
            $split_fields = explode(':', $fields);
            $costes = $split_fields[0];
            $costes_extra = $split_fields[1];
            $coeficiente = $split_fields[2];

            if (!empty($numSemana)) {
                $pdo = Database::connect('stack_bbgest');

                try {
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    //buscar si existe la entrada en la bbdd de coeficiente
                    $sql = "SELECT 1 from costes where numSemana=? and year=?";
                    $q = $pdo->prepare($sql);

                    $q->execute(array($numSemana, $currentYear));
                    $data = $q->fetch();
                } catch (Exception $e) {
                    Database::disconnect();
                    echo "Error al comprobar los datos ".$e;
                    return;
                }

                if ($data) {
                    //Update
                    try {
                        $sql_update = "UPDATE costes SET costes=?, costes_extra=?, coeficiente=? where numSemana=? and year=?";
                        $q_update = $pdo->prepare($sql_update);
                        $q_update->execute(array($costes, $costes_extra, $coeficiente, $numSemana, $currentYear));
                    } catch (Exception $e) {
                        Database::disconnect();
                        echo "Error al actualizar los datos ".$e;
                        return;
                    }
                } else {
                    //Insert
                    try {
                        $sql_insert = "INSERT INTO costes (costes,costes_extra,coeficiente,numSemana,year) values(?, ?, ?, ?, ?)";
                        $q_insert = $pdo->prepare($sql_insert);
                        $q_insert->execute(array($costes, $costes_extra, $coeficiente, $numSemana, $currentYear));
                    } catch (Exception $e) {
                        Database::disconnect();
                        echo "Error al actualizar los datos ".$e;
                        return;
                    }
                }
                echo "Updated";
                Database::disconnect();
            } else {
                echo "Datos incompletos";
            }
        }
    } else {
        echo "No hay datos";
    }
}

function updateCostesCron()
{
    $currentYear = date('y');
    $weekToSave = date('W')-1;

    $pdo = Database::connect('stack_bbgest');

    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //buscar si existe la entrada en la bbdd de coeficiente
        $sql = "SELECT 1 from costes where numSemana=? and year=?";
        $q = $pdo->prepare($sql);

        $q->execute(array($weekToSave, $currentYear));
        $data = $q->fetch();

        $sql_euros_semana = "SELECT ifnull(sum(co.horas*(select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1560),0) as suma_semanal from coeficiente co 
                                              left join usuarios us on us.id=co.id_usuario 
                                              left join proyectos pr on pr.id=co.id_proyecto 
                                              where co.numSemana=' . $weekToSave . '";

        $q_euros_semana = $pdo->prepare($sql_euros_semana);
        $q_euros_semana->execute(array());
        $data_euros_semana = $q_euros_semana->fetch();

        $sql_proyectos_semana = "select week(pr.ptc) as deli, week(now()),pr.nombre, pr.id, pr.kickoff, 
                                 pr.ptc as delivery_date, ifnull(sum(presu.suma),0) as euros, presu.estado 
                                 from proyectos pr 
                                 left join presu14.presupuesto presu on presu.id_proyecto=pr.id 
                                 where pr.kickoff>0 
                                 AND (presu.estado<>'no aceptado' or presu.estado is null) 
                                 AND week(pr.ptc) >= week(now()) group by pr.id ";

    } catch (Exception $e) {
        Database::disconnect();
        echo "Error al comprobar los datos ".$e;
        return;
    }

    if ($data) {
        //Update
        try {
            $sql_update = "UPDATE costes SET costes=?, costes_extra=?, coeficiente=? where numSemana=? and year=?";
            $q_update = $pdo->prepare($sql_update);
            $q_update->execute(array($data_euros_semana['suma_semanal'], 0.00, $coeficiente, $weekToSave, $currentYear));
        } catch (Exception $e) {
            Database::disconnect();
            echo "Error al actualizar los datos ".$e;
            return;
        }
    } else {
        //Insert
        try {
            $sql_insert = "INSERT INTO costes (costes,costes_extra,coeficiente,numSemana,year) values(?, ?, ?, ?, ?)";
            $q_insert = $pdo->prepare($sql_insert);
            $q_insert->execute(array($data_euros_semana['suma_semanal'], 0.00, $coeficiente, $weekToSave, $currentYear));
        } catch (Exception $e) {
            Database::disconnect();
            echo "Error al actualizar los datos ".$e;
            return;
        }
    }
    echo "Updated";
    Database::disconnect();
}

/**
 * Guardar horas recogidas
 * ($_POST['dusuario'],$_POST['did_proyecto'],$_POST['ndeliverable'],$_POST['dfecha'],$_POST['nhoras']);
 */
function saveHoras($usuario, $id_proyecto, $deliverable, $fecha, $horas)
{
    $pdo = Database::connect('stack_bbgest');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $year = date('Y');
    $semana_activa = date('W');
    $fecha = date('Y-m-d', strtotime($fecha));

    //guardar datos del presupuesto
    $sql = "INSERT INTO coeficiente (
                                id_proyecto,
                                deliverable,
                                id_usuario,
                                year,
                                numSemana,
                                fecha,
                                horas
                              )
      values(?, ?, ?, ?, ?, ?, ?)";
    $q = $pdo->prepare($sql);

    try {
        $q->execute(
            array(
                $id_proyecto,
                $deliverable,
                $usuario,
                $year,
                $semana_activa,
                $fecha,
                $horas)
        );
    } catch (Exception $e) {
        print $e;
    }


    Database::disconnect();

    print $usuario.': '.$id_proyecto.' - '.$deliverable.' - '. $fecha.' - '.$horas.'h<br>';
}

/**
 * Crear cliente
 */
function altaCliente()
{
    $pdo = Database::connect('presu14');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //guardar datos del presupuesto
    $sql = "INSERT INTO empresa (nombre, direccion, cp, cif, entrada) VALUES (?,?,?,?,?)";
    $q = $pdo->prepare($sql);

    try {
        $nombre_cliente = $_POST["nombre"];
        $q->execute(
            array(
                $nombre_cliente,
                $_POST['direccion'],
                $_POST['cp'],
                $_POST['cif'],
                date('Y-m-d')
            )
        );
        $idEmpresa = $pdo->lastInsertId();

        $search = array('.', ' ', '-', '&', '/', 'Á', 'É', 'Í', 'Ó', 'Ú', 'À', 'È', 'Ò', 'Ñ');
        $replace = array('', '', '', '', '', 'A', 'E', 'I', 'O', 'U', 'A', 'E', 'O', 'N');
        $ref_cliente = strtoupper(substr(str_replace($search, $replace, $nombre_cliente), 0, 3));
        $index = 0;
        while (!updateRef($ref_cliente, $idEmpresa, $index)) {
            $index++;
        }

    } catch (Exception $e) {
        print $e;
    }

    Database::disconnect();

    print $idEmpresa;
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

/**
 * Update cliente
 */
function updateCliente()
{
    $pdo = Database::connect('presu14');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //guardar datos del presupuesto
    $sql = "UPDATE empresa SET nombre=?, direccion=?, cp=?, cif=? WHERE id_empresa=?";
    $q = $pdo->prepare($sql);

    try {
        $nombre_cliente = $_POST["nombre"];
        $q->execute(
            array(
                $nombre_cliente,
                $_POST['direccion'],
                $_POST['cp'],
                $_POST['cif'],
                $_POST['id']
            )
        );
        $idEmpresa = $pdo->lastInsertId();

//        $search = array('.', ' ', '-', '&', '/', 'Á', 'É', 'Í', 'Ó', 'Ú', 'À', 'È', 'Ò', 'Ñ');
//        $replace = array('', '', '', '', '', 'A', 'E', 'I', 'O', 'U', 'A', 'E', 'O', 'N');
//        $ref_cliente = strtoupper(substr(str_replace($search, $replace, $nombre_cliente), 0, 3));
//        $index = 0;
//        while (!updateRef($ref_cliente, $idEmpresa, $index)) {
//            $index++;
//        }

    } catch (Exception $e) {
        print $e;
    }

    Database::disconnect();

    print $idEmpresa;
}
