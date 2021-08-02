<?php
require_once('../../config.php');
require_once('./locallib.php');

$pluginname = 'local_ubdocument';

$context = context_system::instance();
if (!has_capability('local/ubdocument:view', $context)) {
    throw new moodle_exception(get_string('nopermission', $pluginname));
}

$PAGE->set_url('/local/ubdocument/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', $pluginname));
$PAGE->set_pagetype('local_ubdocument');
$PAGE->navbar->add(get_string('pluginname', $pluginname), $CFG->wwwroot.'/local/ubdocument/index.php');

$PAGE->requires->css('/local/ubdocument/assets/sweetalert/7.24.1/sweetalert2.min.css');
$PAGE->requires->js_call_amd('local_ubdocument/ubdocument', 'index', array());

echo $OUTPUT->header();
//<h2>사이트 관리</h2>

echo html_writer::start_div('', array('class'=>'tab-content mt-3'));
    echo html_writer::start_div('',array('class'=>'tab-pane active', 'id'=>'linkroot', 'role'=>'tabpanel'));
        echo html_writer::start_div('', array('class'=>'container'));
            echo html_writer::start_div('', array('class'=>'row'));
                echo html_writer::start_div('', array('class'=>'col'));
                    echo html_writer::start_div('', array('class'=>'list-unstyled'));
                        $xmldb = html_writer::link('/admin/tool/xmldb/index.php?action=generate_all_documentation',get_string('xmldb', $pluginname));
                        echo html_writer::tag('li',$xmldb,array());
                        $define = html_writer::link('table_definition.php',get_string('table_definition', $pluginname));
                        echo html_writer::tag('li',$define,array());
                        $course = html_writer::link('allcourses.php',get_string('allcourses', $pluginname));
                        echo html_writer::tag('li',$course,array());
                        $user = html_writer::link('allusers.php',get_string('allusers', $pluginname));
                        echo html_writer::tag('li',$user,array());
                        $table = html_writer::link('alltables.php',get_string('alltables', $pluginname));
                        echo html_writer::tag('li',$table,array());
                        $table = html_writer::link('allcolumns.php',get_string('allcolumns', $pluginname));
                        echo html_writer::tag('li',$table,array());
                        $table = html_writer::link('table_definition_en.php',get_string('table_definition_en', $pluginname));
                        echo html_writer::tag('li',$table,array());
                        $count = html_writer::link('count.php',get_string('count', $pluginname));
                        echo html_writer::tag('li',$count,array());
                        $gooder = html_writer::link('gooder.php',get_string('gooder', $pluginname));
                        echo html_writer::tag('li',$gooder,array());
                    echo html_writer::end_div();
                echo html_writer::end_div();
            echo html_writer::end_div();
        echo html_writer::end_div();
    echo html_writer::end_div();
echo html_writer::end_div();

//html_writer 도움말
// echo html_writer::div('<li>안에 div 가 또 생기니?</li>', "tab-content mt-3", array('style'=>'color:red'));
// echo html_writer::div('', "tab-pane active", array('id'=>'linkroot'));
// echo html_writer::div('', "container");
// echo html_writer::div('', "row");
// echo html_writer::div('', "col");
// echo html_writer::div($define, "list-unstyled");
// echo html_writer::div($a, "tab-content mt-3", array('id'=>'link_tabledefinition'));

echo $OUTPUT->footer();