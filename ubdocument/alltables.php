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

$PAGE->set_url('/local/ubdocument/alltables.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', $pluginname));
$PAGE->set_pagetype('local_ubdocument');
$PAGE->navbar->add(get_string('pluginname', $pluginname), $CFG->wwwroot.'/local/ubdocument/index.php');
$PAGE->navbar->add(get_string('alltables', $pluginname), $PAGE->url->out());

$PAGE->requires->css('/local/ubdocument/assets/sweetalert/7.24.1/sweetalert2.min.css');
$PAGE->requires->js_call_amd('local_ubdocument/ubdocument', 'table_definition', array());

echo $OUTPUT->header();

$tables = get_allTables();

// echo '<xmp>';
// print_r($courses);
// echo '</xmp>';

echo '<table class="table table-border">';
echo    '<thead>';
    echo    '<tr>';
    echo        '<th>물리명</th>';
    echo        '<th>논리명</th>';
    echo        '<th>코멘트</th>';
    echo    '</tr>';
echo    '</thead>';
foreach($tables as $v){
    echo '<tbody>';
        echo '<tr>';
        echo    "<td>{$v->physical_name}</td>";
        echo    "<td>{$v->logical_name}</td>";
        echo    "<td>{$v->comment}</td>";
        echo '</tr>';
    echo '</tbody>';
}
echo '</table>';

echo $OUTPUT->footer();

