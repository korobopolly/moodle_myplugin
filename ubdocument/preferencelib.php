<?php
    require_once('../../config.php');

    //좋아요, 싫어요, 횟수 가져오는 함수
    function showPreference($cid, $cmid){
        global $DB;
        
        $sql = "SELECT * 
                FROM mdl_course c
                JOIN mdl_course_modules cm ON cm.course = c.id
                JOIN mdl_preference p ON p.cmid = cm.id
                WHERE c.id = :cid AND cm.id = :cmid "; //무들에서 쓰는 데이터 공간 만들어주는 방법 {$cid} 와 같음

        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid));

        $return_obj = new stdClass();
        $return_obj->like = $datas->like;
        $return_obj->hate = $datas->hate;
        $return_obj->chance = $datas->chance;
        
        return $return_obj;
    }


    // 버튼을 눌렀을때 값을 insert || update
    function sadasd($cid, $cmid){
        global $DB;

        $sql = "SELECT * 
                FROM mdl_course c
                JOIN mdl_course_modules cm ON cm.course = c.id
                JOIN mdl_preference p ON p.cmid = cm.id
                WHERE c.id = :cid AND cm.id = :cmid ";

        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid));

        if(empty($datas)){
            // insert
        } else {
            // update
        }
    }
    