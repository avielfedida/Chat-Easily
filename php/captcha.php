<?php
require_once 'config.php';
session_save_path(SESSION_SAVE_PATH);
if(session_id() == '') session_start();
createImage();
exit;


function createImage()
{
    $md5_hash = md5(rand(0,999));
    $security_code = substr($md5_hash, 15, 5);

    $_SESSION["security_code"] = $security_code;

    $width = 150;
    $height = 40;

    $image = ImageCreatetrueColor($width, $height);

    $white = ImageColorAllocate($image, 255, 255, 255);
    $hardGrey = ImageColorAllocate($image, 0, 0, 0);
    $grey = ImageColorAllocate($image, 245, 245, 245);
    $hardGrey = ImageColorAllocate($image, 97, 97, 97);

    ImageFill($image, 0, 0, $grey);

    $fontSize = rand(15, 20);

    $fontDirectory = new DirectoryIterator("../fonts");

    $fontArray = array();

    while($fontDirectory->valid()){
        if($fontDirectory->current()->isDot() == false){
            $fontArray[] = $fontDirectory->getPath() . "/" . $fontDirectory->current()->getFilename();
        }
        $fontDirectory->next();
    }

    ImagettfText($image, $fontSize, 10, ($width/2)/2, $height/2+$fontSize, $hardGrey, $fontArray[rand(0, count($fontArray)-1)], $security_code);

    $x = rand(10, $width/2);
    $y = rand(0, 10);

    $opX = rand($width/2, $width-10);
    $opY = rand($height, $height-20);

    ImageSetThickness($image, 3);

    ImageLine($image, $x, $y, $opX, $opY, $hardGrey);
    ImageLine($image, $x*2, $y, $opX/2, $opY, $hardGrey);

    header("Content-Type: image/png");

    ImagePng($image);

    ImageDestroy($image);
}