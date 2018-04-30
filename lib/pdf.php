<?php
/**
 * Created by PhpStorm.
 * User: judit
 * Date: 2/04/14
 * Time: 13:25
 */

require_once('../config.php');
require_once('tcpdf/tcpdf.php');

if (!isset($_GET['id']) && !isset($_GET['preview'])) exit;
else {
    require_once('functions.php');
    if (isset($_GET['id'])) {
        $idPresu = $_GET['id'];
        $datosPresu = loadPresupuesto($idPresu);
        $conceptos = loadConceptos($idPresu);
    } else {
        //else preview
        $datosPresu = array(
            "contacto_cliente" => isset($_POST['contacto']) ? $_POST['contacto']:"",
            "direccion_cliente" => isset($_POST['direccion-cliente']) ? $_POST['direccion-cliente']:"",
            "fecha" => isset($_POST['fecha']) ? $_POST['fecha']:"",
            "nombre_proyecto" => isset($_POST['proyecto']) ? $_POST['proyecto']:"",
            "cif_cliente" => isset($_POST['cif-cliente']) ? $_POST['cif-cliente']:"",
            "nombre_cliente" => isset($_POST['nombre-cliente']) ? $_POST['nombre-cliente']:"",
            "ref" => "PREVIEW"
        );

        $conceptos = json_decode($_POST['conceptos'], true);

        /*print "<pre>";
        print_r($conceptos);
        print "</pre>";exit;*/
    }
}

setlocale(LC_ALL, 'es_ES.UTF-8');

// Extend the TCPDF class to create custom Header and Footer
class PresuPDF extends TCPDF
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
            $this->Image($image_file, 55, 10, 50, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
    }

    // Page footer
    public function Footer()
    {
        // Mail + web
        text($this, 'hola@bubblegum.agency', 12, $this->getPageHeight() - 13, 0, 'helvetica', 7, array(249, 89, 120));
        text($this, 'bubblegum.agency', 12, $this->getPageHeight() - 10, 0, 'helvetica', 7, array(249, 89, 120));

        if ($this->page == 1) {
            // Tel/Fax
            text($this, 'Tel +34 934.519.028', 12, $this->getPageHeight() - 100, 0, 'helvetica', 7, array(249, 89, 120));
            text($this, '+34 910.052.312', 16, $this->getPageHeight() - 96, 0, 'helvetica', 7, array(249, 89, 120));

            // Direccion
            text($this, 'Zamora, 46-48 4º 5ª', 12, $this->getPageHeight() - 198, 0, 'helvetica', 7, array(249, 89, 120));
            text($this, '08005 Barcelona', 12, $this->getPageHeight() - 195, 0, 'helvetica', 7, array(249, 89, 120));
            text($this, 'Av. Manoteras, 38', 12, $this->getPageHeight() - 188, 0, 'helvetica', 7, array(249, 89, 120));
            text($this, '28050 Madrid', 12, $this->getPageHeight() - 185, 0, 'helvetica', 7, array(249, 89, 120));
        }

        // Texto vertical izquierda
        $txt = 'Bubblegum Communication Services, S.L. C/Evarist Arnús 65, át. 2ª. 08014 Barcelona. C.I.F. B-64521271. Inscrita en el Reg. Mercantil de Barcelona, Tomo 39604, Folio 128, Hoja B347931, Inscripción 1.';
        text($this, $txt, 8, $this->getPageHeight() - 10, 90, 'helvetica', 6, array(153, 153, 153));
    }

}

// create new PDF document
//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf = new PresuPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
$pdf->SetTitle('BB Presupuesto');
$pdf->SetSubject('BB Presupuesto');

// remove default header/footer
//$pdf->setPrintHeader(false);
//$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(60, 60, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 50);

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

// add a page
$pdf->AddPage();

$fecha = "";
$ref = "";
$cliente = "";
$direccion = "";
$cif = "";
$contacto = "";
$proyecto = "";
$propuesta = "";

