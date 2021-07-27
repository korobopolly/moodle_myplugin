<?php
require_once('../../config.php');
require_once('./locallib.php');

$pluginname = 'local_ubdocument';

$lang = optional_param('lang', current_language(), PARAM_LANG);
$proc_type = optional_param('proc_type', null, PARAM_RAW);

$context = context_system::instance();
if (!has_capability('local/ubdocument:view', $context)) {
    throw new moodle_exception(get_string('nopermission', $pluginname));
}

$languages = get_string_manager()->get_list_of_translations();
if (!array_key_exists($lang, $languages)) {
    redirect('table_definition.php?lang=en', get_string('isnotallowlang', $pluginname, $lang), 1000);
}

$PAGE->set_url('/local/ubdocument/gooder.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', $pluginname));
$PAGE->set_pagetype('local_ubdocument');
$PAGE->navbar->add(get_string('pluginname', $pluginname), $CFG->wwwroot.'/local/ubdocument/index.php');
$PAGE->navbar->add(get_string('gooder', $pluginname), $PAGE->url->out());

$PAGE->requires->css('/local/ubdocument/assets/sweetalert/7.24.1/sweetalert2.min.css');
$PAGE->requires->js_call_amd('local_ubdocument/ubdocument', 'table_definition', array());

echo $OUTPUT->header();

$user_gooder_check = getUserGooder(); // 현재 접속자의 좋아요 참여 여부

if($user_gooder_check != 1){   // 유저의 gooder 필드를 체크해서 출력여부판단
    //좋아요 싫어요 버튼
    echo html_writer::start_tag('form', array('method' => 'post', 'action'=>'action.php'));
        $like=array('type' => 'submit', 'name' => 'like', 'id' => 'like', 'value' => '좋아요', 'class' => 'btn btn-primary');
        echo html_writer::empty_tag('input', $like);
        echo "&nbsp&nbsp";
        $hate=array('type' => 'submit', 'name' => 'hate', 'id' => 'hate', 'value' => '싫어요', 'class' => 'btn btn-info btn-margin');
        echo html_writer::empty_tag('input', $hate);
    echo html_writer::end_tag('form');
} else {
    //disabled
    echo html_writer::start_tag('form', array('method' => 'post', 'action'=>'action.php'));
        $like=array('type' => 'submit', 'name' => 'like', 'id' => 'like', 'value' => '좋아요', 'class' => 'btn', 'style'=>'background-color:#696969; color:white;', 'disabled'=>'disabled');
        echo html_writer::empty_tag('input', $like);
        echo "&nbsp&nbsp";
        $hate=array('type' => 'submit', 'name' => 'hate', 'id' => 'hate', 'value' => '싫어요', 'class' => 'btn btn-margin', 'style'=>'background-color:#696969; color:white;', 'disabled'=>'disabled');
        echo html_writer::empty_tag('input', $hate);
    echo html_writer::end_tag('form');
    echo html_writer::tag('p','해당 평가는 한번만 가능합니다.');
}

$point_count = showFun();

echo html_writer::start_div();
echo "이 활동을 <b>$point_count</b><b>명</b>이 좋아합니다.";
echo html_writer::end_div();


echo $OUTPUT->footer();

