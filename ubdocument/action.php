<?php

require_once('../../config.php');
require_once('./locallib.php');

$like = optional_param('like', '', PARAM_RAW);
$hate = optional_param('hate', '', PARAM_RAW);

$vote = getUserGooder();

if($vote == 0){
    if(!empty($like) && $like == '좋아요'){
        likeFun();
        redirect("gooder.php", '이 활동을 좋아합니다.', 2); 
    } else if(!empty($hate) && $hate == '싫어요'){
        hateFun();
        redirect("gooder.php", '이 활동을 싫어합니다.', 2); 
    } else {
        redirect("gooder.php", '정상적으로 실행되지 않았습니다.', 2); 
    }
} else {
    redirect("gooder.php", '이미 참여하였습니다.', 2); 
}

?>