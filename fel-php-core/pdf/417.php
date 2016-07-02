<?php
require_once dirname(__FILE__).'/../vendor/autoload.php';
use BigFish\PDF417\PDF417;
use BigFish\PDF417\Renderers\ImageRenderer;
use BigFish\PDF417\Renderers\SvgRenderer;
$text = isset($_REQUEST['data'])?$_REQUEST['data']:'';
$pdf417 = new PDF417();
$data = $pdf417->encode($text);
$renderer = new ImageRenderer(['format'=>'png']);
$image = $renderer->render($data);
Header('Content-type: image/png');
echo $image->encoded;