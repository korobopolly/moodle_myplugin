<?php
    require_once('../../config.php');
    require_once('./locallib.php');
    //
/** 
    function showPreference($cmid, $courseid){
        $sql = "SELECT * 
                FROM mdl_course c
                JOIN mdl_course_modules cm ON cm.course = c.id
                JOIN mdl_preference p ON p.cmid = cm.id
                WHERE c.id = {$cmid} AND cm.id = {$courseid} ";

        if($courseid=empty){
            //inserte sql구문
        }
        else{
            //update sql 구문
        }
    }
*/