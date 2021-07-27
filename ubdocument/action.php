<?php

require_once('../../config.php');
require_once('./locallib.php');
require_once($CFG->dirroot.'/local/ubdocument/preferencelib.php');


$like       = optional_param('like', '', PARAM_RAW);
$hate       = optional_param('hate', '', PARAM_RAW);
$paramcid   = optional_param('paramcid', null, PARAM_INT);
$paramcmid  = optional_param('paramcmid', null, PARAM_INT);

if(!empty($like) && $like == '좋아요'){
    plusLike($paramcid, $paramcmid);
    // redirect("http://localhost/mod/forum/view.php?id=5", '이 활동을 좋아합니다.', 2); 
} else if(!empty($hate) && $hate == '싫어요'){
    plusHate($paramcid, $paramcmid);
    // redirect("http://localhost/mod/forum/view.php?id=5", '이 활동을 싫어합니다.', 2); 
} else {
    // redirect("http://localhost/mod/forum/view.php?id=5", '정상적으로 실행되지 않았습니다.', 2); 
}
?>