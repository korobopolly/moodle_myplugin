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

$PAGE->set_url('/local/ubdocument/allcourses.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', $pluginname));
$PAGE->set_pagetype('local_ubdocument');
$PAGE->navbar->add(get_string('pluginname', $pluginname), $CFG->wwwroot.'/local/ubdocument/index.php');
$PAGE->navbar->add(get_string('allcourses', $pluginname), $PAGE->url->out());

$PAGE->requires->css('/local/ubdocument/assets/sweetalert/7.24.1/sweetalert2.min.css');
$PAGE->requires->js_call_amd('local_ubdocument/ubdocument', 'table_definition', array());

echo $OUTPUT->header();

$courses = get_allCourses();

// echo '<xmp>';
// print_r($courses);
// echo '</xmp>';

echo '<table class="table table-border">';
echo    '<tr>';
echo        '<td>강좌 코드</td>';
echo        '<td>강좌명</td>';
echo        '<td>생성 시간</td>';
echo    '</tr>';
foreach($courses as $v){
    echo '<tr>';
    echo    "<td>{$v->sortorder}</td>";
    echo    '<td>'.$v->shortname.'</td>';
    echo    "<td>{$v->timecreated}</td>";
    echo '</tr>';
}
echo '</table>';

echo $OUTPUT->footer();
