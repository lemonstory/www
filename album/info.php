<?php
include_once '../controller.php';
class info extends controller
{
    function action() {
    	$result  = array();
        $album            = new Album();
        $story            = new Story();
        $comment          = new Comment();
        $useralbumlog     = new UserAlbumLog();
        $useralbumlastlog = new UserAlbumLastlog();
        $fav              = new Fav();
        $listenobj        = new Listen();

        $uid = $this->getUid();

        $album_id = $this->getRequest("albumid", "1");

        // 专辑信息
        $result['albuminfo']  = $album->get_album_info($album_id);

        $aliossobj = new AliOss();
        if ($result['albuminfo']['cover']) {
            $result['albuminfo']['cover'] = $aliossobj->getImageUrlNg($aliossobj->IMAGE_TYPE_ALBUM, $result['albuminfo']['cover'], 200, $result['albuminfo']['cover_time']);
        } else {
            $result['albuminfo']['cover'] = $result['albuminfo']['s_cover'];
        }

        // 是否收藏
        $favinfo = $fav->getUserFavInfoByAlbumId($uid, $album_id);
        if ($favinfo) {
            $result['albuminfo']['fav'] = 1;
        } else {
            $result['albuminfo']['fav'] = 0;
        }
        // 收听数量
        $albumlistennum = $listenobj->getAlbumListenNum($album_id);
        if ($albumlistennum) {
            $result['albuminfo']['listennum'] = (int)$albumlistennum[$album_id]['num'];
        } else {
            $result['albuminfo']['listennum'] = 0;
        }
        
        // 专辑收藏数
        $favobj = new Fav();
        $albumfavnum = $favobj->getAlbumFavCount($album_id);
        if ($albumfavnum) {
            $result['albuminfo']['favnum'] = (int)$albumfavnum[$album_id]['num'];
        } else {
            $result['albuminfo']['favnum'] = 0;
        }

        $result['storylist'] = $story->get_album_story_list($album_id);
        // 评论数量
        $result['albuminfo']['commentnum'] = (int)$comment->get_total("`albumid`={$album_id}");
        $result['commentlist'] = $comment->get_comment_list("`albumid`={$album_id}", "ORDER BY `id` DESC ");

        //评论星级数组
        $star_arr = array(1,2,3,4,5);
        $star_level = floor($result['albuminfo']['star_level']/$result['albuminfo']['commentnum']);

        //是否微信访问
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $is_weixin = false;
        if (stripos($user_agent, 'MicroMessenger') !== false) {
            // 非微信浏览器禁止浏览
            $is_weixin = true;
        }
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('result', $result);
        $smartyObj->assign('star_level',$star_level);
        $smartyObj->assign('star_arr',$star_arr);
        $smartyObj->assign("is_weixin",$is_weixin);
        $smartyObj->assign('JICDOMAIN', JICDOMAIN);
        $smartyObj->assign('VERSION', VERSION);
        $smartyObj->display("album/info.html");
    }
}
new info();

