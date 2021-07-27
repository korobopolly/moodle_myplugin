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
    function plusLike($cid, $cmid){
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
                WHERE c.id = :cid AND cm.id = :cmid ";

        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid));

        

        if(empty($datas)){
            // insert
            $object_like = new stdClass();
            $object_like->cmid = $cmid;
            $object_like->liked = 1;
            $object_like->hate = 0;
            $object_like->chance = 1;

            $DB->insert_record('preference', $object_like);

            redirect("/mod/".$usemodulename."/view.php?id=".$usemoduleid, '이 활동을 좋아합니다.', 2);
        } else {
            // update
            $object_like = new stdClass();
            $object_like->id = $datas->preferenceid;
            $object_like->cmid = $cmid;
            $object_like->liked = (int)$datas->liked + 1;
            $object_like->hate = (int)$datas->hate;
            $object_like->chance = 1;
            

            $DB->update_record('preference', $object_like);
            redirect("/mod/".$usemodulename."/view.php?id=".$usemoduleid, '이 활동을 좋아합니다.', 2); 
        }
    }

    function plusHate($cid, $cmid){
        global $DB;

        $sql = "SELECT * 
                FROM mdl_course c
                JOIN mdl_course_modules cm ON cm.course = c.id
                JOIN mdl_preference p ON p.cmid = cm.id
                WHERE c.id = :cid AND cm.id = :cmid ";

        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid));

        if(empty($datas)){
            // insert
            $insert_sql = "INSERT INTO mdl_preference (`like`, hate, chance) VALUES ('0', '0', '0')";
            $DB->insert_record($datas, $insert_sql);
        } else {
            // update
            $object_hate = new stdClass();
            $object_hate->like = $datas->like;
            $object_hate->hate = $datas->hate+1;
            $object_hate->chance = 1;

            $DB->update_record($datas, $object_hate);            
        }
    }

    function getChance($cid, $cmid){
        global $DB;

        $sql = "SELECT * 
                FROM mdl_course c
                JOIN mdl_course_modules cm ON cm.course = c.id
                JOIN mdl_preference p ON p.cmid = cm.id
                WHERE c.id = :cid AND cm.id = :cmid ";

        $datas = $DB->get_record_sql($sql, array('cid'=>$cid, 'cmid'=>$cmid));

        if(empty($datas->chance)){
            $chancedata = 0;
        } else {
            $chancedata = $datas->chance;
        }

        return $chancedata;
    }
    