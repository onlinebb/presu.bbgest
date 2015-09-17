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
        case "saveFact":
            saveFactura();
            break;
        case "updateFact":
            saveFactura(true);
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
        case "bbgest":
            bbgest();
            break;
        case "exportExcel":
            exportExcel($_GET['tipo']);
            break;
    }
}

/**
 * Buscar cliente que su nombre contenga el texto
 * @param $text
 */
function searchClient($text)
{
    $pdo = Database::connect('bbgest');

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

    if (!$isUpdate) {

        //buscar si hay algun presupuesto para este cliente y sacar el último id
        $sql = "SELECT ref from presupuesto where ref like ? order by id desc";
        $q = $pdo->prepare($sql);

        $curYear = date('y');
        //$q->bindValue(1, $_POST['empresa'], PDO::PARAM_STR);
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
                                    nombre_proyecto,
                                    suma,
                                    autor,
                                    fecha_emision
                                  )
          values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
                    $_POST['proyecto'],
                    str_replace(array('.',','),array('','.'),$_POST['suma']),
                    $_SESSION['valid'],
                    date('Y-m-d'))
            );
            $idPresu = $pdo->lastInsertId();
        } catch (Exception $e) {
            print $e;
        }

    } else {

        //actualizar datos del presupuesto
        //$ref_cliente = strtoupper(substr(str_replace(array('.',' ','-', '&', '/'),'',$_POST['cliente']), 0, 3));
        $idPresu = $_POST['id'];

        $sql = "UPDATE presupuesto SET fecha = ?,
                                   id_empresa = ?,
                                   nombre_cliente = ?,
                                   direccion_cliente = ?,
                                   cif_cliente = ?,
                                   cp_cliente = ?,
                                   contacto_cliente = ?,
                                   nombre_proyecto = ?,
                                   suma = ?
          where id = ?";
        $q = $pdo->prepare($sql);

        $fecha = date('Y-m-d', strtotime($_POST['fecha']));

        try {
            $q->execute(
                array(
                    $fecha,
                    $_POST['empresa'],
                    //$ref_cliente,
                    $_POST['cliente'],
                    $_POST['direccion'],
                    $_POST['cif'],
                    $_POST['cp'],
                    $_POST['contacto'],
                    $_POST['proyecto'],
                    str_replace(array('.',','),array('','.'),$_POST['suma']),
                    $idPresu)
            );

        } catch (Exception $e) {
            //print $e;
        }

    }

    if ($isUpdate) {

        //borrar conceptos existentes primero

        $sql = "DELETE from concepto where id_presupuesto = ?";
        $q = $pdo->prepare($sql);

        $q->execute(array($idPresu));
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

    print $idPresu;
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
    //$q->bindValue(1, $_POST['empresa'], PDO::PARAM_STR);
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
                                nombre_proyecto,
                                suma,
                                autor,
                                presu_origen,
                                fecha_emision
                              )
      values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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

    $sql = "SELECT * from presupuesto where id = ?";
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

    $sql = "SELECT * FROM concepto where concat(concepto,concepto_subtitulo,titulo1,titulo2,titulo3,texto) like ?";
    $q = $pdo->prepare($sql);
    $q->bindValue(1, "%$text%", PDO::PARAM_STR);
    $q->execute();
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

/**
 * Guardar factura y conceptos
 */
function saveFactura($isUpdate = false)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
                                    autor
                                  )
          values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
                    str_replace(array('.',','),array('','.'),$_POST['iva']),
                    str_replace(array('.',','),array('','.'),$_POST['total']),
                    $_POST['nombre_cliente'],
                    $_POST['direccion_cliente'],
                    $_POST['cif_cliente'],
                    $_POST['cp_cliente'],
                    $_POST['ref_compras'],
                    $_SESSION['valid'])
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
                                   ref_po = ?
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
                    str_replace(array('.',','),array('','.'),$_POST['iva']),
                    str_replace(array('.',','),array('','.'),$_POST['total']),
                    $_POST['nombre_cliente'],
                    $_POST['direccion_cliente'],
                    $_POST['cif_cliente'],
                    $_POST['cp_cliente'],
                    $_POST['ref_compras'],
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

        if($cobrado >= $suma_presu)
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
 * Carga los datos de la factura
 * @param $id
 */
function loadFactura($id)
{
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT *, factura.fecha_emision AS fecha_emision_factura FROM factura left join presupuesto on factura.presupuesto_asoc = presupuesto.ref where factura.id = ?";
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

    //Buscar Id ultima factura abonada
    $sql = "SELECT ref_abono from factura where ref_abono like ? order by id desc";
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
    $sql = "UPDATE factura set estado = 'abonada', ref_abono = ?, fecha_abono = ? where id = ?";
    $q = $pdo->prepare($sql);

    $fecha = date('Y-m-d');
    $q->execute(array($ref_abono, $fecha, $id_fact));

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
    $sql = "UPDATE factura SET estado = 'cobrada', fecha_cobro = NOW() WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($id));

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

        if($cobrado >= $suma_presu) {
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
    array_push($list, array("Fecha", "Aceptado", "Pendiente", "Presupuestado", "Fact. pendientes", "Fact. Total",
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
                   ifnull(nombre_proyecto,''),
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
            FROM presupuesto order by fecha_emision desc";

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
            FROM factura order by fecha_emision desc";

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