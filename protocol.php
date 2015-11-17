<?php
include_once './controller.php';
class index extends controller
{
    public function action()
    {
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('JICDOMAIN', JICDOMAIN);
        $smartyObj->assign('VERSION', VERSION);
        $smartyObj->display("protocol.html");
    }
}
new index();