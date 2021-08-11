# Like or Hate Plugin - Applicable to all activity

## warning! 
This plugin modifies the core of the moodle.

## Development environment
moodle 3.9.7+
Apache 2.4.48
PHP 7.2.0
MySQL 5.6

## Setting preferences before using the plugin (required)
### 0. Insert the ubdocument folder into the local folder.

### 1. DB Create
#### Table name : mdl_preference
|name|data type|length|nullable|Default Value|
|:---:|:---:|:---:|:---:|:---:|
|id|BIGINT|10|:black_square_button:|AUTO_INCREMENT|
|userid|BIGINT|10|:black_square_button:|default|
|cid|BIGINT|10|:black_square_button:|default|
|cmid|BIGINT|10|:black_square_button:|default|
|liked|BIGINT|10|:black_square_button:|'0'|
|hate|BIGINT|10|:black_square_button:|'0'|
|chance|TINYINT|1|:black_square_button:|'0'|

### 2. Modifies the core of the moodle
#### mod\assign\view.php
```
require_once($CFG->dirroot.'/local/ubdocument/preferencelib.php'); //ubdocument plugin function call

...

// Get the assign class to
// render the page.
echo $assign->view(optional_param('action', '', PARAM_ALPHA), array('courseid'=>$course->id, 'cmid'=>$cm->id)); //call by value to view function
```

#### mod\assign\locallib.php
```
...
public function view($action='', $args = array()) { //Receive data via $args
        global $PAGE;

        $o = '';
        $mform = null;
        $notices = array();
        $nextpageparams = array();

        if (!empty($this->get_course_module()->id)) {
            $nextpageparams['id'] = $this->get_course_module()->id;
        }

        // Handle form submissions first.
        if ($action == 'savesubmission') {
            $action = 'editsubmission';
            if ($this->process_save_submission($mform, $notices)) {
                $action = 'redirect';
                if ($this->can_grade()) {
                    $nextpageparams['action'] = 'grading';
                } else {
                    $nextpageparams['action'] = 'view';
                }
            }
        } else if ($action == 'editprevioussubmission') {
            $action = 'editsubmission';
            if ($this->process_copy_previous_attempt($notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'editsubmission';
            }
        } else if ($action == 'lock') {
            $this->process_lock_submission();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'removesubmission') {
            $this->process_remove_submission();
            $action = 'redirect';
            if ($this->can_grade()) {
                $nextpageparams['action'] = 'grading';
            } else {
                $nextpageparams['action'] = 'view';
            }
        } else if ($action == 'addattempt') {
            $this->process_add_attempt(required_param('userid', PARAM_INT));
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'reverttodraft') {
            $this->process_revert_to_draft();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'unlock') {
            $this->process_unlock_submission();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'setbatchmarkingworkflowstate') {
            $this->process_set_batch_marking_workflow_state();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'setbatchmarkingallocation') {
            $this->process_set_batch_marking_allocation();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'confirmsubmit') {
            $action = 'submit';
            if ($this->process_submit_for_grading($mform, $notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'view';
            } else if ($notices) {
                $action = 'viewsubmitforgradingerror';
            }
        } else if ($action == 'submitotherforgrading') {
            if ($this->process_submit_other_for_grading($mform, $notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            } else {
                $action = 'viewsubmitforgradingerror';
            }
        } else if ($action == 'gradingbatchoperation') {
            $action = $this->process_grading_batch_operation($mform);
            if ($action == 'grading') {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'submitgrade') {
            if (optional_param('saveandshownext', null, PARAM_RAW)) {
                // Save and show next.
                $action = 'grade';
                if ($this->process_save_grade($mform)) {
                    $action = 'redirect';
                    $nextpageparams['action'] = 'grade';
                    $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) + 1;
                    $nextpageparams['useridlistid'] = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
                }
            } else if (optional_param('nosaveandprevious', null, PARAM_RAW)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grade';
                $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) - 1;
                $nextpageparams['useridlistid'] = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
            } else if (optional_param('nosaveandnext', null, PARAM_RAW)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grade';
                $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) + 1;
                $nextpageparams['useridlistid'] = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
            } else if (optional_param('savegrade', null, PARAM_RAW)) {
                // Save changes button.
                $action = 'grade';
                if ($this->process_save_grade($mform)) {
                    $action = 'redirect';
                    $nextpageparams['action'] = 'savegradingresult';
                }
            } else {
                // Cancel button.
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'quickgrade') {
            $message = $this->process_save_quick_grades();
            $action = 'quickgradingresult';
        } else if ($action == 'saveoptions') {
            $this->process_save_grading_options();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'saveextension') {
            $action = 'grantextension';
            if ($this->process_save_extension($mform)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'revealidentitiesconfirm') {
            $this->process_reveal_identities();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        }

        $returnparams = array('rownum'=>optional_param('rownum', 0, PARAM_INT),
                              'useridlistid' => optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM));
        $this->register_return_link($action, $returnparams);

        // Include any page action as part of the body tag CSS id.
        if (!empty($action)) {
            $PAGE->set_pagetype('mod-assign-' . $action);
        }
        // Now show the right view page.
        if ($action == 'redirect') {
            $nextpageurl = new moodle_url('/mod/assign/view.php', $nextpageparams);
            $messages = '';
            $messagetype = \core\output\notification::NOTIFY_INFO;
            $errors = $this->get_error_messages();
            if (!empty($errors)) {
                $messages = html_writer::alist($errors, ['class' => 'mb-1 mt-1']);
                $messagetype = \core\output\notification::NOTIFY_ERROR;
            }
            redirect($nextpageurl, $messages, null, $messagetype);
            return;
        } else if ($action == 'savegradingresult') {
            $message = get_string('gradingchangessaved', 'assign');
            $o .= $this->view_savegrading_result($message);
        } else if ($action == 'quickgradingresult') {
            $mform = null;
            $o .= $this->view_quickgrading_result($message);
        } else if ($action == 'gradingpanel') {
            $o .= $this->view_single_grading_panel($args);
        } else if ($action == 'grade') {
            $o .= $this->view_single_grade_page($mform);
        } else if ($action == 'viewpluginassignfeedback') {
            $o .= $this->view_plugin_content('assignfeedback');
        } else if ($action == 'viewpluginassignsubmission') {
            $o .= $this->view_plugin_content('assignsubmission');
        } else if ($action == 'editsubmission') {
            $o .= $this->view_edit_submission_page($mform, $notices);
        } else if ($action == 'grader') {
            $o .= $this->view_grader();
        } else if ($action == 'grading') {
            $o .= $this->view_grading_page();
        } else if ($action == 'downloadall') {
            $o .= $this->download_submissions();
        } else if ($action == 'submit') {
            $o .= $this->check_submit_for_grading($mform);
        } else if ($action == 'grantextension') {
            $o .= $this->view_grant_extension($mform);
        } else if ($action == 'revealidentities') {
            $o .= $this->view_reveal_identities_confirm($mform);
        } else if ($action == 'removesubmissionconfirm') {
            $o .= $this->view_remove_submission_confirm();
        } else if ($action == 'plugingradingbatchoperation') {
            $o .= $this->view_plugin_grading_batch_operation($mform);
        } else if ($action == 'viewpluginpage') {
             $o .= $this->view_plugin_page();
        } else if ($action == 'viewcourseindex') {
             $o .= $this->view_course_index();
        } else if ($action == 'viewbatchsetmarkingworkflowstate') {
             $o .= $this->view_batch_set_workflow_state($mform);
        } else if ($action == 'viewbatchmarkingallocation') {
            $o .= $this->view_batch_markingallocation($mform);
        } else if ($action == 'viewsubmitforgradingerror') {
            $o .= $this->view_error_page(get_string('submitforgrading', 'assign'), $notices);
        } else if ($action == 'fixrescalednullgrades') {
            $o .= $this->view_fix_rescaled_null_grades();
        } else {
            // view render #Jeenlee
            $o .= $this->view_submission_page($args['courseid'], $args['cmid']); //call by value to view_submission_page function, It's not an object, it's an array! $args['example(key)'] = (value) use in this format
        }

        return $o;
    }
...
```

