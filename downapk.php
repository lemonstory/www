<?php
include_once './controller.php';
class downapk extends controller
{
    public function action()
    {
        $filename  = "xnm_official_2.4.apk";
        
        $downurl =  JICDOMAIN . "/apk/{$filename}";
        header("Location:".$downurl);
    }
}
new downapk();
