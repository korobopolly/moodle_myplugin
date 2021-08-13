<?php
    // require_once('../config.php');
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

    
    function getChart(){
        // 파라메타로 모듈을 던져서 해당 모듈에 맞는 테이블로 쿼리는 만들게..
        global $DB;//moodle 내부의 DB(폴더)에서 함수를 불러옴 import랑 비슷함

        // $sql = "SELECT m.name
        //     FROM {course} c
        //     JOIN {course_modules} cm ON cm.course = c.id
        //     JOIN {modules} m ON m.id = cm.module
        //     WHERE cm.id = :cmid";

        // $modulename = $DB->get_record_sql($sql, array('cmid'=>$cmid));
    
        $sql = "SELECT p.id id, p.cid course_id, c.shortname course_name, p.cmid coursemodule_id, m.name module_name, f.name forum_name,
                f.intro forum_intro, SUM(liked) liked, SUM(hate) hated
                FROM mdl_preference p
                JOIN mdl_course c ON c.id=p.cid
                JOIN mdl_course_modules cm ON cm.id=p.cmid
                JOIN mdl_modules m ON m.id=cm.module
                JOIN mdl_forum f ON f.id=cm.instance
                GROUP BY cm.id
                ORDER BY cm.id";
                //forum의 코드 재사용성 생각

        // JOIN mdl_{$modulename->name} mn ON mn.id=cm.instance

        if ($datas = $DB->get_records_sql($sql)) {
    
            $chart = new core\chart_bar();
            $chart->set_title('강좌 활동별 호불호');
            $series = array();
            $series2 = array();
            $labels = array();
    
            foreach($datas as $v){
                array_push($series, $v->liked);
                array_push($series2, $v->hated);
                array_push($labels, $v->forum_name);
            }
    
            $sales = new core\chart_series('좋아요', $series);
            $sales2 = new core\chart_series('싫어요', $series2);
            $chart->add_series($sales);
            $chart->add_series($sales2);
            $chart->set_labels($labels);
        } else {
            $chart = '데이터가 존재하지 않습니다.';
        }
    
        return $chart;
    }

    function setButton($uid, $cid, $cmid){
        $chance_check = getChance($uid, $cid, $cmid);    // 현재 접속자의 좋아요 참여 여부
        $total_check = getTotal($cid, $cmid);
        $html = '';
        if($chance_check != 1){   // 유저의 gooder 필드를 체크해서 출력여부판단
            //좋아요 싫어요 버튼
            $html .= html_writer::start_tag('form', array('method' => 'post', 'action'=>'/local/ubdocument/action.php'));
    
                $paramuserid = array('type' => 'hidden', 'name' => 'paramuserid', 'id' => 'paramuserid', 'value' => $uid, 'class' => '');
                $html .= html_writer::empty_tag('input', $paramuserid);
                $paramcid = array('type' => 'hidden', 'name' => 'paramcid', 'id' => 'paramcid', 'value' => $cid, 'class' => '');
                $html .= html_writer::empty_tag('input', $paramcid);
                $paramcmid = array('type' => 'hidden', 'name' => 'paramcmid', 'id' => 'paramcmid', 'value' => $cmid, 'class' => '');
                $html .= html_writer::empty_tag('input', $paramcmid);
    
                $like = array('type' => 'submit', 'name' => 'like', 'id' => 'like', 'value' => '좋아요', 'class' => 'btn btn-primary');
                $html .= html_writer::empty_tag('input', $like);
                $html .= "&nbsp&nbsp";
                $hate = array('type' => 'submit', 'name' => 'hate', 'id' => 'hate', 'value' => '싫어요', 'class' => 'btn btn-info btn-margin');
                $html .= html_writer::empty_tag('input', $hate);
    
            $html .= html_writer::end_tag('form');
    
            $html .= html_writer::tag('p','이 활동을 <b>'.$total_check->total_like.'</b> 명이 좋아합니다.');
        } else {
            //disabled
            $html .= html_writer::start_tag('form', array('method' => 'post', 'action'=>'/local/ubdocument/action.php'));
    
                $like = array('type' => 'submit', 'name' => 'like', 'id' => 'like', 'value' => '좋아요', 'class' => 'btn', 'style'=>'background-color:#696969; color:white;', 'disabled'=>'disabled');
                $html .= html_writer::empty_tag('input', $like);
                $html .= "&nbsp&nbsp";
                $hate = array('type' => 'submit', 'name' => 'hate', 'id' => 'hate', 'value' => '싫어요', 'class' => 'btn btn-margin', 'style'=>'background-color:#696969; color:white;', 'disabled'=>'disabled');
                $html .= html_writer::empty_tag('input', $hate);
    
            $html .= html_writer::end_tag('form');
    
            $html .= html_writer::tag('p','해당 평가는 한번만 가능합니다.');
            $html .= html_writer::tag('p','이 활동을 <b>'.$total_check->total_like.'</b> 명이 좋아합니다.');
        };
        return $html;
    }
    