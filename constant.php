<?php
/**
 * Created by PhpStorm.
 * User: hoangthanh
 * Date: 01-May-18
 * Time: 9:42 AM
 */
$arrQualityConst = array('Awful', 'Shoddy', 'Poor', 'Normal', 'Good', 'Superior', 'Excellent', 'Masterwork', 'Legendary');
const fileTMP = './assets/file-tmp/file.rws';

if(!file_exists('./assets/file-tmp')){
    mkdir('./assets/file-tmp');
}

if(!file_exists(fileTMP)){
    $myfile = fopen(fileTMP, "w");
    fclose($myfile);
}
if ( ! session_id() ) @ session_start();

$mode = array_key_exists('mode',$_GET) ? $_GET['mode'] : null;
$scriptName = $_SERVER['SCRIPT_NAME'];
if($mode == 'save-file'&& $_SESSION['token']) {

    $content = $_SESSION['data-resource'];
    $formFileName = $_SESSION['form-file-name'];
    file_put_contents(fileTMP, $content);

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $formFileName . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize(fileTMP));
    readfile(fileTMP);

    unlink(fileTMP);
    unset($_SESSION['token']);
    unset($_SESSION['data-resource']);
    unset($_SESSION['form-file-name']);
}