<?php

require_once('../../config.php');
require_once('./locallib.php');
require_once($CFG->dirroot.'/local/ubdocument/preferencelib.php');


$like       = optional_param('like', '', PARAM_RAW);
$hate       = optional_param('hate', '', PARAM_RAW);
$paramuserid   = optional_param('paramuserid', null, PARAM_INT);
$paramcid   = optional_param('paramcid', null, PARAM_INT);
$paramcmid  = optional_param('paramcmid', null, PARAM_INT);

if(!empty($like) && $like == '좋아요'){
    plusLike($paramuserid,$paramcid, $paramcmid);
} else if(!empty($hate) && $hate == '싫어요'){
    plusHate($paramuserid,$paramcid, $paramcmid);
} else {
}
?>