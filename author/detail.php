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
        $uid = $this->getRequest('uid');
        if (empty($uid)) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
        $userobj = new User();
        $alioss = new AliOss();
        $userinfo = current($userobj->getUserInfo($uid, 1));
        if (empty($userinfo)) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
        if (!empty($userinfo['avatartime'])) {
            $userinfo['avatar'] = $alioss->getAvatarUrl($uid, $userinfo['avatartime'], 210);
        }
        $creator = new Creator();
        $creatorInfo = $creator->getCreatorInfo($uid);
        if (empty($creatorInfo)) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('userinfo', $userinfo);
        $smartyObj->assign('creatorInfo', $creatorInfo);
        $smartyObj->display("author/detail.html");
    }

}
new authorDetail();

?>

