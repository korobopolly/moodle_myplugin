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
    function plusLike($userid, $cid, $cmid){
        global $DB;

        $sql = "SELECT cm.id, m.name FROM {modules} m JOIN {course_modules} cm ON cm.module = m.id WHERE cm.course = :cid AND cm.id = :cmid ";
        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid));

        $usemodulename = $datas->name;
        $usemoduleid = $datas->id;

        $sql = "SELECT c.id cid, cm.id cmid, cm.instance instanceid, p.id preferenceid, p.liked, p.hate, p.chance, m.name modulename
                FROM {course} c
                JOIN {course_modules} cm ON cm.course = c.id
                JOIN {preference} p ON p.cmid = cm.id
                JOIN {modules} m ON m.id = cm.module
                WHERE c.id = :cid AND cm.id = :cmid AND userid = :userid";

        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid, 'userid'=>$userid));

        

        if(empty($datas)){
            // insert
            $object_like = new stdClass();
            $object_like->userid = $userid;
            $object_like->cid = $cid;
            $object_like->cmid = $cmid;
            $object_like->liked = 1;
            $object_like->hate = 0;
            $object_like->chance = 1;

            $DB->insert_record('preference', $object_like);

            redirect("/mod/".$usemodulename."/view.php?id=".$usemoduleid, '이 활동을 좋아합니다.', 2);
        } else {
            // update
            $object_like = new stdClass();
            $object_like->userid = $userid;
            $object_like->cid = $cid;
            $object_like->cmid = $cmid;
            $object_like->liked = 1;
            $object_like->hate = 0;
            $object_like->chance = 1;
            
            $DB->update_record('preference', $object_like);
            redirect("/mod/".$usemodulename."/view.php?id=".$usemoduleid, '이 활동을 좋아합니다.', 2); 
        }
    }

    function plusHate($userid, $cid, $cmid){
        global $DB;

        $sql = "SELECT cm.id, m.name FROM {modules} m JOIN {course_modules} cm ON cm.module = m.id WHERE cm.course = :cid AND cm.id = :cmid ";
        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid));

        $usemodulename = $datas->name;
        $usemoduleid = $datas->id;

        $sql = "SELECT c.id cid, cm.id cmid, cm.instance instanceid, p.id preferenceid, p.liked, p.hate, p.chance, m.name modulename
                FROM {course} c
                JOIN {course_modules} cm ON cm.course = c.id
                JOIN {preference} p ON p.cmid = cm.id
                JOIN {modules} m ON m.id = cm.module
                WHERE c.id = :cid AND cm.id = :cmid AND userid = :userid";

        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid, 'userid'=>$userid));

        if(empty($datas)){
            // insert
            $object_like = new stdClass();
            $object_like->userid = $userid;
            $object_like->cid = $cid;
            $object_like->cmid = $cmid;
            $object_like->liked = 0;
            $object_like->hate = 1;
            $object_like->chance = 1;

            $DB->insert_record('preference', $object_like);

            redirect("/mod/".$usemodulename."/view.php?id=".$usemoduleid, '이 활동을 싫어합니다.', 2);
        } else {
            // update
            $object_like = new stdClass();
            $object_like->userid = $userid;
            $object_like->cid = $cid;
            $object_like->cmid = $cmid;
            $object_like->liked = 0;
            $object_like->hate = 1;
            $object_like->chance = 1;
            
            $DB->update_record('preference', $object_like);
            redirect("/mod/".$usemodulename."/view.php?id=".$usemoduleid, '이 활동을 싫어합니다.', 2);           
        }
    }

    function getChance($userid,$cid, $cmid){
        global $DB;

        $sql = "SELECT * 
                FROM mdl_course c
                JOIN mdl_course_modules cm ON cm.course = c.id
                JOIN mdl_preference p ON p.cmid = cm.id
                WHERE c.id = :cid AND cm.id = :cmid AND p.userid = :userid ";

        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid, 'userid'=>$userid));

        if(empty($datas->chance)){
            $chancedata = 0;
        } else {
            $chancedata = $datas->chance;
        }

        return $chancedata;
    }
    
    function getTotal($cid, $cmid){
        global $DB;

        $sql = "SELECT SUM(liked) total_like, SUM(hate) total_hate
                FROM mdl_preference
                WHERE cid = :cid AND cmid = :cmid";

        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid));
                                                        
        if($datas->total_like == 0){
            $datas -> total_like = 0;
            $datas -> total_hate = 0;
        }
        
        return $datas;
    }