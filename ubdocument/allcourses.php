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

switch($proc_type) {
    case 'IMPORT-SCHEMA':
        local_ubdocument_updateTableSchema($lang);
        redirect('table_definition.php?lang='.$lang, get_string('importedtodatabase', $pluginname), 1000);
        break;
    case 'SAVE-SCHEMA':
        foreach($_POST as $key=>$value) {
        
            if (preg_match("~table_logical_name_([0-9]*)~", $key, $match)) {
                $id = !empty($match[1]) ? $match[1] : 0;
                if (is_number($id) && $id > 0) {
                    $DB->set_field('local_ubdocument_tables', 'logical_name', $value, array('id'=>$id));
                }
            } elseif (preg_match("~table_comment_([0-9]*)~", $key, $match)) {
                $id = !empty($match[1]) ? $match[1] : 0;
                if (is_number($id) && $id > 0) {
                    $DB->set_field('local_ubdocument_tables', 'comment', $value, array('id'=>$id));
                }
            } elseif (preg_match("~field_logical_name_([0-9]*)~", $key, $match)) {
                $id = !empty($match[1]) ? $match[1] : 0;
                if (is_number($id) && $id > 0) {
                    $DB->set_field('local_ubdocument_table_columns', 'logical_name', $value, array('id'=>$id));
                }
            } elseif (preg_match("~field_comment_([0-9]*)~", $key, $match)) {
                $id = !empty($match[1]) ? $match[1] : 0;
                if (is_number($id) && $id > 0) {
                    $DB->set_field('local_ubdocument_table_columns', 'column_comment', $value, array('id'=>$id));
                }
            }
        }
        redirect('table_definition.php?lang='.$lang, get_string('datasaved', $pluginname), 1000);
        break;
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
/*
if ($tables = local_ubdocument_getTableDefinition($lang,true)) {
    echo html_writer::start_div('well clearfix');
        echo html_writer::start_div('float-left');
            echo html_writer::start_tag('form', array('id'=>'form-search'));
                $options = get_string_manager()->get_list_of_translations();
                echo html_writer::select($options, 'lang', $lang, null, array('class'=>'form-control form-auto-sumit'));
            echo html_writer::end_tag('form');
        echo html_writer::end_div();
        echo html_writer::start_div('float-right');
            echo html_writer::link('table_definition.php?proc_type=IMPORT-SCHEMA&sesskey='.sesskey().'&lang='.$lang, get_string('importtodatabase', $pluginname), array('id'=>'table-init', 'class'=>'btn btn-info btn-margin'));
            echo html_writer::start_tag('button', array('class'=>'btn btn-primary btn-margin btn-save-schema'));
                echo html_writer::span(get_string('save', $pluginname));
            echo html_writer::end_tag('button');
        echo html_writer::end_div();
    echo html_writer::end_div();
    
    
    $datas = array(
        'languages'=>$languages, 
        'lang'=>$lang, 
        'tables'=>$tables);
    echo $OUTPUT->render_from_template('local_ubdocument/table_definition', $datas);
    
}
*/

$test = get_allCourses();
echo '<xmp>';
print_r($test);
echo '</xmp>';

echo $OUTPUT->footer();

