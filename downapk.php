<?php
include_once './controller.php';
class downapk extends controller
{
    public function action()
    {
        $filename  = "xnm_1.0.0.apk";
        
        $downurl =  JICDOMAIN . "/apk/{$filename}";
        header("Location:".$downurl);
    }
}
new downapk();
