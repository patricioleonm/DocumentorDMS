<?php


include_once(realpath('.').'/../../../config/dmsDefaults.php');
include_once(KT_DIR.'/plugins/PLRadicador/lib/LabelGenerator.php');

$aData = array();
foreach($_GET as $item=>$value){
    $aData[$item] = $value;
}
//print_r($aData);
$pdf = new LabelGenerator($aData['orientacion'],$aData['alto'],$aData['largo']);
if($aData['cb'] == 'true'){ //codigo 39
    $pdf->AddCode39('201399999988',$aData['cb_h'],$aData['cb_v']);
}
if($aData['qr'] == 'true'){ //codigo qr
    $pdf->AddQR('201399999988',$aData['qr_h'],$aData['qr_v']);
}
if($aData['fecha'] == 'true'){ //fecha
    $pdf->AddText(date('d-m-Y H:mA'),$aData['fecha_color'], $aData['fecha_h'], $aData['fecha_v']);
}
if($aData['remitente'] == 'true'){ //remitente
    $pdf->AddText('Don Ramon',$aData['remitente_color'], $aData['remitente_h'], $aData['remitente_v']);
}
if($aData['destinatario'] == 'true'){ //destinatario
    $pdf->AddText('Chavo del 8',$aData['destinatario_color'], $aData['destinatario_h'], $aData['destinatario_v']);
}
if($aData['texto1'] == 'true'){ //texto1
    $pdf->AddText($aData['texto1_txt'],$aData['texto1_color'], $aData['texto1_h'], $aData['texto1_v']);
}
if($aData['texto2'] == 'true'){ //texto2
    $pdf->AddText($aData['texto2_txt'],$aData['texto2_color'], $aData['texto2_h'], $aData['texto2_v']);
}

header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s',time()+60*60*8 ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

if($aData["savethumbnail"] == 'true'){
    $pdf->SaveThumbnail($aData['rad_id']);
}else{
    $pdf->Salvar('');
}
?>