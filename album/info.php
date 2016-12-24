<?php
include_once '../controller.php';

class info extends controller
{
    function action()
    {
        $result = array();
        $album = new Album();
        $story = new Story();
        $comment = new Comment();
        $useralbumlog = new UserAlbumLog();
        $useralbumlastlog = new UserAlbumLastlog();
        $fav = new Fav();
        $listenobj = new Listen();
        $aliossObj = new AliOss();
        $dataAnalyticsObj = new DataAnalytics();
        $tagNewObj = new TagNew();

        $uid = $this->getUid();

        $album_id = $this->getRequest("albumid", "1");
        $test = $this->getRequest("test", null);

        // 专辑信息
        $result['albuminfo'] = $album->get_album_info($album_id);
        $albumAgeLevelStr = $album->getAgeLevelStr($result['albuminfo']['min_age'], $result['albuminfo']['max_age']);
        $result['albuminfo']['age_str'] = sprintf("适合%s岁", $albumAgeLevelStr);

        $aliossobj = new AliOss();
        if ($result['albuminfo']['cover']) {
            $result['albuminfo']['cover'] = $aliossobj->getImageUrlNg($aliossobj->IMAGE_TYPE_ALBUM,
                $result['albuminfo']['cover'], 460, $result['albuminfo']['cover_time']);
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
        $star_arr = array(1, 2, 3, 4, 5);
        $star_level = floor($result['albuminfo']['star_level'] / $result['albuminfo']['commentnum']);

        //是否微信访问
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $is_weixin = false;
        if (stripos($user_agent, 'MicroMessenger') !== false) {
            // 非微信浏览器禁止浏览
            $is_weixin = true;
        }

        $tagIds = array();
        $tagList = array();
        // 获取当前专辑的标签
        $relationTagList = current($tagNewObj->getAlbumTagRelationListByAlbumIds($album_id));
        if (!empty($relationTagList)) {
            foreach ($relationTagList as $value) {
                $tagIds[] = $value['tagid'];
            }
            if (!empty($tagIds)) {
                $tagIds = array_unique($tagIds);
                $tagInfos = $tagNewObj->getTagInfoByIds($tagIds);
                if (!empty($tagInfos)) {

                    $tagInfo = array();
                    foreach ($tagInfos as $key => $item) {
                        if ($item['status'] != 1) {
                            continue;
                        }
                        $tagInfo['id'] = $item['id'];
                        $tagInfo['name'] = $item['name'];
                        if (!empty($item['cover'])) {
                            $tagInfo['cover'] = $aliossObj->getImageUrlNg($aliossObj->IMAGE_TYPE_TAG, $item['cover'], 0,
                                $item['covertime']);
                        } else {
                            $tagInfo['cover'] = "";
                        }
                        $tagList[] = $tagInfo;
                    }
                }
            }
        }
        $result['tagList'] = $tagList;


        $tagRelationAlbumIds = array();
        $tagRelationAlbumList = array();
        $tagRelationList = array();
        if (!empty($interestTagIds)) {
            // 获取喜好标签的专辑
            $tagRelationList = $dataAnalyticsObj->getRecommendAlbumListByTagids($interestTagIds, 100);
        } else {
            // 未登录、没有喜好的新用户，默认获取本专辑标签相同的其他专辑
            $tagRelationList = $dataAnalyticsObj->getRecommendAlbumListByTagids($tagIds, 100);
        }
        if (!empty($tagRelationList)) {
            foreach ($tagRelationList as $value) {
                // 过滤当前专辑
                if ($value['albumid'] == $album_id) {
                    continue;
                }
                $tagRelationAlbumIds[] = $value['albumid'];
            }
        }
        $recommendAlbumList = array();
        // 获取指定长度的推荐专辑id数组
        if (!empty($tagRelationAlbumIds)) {
            $tagRelationAlbumIds = array_unique($tagRelationAlbumIds);
            // 随机推荐
            shuffle($tagRelationAlbumIds);
            $tagRelationAlbumIds = array_slice($tagRelationAlbumIds, 0, 4);
            $tagRelationAlbumList = $album->getListByIds($tagRelationAlbumIds);

            // 获取推荐语
            $recommenddescObj = new RecommendDesc();
            $recommendDescList = $recommenddescObj->getAlbumRecommendDescList($tagRelationAlbumIds);
        }

        if (!empty($tagRelationAlbumList)) {

            $albumIds = array();
            foreach ($tagRelationAlbumList as $value) {
                $albumInfo = array();
                $albumIds[] = $value['id'];
                $albumInfo['id'] = $value['id'];
                $albumInfo['title'] = $value['title'];
                $albumInfo['star_level'] = $value['star_level'];
                //$albumInfo['story_num'] = $value['story_num'];
                //$albumInfo['intro'] = $value['intro'];
                $albumAgeLevelStr = $album->getAgeLevelStr($value['min_age'], $value['max_age']);
                //$albumInfo['age_str'] = sprintf("(%s)岁", $albumAgeLevelStr);
                if (!empty($value['cover'])) {
                    $albumInfo['cover'] = $aliossObj->getImageUrlNg($aliossObj->IMAGE_TYPE_ALBUM, $value['cover'], 460,
                        $value['cover_time']);
                }
                $albumInfo['listennum'] = 0;
                if (!empty($tagRelationList[$value['id']])) {
                    $albumInfo['listennum'] = $album->format_album_listen_num($tagRelationList[$value['id']]['albumlistennum'] + 0);
                }

                //推荐语
                $albumInfo['recommenddesc'] = "";
                if (!empty($recommendDescList[$album_id])) {
                    $albumInfo['recommenddesc'] = $recommendDescList[$album_id]['desc'];
                }
                $recommendAlbumList[] = $albumInfo;
            }
        }

        $result['recommends'] = $recommendAlbumList;


        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('result', $result);
        $smartyObj->assign('star_level', $star_level);
        $smartyObj->assign('star_arr', $star_arr);
        $smartyObj->assign("is_weixin", $is_weixin);
        $smartyObj->assign('JICDOMAIN', JICDOMAIN);
        $smartyObj->assign('VERSION', VERSION);


        $smartyObj->display("album/info2.html");
    }
}

new info();

