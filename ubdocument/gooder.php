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

// echo html_writer::start_tag('button', array('class'=>'btn btn-primary btn-margin btn-save-schema', 'onclick'=>"location.href='gooder.php'"));
// echo '좋아요';
// echo html_writer::end_tag('button');
// echo '<br>';
// print_r(gooder()); //모든 객체를 출력
?>
<form method='post'> <input type='submit' name='like' id='like' class='btn btn-primary' value='좋아요'/></form>
<?php

if(array_key_exists('like',$_POST)){ likefun(); }

echo "$sum_count 명이 좋아합니다.";
echo $OUTPUT->footer();

