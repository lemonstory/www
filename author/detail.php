<?php
/**
 * 作者-人物百科
 * Date: 16/10/14
 * Time: 上午10:59
 */

include_once '../controller.php';
class authorDetail extends controller
{
    public function action()
    {

        $smartyObj = $this->getSmartyObj();
        
        $smartyObj->display("author/detail.html");
    }

}
new authorDetail();

?>