#### mod\assign\locallib.php
```
...
/**
     * View submissions page (contains details of current submission).
     *
     * @return string
     */
    protected function view_submission_page($cid,$cmid) {
        global $CFG, $DB, $USER, $PAGE;

        $instance = $this->get_instance();

        $this->add_grade_notices();

        $o = '';

        $postfix = '';
        if ($this->has_visible_attachments()) {
            $postfix = $this->render_area_files('mod_assign', ASSIGN_INTROATTACHMENT_FILEAREA, 0);
        }
        $o .= $this->get_renderer()->render(new assign_header($instance,
                                                      $this->get_context(),
                                                      $this->show_intro(),
                                                      $this->get_course_module()->id,
                                                      '', '', $postfix));

        // Display plugin specific headers.
        $plugins = array_merge($this->get_submission_plugins(), $this->get_feedback_plugins());
        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $o .= $this->get_renderer()->render(new assign_plugin_header($plugin));
            }
        }

        if ($this->can_view_grades()) {
            // Group selector will only be displayed if necessary.
            $currenturl = new moodle_url('/mod/assign/view.php', array('id' => $this->get_course_module()->id));
            $o .= groups_print_activity_menu($this->get_course_module(), $currenturl->out(), true);

            $summary = $this->get_assign_grading_summary_renderable();
            $o .= $this->get_renderer()->render($summary);
        }
        $grade = $this->get_user_grade($USER->id, false);
        $submission = $this->get_user_submission($USER->id, false);

        if ($this->can_view_submission($USER->id)) {
            $o .= $this->view_student_summary($USER, true);
        }

        $o .= setButton($USER->id, $cid, $cmid);    //Add function to place button

        $o .= $this->view_footer();

        \mod_assign\event\submission_status_viewed::create_from_assign($this)->trigger();

        return $o;
    }
...
```

#### mod\forum\view.php
```
require_once($CFG->dirroot.'/local/ubdocument/preferencelib.php'); //ubdocument plugin function call
...
$vote = setButton($USER->id,$course->id,$cmid); //Add function to place button
echo $vote;

echo $OUTPUT->footer();
```

### 3. Access to administrator account to add courses and assignment, forums

### 4. Statistics page   
Development output page <http://localhost/local/ubdocument/index.php?lang=en>    
Statistics page for forums <http://localhost/local/ubdocument/gooder.php>    

### 5. Modify Language Pack
#### local\ubdocument\lang\en\local_ubdocument.php

