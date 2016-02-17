<?php
include_once './controller.php';
class downapk extends controller
{
    public function action()
    {
        $filename  = "xnm_official_1.3.apk";
        
        $downurl =  JICDOMAIN . "/apk/{$filename}";
        header("Location:".$downurl);
    }
}
new downapk();
