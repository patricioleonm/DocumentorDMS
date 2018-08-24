<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Pato León
 * Date: 29-08-13
 * Time: 02:38 PM
 * To change this template use File | Settings | File Templates.
 */

require_once(KT_DIR .'/config/dmsDefaults.php');

include_once(KT_DIR .'/plugins/PLRadicador/lib/fpdf/fpdf.php');

class LabelGenerator {

    private $orientacion = "L";
    private $alto = 4;
    private $ancho = 8;
    private $pdf;
    private $path;

    public function LabelGenerator($orientacion, $ancho, $alto){
        $this->orientacion = $orientacion;
        $this->alto = $alto;
        $this->ancho = $ancho;
        $this->pdf = new FPDF($orientacion,'cm',array($alto,$ancho));
        $this->pdf->SetMargins(0,0,0);
        $this->pdf->AddPage();
        $this->pdf->SetAutoPageBreak(false);
        $this->path = KT_DIR.'/plugins/PLRadicador/lib/';
    }

    //metodos publicos
    public function AddCode39($value, $X, $Y){
        // Including all required classes
        require_once(KT_DIR.'/plugins/PLRadicador/lib/barcodegen/class/BCGFontFile.php');
        require_once(KT_DIR.'/plugins/PLRadicador/lib/barcodegen/class/BCGColor.php');
        require_once(KT_DIR.'/plugins/PLRadicador/lib/barcodegen/class/BCGDrawing.php');

// Including the barcode technology
        require_once(KT_DIR.'/plugins/PLRadicador/lib/barcodegen/class/BCGcode39.barcode.php');

// Loading Font
        $font = new BCGFontFile(KT_DIR.'/plugins/PLRadicador/lib/barcodegen/font/Arial.ttf', 15);

// Don't forget to sanitize user inputs

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
            $code->parse($value); // Text
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
            $drawing->setFilename($this->path.'tmp39.png');
        }

// Header that says it is an image (remove it if you save the barcode to a file)
//    header('Content-Type: image/png');
        //   header('Content-Disposition: inline; filename="barcode.png"');

// Draw (or save) the image into PNG format.
        $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
        $this->pdf->Image($this->path.'tmp39.png', $X, $Y);
        unlink($this->path.'tmp39.png');
    }

    public function AddQR($value, $X, $Y){
        include_once(KT_DIR.'/plugins/PLRadicador/lib/phpqrcode/qrlib.php');
        QRcode::png($value,$this->path.'tmp.png','L',2,2);
        $this->pdf->Image($this->path.'tmp.png',$X,$Y);
        unlink($this->path.'tmp.png');
    }

    public function AddText($text, $color, $X, $Y){
        $this->pdf->SetFont("Arial",'',12);
        $color = $this->hex2rgb($color);
        $this->pdf->SetTextColor($color["R"],$color["G"],$color["B"]);
        $this->pdf->SetXY($X,$Y);
        $this->pdf->Write(0.5,$text);
    }

    public function Salvar($filename){
        if($filename == ''){
            $this->pdf->Output();
        }else{
            $this->pdf->Output($filename,'F');
        }
    }

    public function SaveThumbnail($rad_id){
        global $default;
        $this->pdf->Output('tmppreview.pdf');
        // do generation
        $pathConvert = (!empty($default->convertPath)) ? $default->convertPath : 'convert';
        // windows path may contain spaces
        if (stristr(PHP_OS,'WIN')) {
            $cmd = '"'.$pathConvert.'" "tmppreview.pdf" -resize 200x200 "../labels/'.$rad_id.'.png"';
        }
        else {
            $cmd = "{$pathConvert} tmppreview.pdf -resize 200x200 ../labels/{$rad_id}.png";
        }
        $result = KTUtil::pexec($cmd);
        unlink('tmppreview.pdf');
    }
    private function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        $rgb = array("R" => $r, "G" => $g, "B" => $b);
        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }
}