if(isset($datosPresu['fecha'])) {
    $fecha = trim(strftime('%e %B %G', strtotime($datosPresu['fecha'])));
}
if(isset($datosPresu['ref'])) {
    $ref = $datosPresu['ref'];
}
if(isset($datosPresu['nombre_cliente'])) {
    $cliente = $datosPresu['nombre_cliente'];
}
if(isset($datosPresu['direccion_cliente'])) {
    $direccion = $datosPresu['direccion_cliente'];
}
if(isset($datosPresu['cif_cliente'])) {
    $cif = $datosPresu['cif_cliente'];
}
if(isset($datosPresu['contacto_cliente'])) {
    $contacto = $datosPresu['contacto_cliente'];
}
if(isset($datosPresu['proyecto'])) {
    $proyecto = $datosPresu['proyecto'];
}
if(isset($datosPresu['nombre_propuesta'])) {
    $propuesta = $datosPresu['nombre_propuesta'];
}

// Pintar cabecera presupuesto
$cabecera =
    '<table cellspacing="0" cellpadding="1" border="0">

        <tr>
            <td width="20%"><b>Presupuesto</b></td>
            <td width="80%">' . $ref . '</td>
        </tr>
        <tr>
           <td><b>Fecha</b></td>
           <td>' . $fecha . '</td>
        </tr>
        <tr>
           <td><b>Cliente</b></td>
           <td>' . $cliente . ' <br>' . nl2br($direccion) . '<br>C.I.F. ' . $cif . '<br></td>
        </tr>
        <tr>
           <td><b>Contacto</b></td>
           <td>' . $contacto . '<br></td>
        </tr>
        <tr>
           <td><b>Proyecto</b></td>
           <td><span style="color:#F95978;font-weight:bold;">' . nl2br($proyecto) . '<br></span></td>
        </tr>
        <tr>
           <td><b>Propuesta</b></td>
           <td><span style="color:#F95978;font-weight:bold;">' . nl2br($propuesta) . '<br></span></td>
        </tr>
    </table>
    <br>
    <div style="border-bottom:1px solid #F95978; color: #F95978;"><b>Concepto</b></div>';

$pdf->writeHTML($cabecera);

$html = '';

//Pintar los elementos de cada concepto segun el orden establecido
foreach ($conceptos as $concepto) {
    $orden = explode(',', $concepto['orden']);

    //inicio tabla
    $html .= '<table nobr="true" cellspacing="0" cellpadding="0" border="0">';

    foreach ($orden as $item) {
        $concep = "";
        $precio = "";

        if(isset($concepto[$item])) {
            $concep = nl2br($concepto[$item]);
        }

        if(isset($concepto['precio_' . $item])) {
            $precio = $concepto['precio_' . $item];
        }

        $html .= pintarConcepto($pdf, $concep, $precio, $estilos[$item]);
    }


    //fin tabla
    $html .= '</table>';
    $pdf->writeHTML($html);
    $html = '';
}

$confi= '<p style="font-size:80%">Confidencialidad:<br>Este documento es confidencial y propiedad de Bubblegum Communication Services, S.L., SLNE (CIF ESB64521271). 
         Esta exclusivamente dirigido al destinatario del mismo (especificado en la tabla al pie) 
         y puede contener información confidencial y/o privilegiada desde el punto de vista legal. 
         Si usted no es el destinatario o su representante, le notificamos que la diseminación, distribución, 
         copia o uso de este mensaje o sus ficheros adjuntos está estrictamente prohibida.</p>';

$pdf->writeHTML($confi);

function pintarConcepto($pdf, $concepto, $precio, $estilo)
{
    if (isset($precio) && $precio != 0)
        $precio = number_format($precio, 2, ',', '.') . ' €';
    else
        $precio = "";

    $html =
        '<tr>
            <td width="80%">' . $estilo . $concepto . '</span></td>
            <td width="20%" style="text-align:right;font-weight:bold;">' . $estilo . $precio . '</span></td>
        </tr>';

    return $html;
}

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
$pdf->Output($ref.'.pdf', 'I');
$pdf->Output('../pdfs/'.date('YmdHms').'.pdf', 'F');