<?php
/**
 * Created by PhpStorm.
 * User: Judit
 * Date: 2/04/14
 * Time: 13:25
 */

require_once('../config.php');
require_once('tcpdf/tcpdf.php');

$noiva = 0;

if (!isset($_GET['id']) && !isset($_GET['preview'])) exit;
else {
    require_once('functions.php');
    if (isset($_GET['id'])) {
        $idFact = $_GET['id'];
        $datosFactura = loadFactura($idFact);
        $conceptos = loadConceptosFactura($idFact);


        $estado_factura = $datosFactura['estadof'];
        $abonada = 0;
        if($estado_factura == 'abonada') {
            $abonada = 1;
        }

        if(empty($datosFactura['po_ref']) && !empty($datosFactura['ref_po'])) {
            $datosFactura['po_ref'] = $datosFactura['ref_po'];
        }
        else if(empty($datosFactura['po_ref']) && empty($datosFactura['ref_po'])) {
            $datosFactura['po_ref'] = "N/A";
        }

        if(empty($datosFactura['presupuesto_asoc'])) {
            $datosFactura['presupuesto_asoc'] = "N/A";
        }

        $edit = true;

    } else {
        //else preview
        $edit = false;
        $datosFactura = array(
            "ref_presu" => isset($_POST['ref_presu']) ? $_POST['ref_presu']:"",
            "ref_compras" => isset($_POST['ref_compras']) ? $_POST['ref_compras']:"N/A",
            "fecha_emision" => isset($_POST['fecha_emision']) ? $_POST['fecha_emision']:"",
            "fecha_vencimiento" => isset($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento']:"",
            "nombre_cliente" => isset($_POST['nombre-cliente']) ? $_POST['nombre-cliente']:"",
            "direccion_cliente" => isset($_POST['direccion-cliente']) ? $_POST['direccion-cliente']:"",
            "cif_cliente" => isset($_POST['cif-cliente']) ? $_POST['cif-cliente']:"",
            "cp_cliente" => isset($_POST['cp-cliente']) ? $_POST['cp-cliente']:"",
            "condiciones_pago" => isset($_POST['condiciones-pago']) ? $_POST['condiciones-pago']:"",
            "entidad" => isset($_POST['entidad']) ? $_POST['entidad']:"",
            "subtotal" => isset($_POST['subtotal']) ? str_replace(array('.',','),array('','.'),$_POST['subtotal']):"",
            "iva" => isset($_POST['iva']) ? str_replace(array('.',','),array('','.'),$_POST['iva']):"",
            "total" => isset($_POST['total']) ? str_replace(array('.',','),array('','.'),$_POST['total']):"",
            "ref" => "PREVIEW",
            "ref_factura" => "PREVIEW"
        );

        $conceptos = json_decode($_POST['conceptos'], true);

        /*print "<pre>";
        print_r($_POST);
        print "</pre>";exit;*/
    }

    if (isset($_GET['noiva'])) {
        $noiva = $_GET['noiva'];
    }
}

setlocale(LC_ALL, 'es_ES.UTF-8');

// Extend the TCPDF class to create custom Header and Footer
class FactPDF extends TCPDF
{

    //Page header
    public function Header()
    {
        // Logo
        if ($this->page == 1) {
            $image_file = $GLOBALS['basedir'] . '/img/logo-big.png';
            $this->Image($image_file, 10, 10, $this->getPageWidth() - 20, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            $image_file = $GLOBALS['basedir'] . '/img/logo-bb.jpg';
            $this->Image($image_file, 10, 10, 50, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
    }

    // Page footer
    public function Footer()
    {
        // Texto vertical izquierda
        $txt = 'Bubblegum Communication Services, S.L. C/Evarist Arnús 65, át. 2ª. 08014 Barcelona. C.I.F. B-64521271. Inscrita en el Reg. Mercantil de Barcelona, Tomo 39604, Folio 128, Hoja B347931, Inscripción 1.';
        text($this, $txt, 8, $this->getPageHeight() - 10, 90, 'helvetica', 6, array(153, 153, 153));
    }

}

// create new PDF document
$pdf = new FactPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Estilos
$estilos = array
(
    'concepto' => '<span style="color:#F95978; font-weight: bold; text-transform: uppercase;">',
    'concepto_subtitulo' => '<span style="color:#F95978; font-weight: bold;">',
    'titulo1' => '<span style="color:#999999; font-weight: bold; font-size: 9px;"><br>',
    'titulo2' => '<span style="color:#999999; font-size: 9px; text-align: right;">',
    'titulo3' => '<span style="color:#F95978; font-weight: bold; font-size: 12px; text-align: right;"><br>',
    'texto' => '<span style="color:#999999; font-size: 9px;">'
);


// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('BB');
$pdf->SetTitle('BB Factura');
$pdf->SetSubject('BB Factura');

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(15, 60, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/spa.php')) {
    require_once(dirname(__FILE__) . '/lang/spa.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------*/
// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set font
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(153, 153, 153);
$pdf->setOpenCell(false);

// add a page
$pdf->AddPage();

// Pintar cabecera factura
$html =
    '<table cellspacing="0" cellpadding="1" border="0">
        <tr>
            <td width="60%">&nbsp;</td>
            <td width="40%" style="color: #F95978; font-weight: bold; font-size: 20px;"><b>FACTURA</b></td>
        </tr>
    </table>
    <br><br>
    <table cellspacing="0" cellpadding="1" border="0">
        <tr>
            <td width="60%">&nbsp;</td>
            <td width="20%" style="color: #F95978; border: 1px solid #999999;">Fecha emisión:</td>
            <td width="20%" style="color: #999999; border: 1px solid #999999;">'.date('d-m-Y', strtotime($datosFactura['fecha_emision_factura'])).'</td>
        </tr>
        <tr>
            <td width="60%">&nbsp;</td>
            <td width="20%" style="color: #F95978; border: 1px solid #999999;">Presup. asociado:</td>
            <td width="20%" style="color: #999999; border: 1px solid #999999;">'.($edit ? $datosFactura['presupuesto_asoc']:$datosFactura['ref_presu']).'</td>
        </tr>
        <tr>
            <td width="60%">&nbsp;</td>
            <td width="20%" style="color: #F95978; border: 1px solid #999999;">Ref. compras:</td>
            <td width="20%" style="color: #999999; border: 1px solid #999999;">'.($edit ? $datosFactura['po_ref']:$datosFactura['ref_compras']).'</td>
        </tr>
        <tr>
            <td width="60%">&nbsp;</td>
            <td width="20%" style="color: #F95978; border: 1px solid #999999;">'.($abonada ? "Referencia original":"Nuestra referencia").':</td>
            <td width="20%" style="color: #999999; border: 1px solid #999999;">'.($edit ? $datosFactura['ref_factura']:$datosFactura['ref']).'</td>
        </tr>
		'.($abonada ? '<tr><td width="60%">&nbsp;</td><td width="20%" style="color: #F95978; border: 1px solid #999999;">Referencia abono:</td><td width="20%" style="color: #999999; border: 1px solid #999999;">'.$datosFactura['ref_abono'].'</td></tr>':'').'
    </table>
    <br>
    <table cellspacing="0" cellpadding="1" border="0">
        <tr>
            <td width="15%" style="color: #F95978; border: 1px solid #999999;">Destinatario</td>
            <td width="30%" style="border-bottom: 1px solid #999999;">&nbsp;</td>
        </tr>
        <tr>
            <td width="15%" style="color: #999999; border-left: 1px solid #999999;">Contacto</td>
            <td width="30%" style="color: #F95978; border-right: 1px solid #999999;">'.((isset($datosFactura['contacto_cliente']))?$datosFactura['contacto_cliente']:$datosFactura['contacto_cliente']).'</td>
        </tr>
        <tr>
            <td width="15%" style="color: #999999; border-left: 1px solid #999999;">Empresa</td>
            <td width="30%" style="color: #F95978; border-right: 1px solid #999999;">'.((isset($datosFactura['nombre_cliente']))?$datosFactura['nombre_cliente']:$datosFactura['cliente']).'</td>
        </tr>
        <tr>
            <td width="15%" style="color: #999999; border-left: 1px solid #999999;">Dirección</td>
            <td width="30%" style="color: #F95978; border-right: 1px solid #999999;">'.((isset($datosFactura['direccion_cliente']))?$datosFactura['direccion_cliente']:$datosFactura['direccion']).'</td>
        </tr>
        <tr>
            <td width="15%" style="color: #999999; border-left: 1px solid #999999;">C.P.</td>
            <td width="30%" style="color: #F95978; border-right: 1px solid #999999;">'.((isset($datosFactura['cp_cliente']))?$datosFactura['cp_cliente']:$datosFactura['cp']).'</td>
        </tr>
        <tr>
            <td width="15%" style="color: #999999; border-left: 1px solid #999999; border-bottom: 1px solid #999999;">C.I.F.</td>
            <td width="30%" style="color: #F95978; border-right: 1px solid #999999; border-bottom: 1px solid #999999;">'.((isset($datosFactura['cif_cliente']))?$datosFactura['cif_cliente']:$datosFactura['cif']).'</td>
        </tr>
    </table>
    <br><br><br>
    <table cellspacing="0" cellpadding="1" border="0">
        <thead>
            <tr>
                <td width="15%" style="color: #F95978; border: 1px solid #999999;">CONCEPTO</td>
                <td width="85%" style="border-bottom: 1px solid #999999;">&nbsp;</td>
            </tr>
        </thead>
    </table>';

$html .='<table cellspacing="0" cellpadding="1" border="0" style="border: 1px solid #999999;">
            <tr><td colspan="3">&nbsp;</td></tr>';

//Pintar los elementos de cada concepto segun el orden establecido
foreach ($conceptos as $concepto) {
    $orden = explode(',', $concepto['orden']);

    foreach ($orden as $item) {
        $concep = "";
        $precio = "";

        if(isset($concepto[$item])) {
            $concep = nl2br($concepto[$item]);
        }

        if(isset($concepto['precio_' . $item])) {
            $precio = $concepto['precio_' . $item];
        }

        $html .= pintarConcepto($concep, $precio, $estilos[$item], $abonada);
    }
}

function pintarConcepto($concepto, $precio, $estilo, $abonada)
{
    if (isset($precio) && $precio != 0) {
        if($abonada) {
            $precio = '-'.number_format($precio, 2, ',', '.') . ' €';
        }
        else {
            $precio = number_format($precio, 2, ',', '.') . ' €';
        }

        $texto_precio = "Precio:";
        $add = '<tr><td colspan="3">&nbsp;</td></tr>';
    }
    else {
        $precio = "";
        $texto_precio = "";
        $add = '';
    }

    if(empty($concepto)) {
        $html = '<tr><td colspan="3">&nbsp;</td></tr>';
    }
    else {
        $html =
            '<tr nobr="true">
				<td width="60%" style="color: #999999;">'. $estilo . $concepto . '</span></td>
				<td width="20%" style="color: #F95978;">'. $estilo . $texto_precio.'</span></td>
				<td width="20%" style="color: #999999; text-align: right;">'. $estilo . $precio . '</span></td>
			 </tr>'.$add;
    }

    return $html;
}

//Datos entidad bancaria
$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT * from cuentas_bancarias where id = ?";
$q = $pdo->prepare($sql);

if($edit)
    $entidad = $datosFactura['datos_bancarios'];
else
    $entidad = $datosFactura['entidad'];

$q->execute(array($entidad));
$cuenta = $q->fetch();

Database::disconnect();

if($noiva) {
    $precio = '<table cellspacing="0" cellpadding="1" border="0">
        <tr>
            <td width="60%">&nbsp;</td>
            <td width="20%" style="color: #F95978; border-bottom: 1px solid #999999; border-left: 1px solid #999999;"><b>TOTAL</b></td>
            <td width="20%" style="color: #999999; text-align: right; border-bottom: 1px solid #999999; border-right: 1px solid #999999;">' . ($abonada ? '-':'').number_format($datosFactura['subtotal'], 2, ',', '.'). ' €'.'</td>
        </tr>
    </table>';
}
else {
    $precio = '<table cellspacing="0" cellpadding="1" border="0">
        <tr>
            <td width="60%">&nbsp;</td>
            <td width="20%" style="color: #F95978; border-bottom: 1px solid #999999; border-left: 1px solid #999999;">Subtotal:</td>
            <td width="20%" style="color: #999999; text-align: right; border-bottom: 1px solid #999999; border-right: 1px solid #999999;">' . ($abonada ? '-':'').number_format($datosFactura['subtotal'], 2, ',', '.') . ' €'.'</td>
        </tr>
        <tr>
            <td width="60%">&nbsp;</td>
            <td width="20%" style="color: #F95978; border-bottom: 1px solid #999999; border-left: 1px solid #999999;">I.V.A. (21%):</td>
            <td width="20%" style="color: #999999; text-align: right; border-bottom: 1px solid #999999; border-right: 1px solid #999999;">' . ($abonada ? '-':'').number_format($datosFactura['iva'], 2, ',', '.'). ' €'.'</td>
        </tr>
        <tr>
            <td width="60%">&nbsp;</td>
            <td width="20%" style="color: #F95978; border-bottom: 1px solid #999999; border-left: 1px solid #999999;"><b>TOTAL</b></td>
            <td width="20%" style="color: #999999; text-align: right; border-bottom: 1px solid #999999; border-right: 1px solid #999999;">' . ($abonada ? '-':'').number_format($datosFactura['total'], 2, ',', '.'). ' €'.'</td>
        </tr>
    </table>';
}

$html .= '<tr><td colspan="3">&nbsp;</td></tr>
    </table>

    '.$precio.'

    <table cellspacing="0" cellpadding="1" border="0">
        <tr>
            <td width="20%" style="color: #F95978; border: 1px solid #999999;">Fecha de vencimiento</td>
            <td width="20%">&nbsp;</td>
        </tr>
        <tr>
            <td width="40%" colspan="2" style="color: #999999; border: 1px solid #999999; text-align: center">'.date('d-m-Y', strtotime($datosFactura['fecha_vencimiento'])).'</td>
        </tr>
    </table>
    <br><br>
    <table cellspacing="0" cellpadding="1" border="0">
        <tr>
            <td width="20%" style="color: #F95978; border: 1px solid #999999;">Condiciones de pago</td>
            <td width="20%">&nbsp;</td>
        </tr>
        <tr>
            <td width="40%" colspan="2" style="color: #999999; border: 1px solid #999999; text-align: center">'.$datosFactura['condiciones_pago'].'</td>
        </tr>
    </table>
    <br><br>
    <table cellspacing="0" cellpadding="1" border="0">
        <tr>
            <td width="20%" colspan="2" style="color: #F95978; border: 1px solid #999999;">Datos bancarios</td>
            <td width="20%" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <!--<td width="10%" style="color: #F95978; border: 1px solid #999999; text-align: center">'.$cuenta['entidad'].'</td>
            <td width="10%" style="color: #F95978; border: 1px solid #999999; text-align: center">'.$cuenta['oficina'].'</td>
            <td width="10%" style="color: #F95978; border: 1px solid #999999; text-align: center">'.$cuenta['dc'].'</td>-->
            <td width="40%" colspan="4" style="color: #F95978; border: 1px solid #999999; text-align: center">'.$cuenta['iban'].'</td>
        </tr>
        <tr>
            <td width="40%" colspan="4" style="color: #F95978; border: 1px solid #999999; text-align: center">'.$cuenta['nombre'].'</td>
        </tr>
    </table>
    ';

$pdf->writeHTML($html);

function text($pdf, $s, $x, $y, $angle, $font, $size, $color)
{
    $pdf->StartTransform();
    $pdf->SetFont($font, '', $size);
    $pdf->SetTextColor($color[0], $color[1], $color[2]);
    //$pdf->SetAlpha ( (127-$color[3])/127 ); // change from PHP to PDF units for transparency
    $pdf->Rotate($angle, $x, $y); // rotate origin
    $pdf->Text($x, $y, $s, FALSE, FALSE, TRUE, 0, 0, '', FALSE, '', 0, FALSE, 'L');
    $pdf->StopTransform();
}


// Close and output PDF document
$pdf->Output($datosFactura["ref_factura"].'.pdf', 'I');