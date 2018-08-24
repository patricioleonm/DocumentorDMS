<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Pato León
 * Date: 28-08-13
 * Time: 02:31 AM
 * To change this template use File | Settings | File Templates.
 */
include_once('fpdf/fpdf.php');
include_once('phpqrcode/qrlib.php');

$pdf = new FPDF('L','cm',array(4,8));
$pdf->AddPage();

//genera e inserta QR
QRcode::png('texto para el puto codigo','tmp.png','L',2,2);
$pdf->Image('tmp.png',0.5,1);
unlink('tmp.png');

//genera e inserta CODE39
$file = generateCode39('201399999988');
$pdf->Image($file,2.5,1);
unlink($file);

//inserta texto
$pdf->SetFont("Arial",'',14);
$pdf->SetTextColor(128,0,128);
$pdf->SetXY(0.5,0.5);
//$pdf->Write(0.5,"texto de prueba");

$pdf->Output();

function generateCode39($txtCode){
    // Including all required classes
    require_once('barcodegen/class/BCGFontFile.php');
    require_once('barcodegen/class/BCGColor.php');
    require_once('barcodegen/class/BCGDrawing.php');

// Including the barcode technology
    require_once('barcodegen/class/BCGcode39.barcode.php');

// Loading Font
    $font = new BCGFontFile('barcodegen/font/Arial.ttf', 18);

// Don't forget to sanitize user inputs
    $text = $txtCode;

// The arguments are R, G, B for color.
    $color_black = new BCGColor(0, 0, 0);
    $color_white = new BCGColor(255, 255, 255);

    $drawException = null;
    try {
        $code = new BCGcode39();
        $code->setScale(1); // Resolution
        $code->setThickness(30); // Thickness
        $code->setForegroundColor($color_black); // Color of bars
        $code->setBackgroundColor($color_white); // Color of spaces
        $code->setFont($font); // Font (or 0)
        $code->parse($text); // Text
    } catch(Exception $exception) {
        $drawException = $exception;
    }

    /* Here is the list of the arguments
    1 - Filename (empty : display on screen)
    2 - Background color */
    $drawing = new BCGDrawing('', $color_white);
    if($drawException) {
        $drawing->drawException($drawException);
    } else {
        $drawing->setBarcode($code);
        $drawing->draw();
        $drawing->setFilename('tmp39.png');
    }

// Header that says it is an image (remove it if you save the barcode to a file)
//    header('Content-Type: image/png');
 //   header('Content-Disposition: inline; filename="barcode.png"');

// Draw (or save) the image into PNG format.
    $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
    return 'tmp39.png';
}
?>