<?php
require_once(dirname(__FILE__).'/../vendor/autoload.php');
use BigFish\PDF417\PDF417;
use BigFish\PDF417\Renderers\ImageRenderer;
header("Content-type:application/png");
date_default_timezone_set('America/Lima');

$pdf417 = new PDF417();
$pdf417->setColumns(16);

$data = $pdf417->encode($_REQUEST['data']);
$renderer = new ImageRenderer([
    'format' => 'png'
]);

$image = $renderer->render($data);

echo $image;