<?php

use block_mockblock\search\area;

/**
 * 데이터 스키마에서 기본값 적용
 * 
 * @global type $DB
 * @global type $CFG
 * @return boolean
 * @throws Exception
 */
function local_ubdocument_updateTableSchema($lang=null) {
    global $DB, $CFG; //moodle 내부의 DB(폴더)에서 함수를 불러옴 import랑 비슷함
 
    if (empty($lang)) $lang = current_language (); //언어 선택
    
    $sql = ""
    . "SELECT "
        . "CONCAT(t1.table_name, '_field_', column_name) field_key, ordinal_position column_seq, t1.table_name, t1.table_comment, column_name, data_type, column_type, column_key, is_nullable, column_default, extra, column_comment "
        //t1의 테이블 명, 필드, 컬럼 명 결합 및 필요한 컬럼들을 선택
    . "FROM "
        . "(SELECT table_name, table_comment FROM information_schema.TABLES WHERE table_schema='".$CFG->dbname."') t1, " 
        //테이블 명, 테이블 코멘트를 information_schema.TABLES에서 불러와 t1으로 묶음
        . "(SELECT table_name, column_name, data_type, column_type, column_key, is_nullable, column_default, extra, column_comment, ordinal_position FROM information_schema.COLUMNS WHERE table_schema='".$CFG->dbname."') t2 "
        //테이블 명, 컬럼 명, 데이터 타입, 컬럼 타입, 컬럼 키, NULL 여부, 컬럼 디폴트 값, 기타, 컬럼 코멘트, 색인을 information_schema.COLUMNS에서 불러와 t2로 묶음
    . "WHERE t1.table_name = t2.table_name "
    //조건 : t1과 t2의 테이블 명이 같은 것
    . "ORDER BY t1.table_name, ordinal_position";
    //t1의 테이블 명과 색인을 기준으로 정렬
    
    if ($datas = $DB->get_records_sql($sql)) {
        //sql에서 가져온 데이터를 datas에 저장했다면

        $DB->execute("UPDATE {local_ubdocument_tables} t JOIN {local_ubdocument_table_columns} c ON c.tid = t.id SET t.deleted=1, c.deleted=1 WHERE t.lang=:lang", array('lang'=>$lang));
        

        foreach($datas as $v) {
            $table = new stdClass();
            if (!$table = $DB->get_record('local_ubdocument_tables', array('lang'=>$lang, 'physical_name'=>$v->table_name))) {
                $table = new stdClass();
                $table->id = 0;
                $table->lang = $lang;
                $table->physical_name = $v->table_name;
                $table->logical_name = '';
                $table->comment = $v->table_comment;
                $table->deleted = 0;
                if (!$table->id = $DB->insert_record('local_ubdocument_tables', $table)) {
                    throw new Exception('테이블 정보 입력 중 오류발생!!!');
                }
            } else {
                $table->deleted = 0;
                $DB->update_record('local_ubdocument_tables', $table);
            }

            if (!$column = $DB->get_record('local_ubdocument_table_columns', array('tid'=>$table->id, 'physical_name'=>$v->column_name))) {
                $column = new stdClass();
                $column->tid = $table->id;
                $column->column_seq = $v->column_seq;
                $column->physical_name = $v->column_name;
                $column->logical_name = '';
                $column->column_type = $v->column_type;
                $column->column_length = $v->column_type;
                $column->column_nullable = $v->is_nullable;
                $column->column_key = $v->column_key;
                $column->column_default = !empty($v->extra) ? $v->extra : $v->column_default;
                $column->column_comment = $v->column_comment;
                $column->deleted = 0;
                if (!$column->id = $DB->insert_record('local_ubdocument_table_columns', $column)) {
                    throw new Exception('테이블 필드 정보 입력 중 오류발생!!!');
                }
            } else {
                $column->column_seq = $v->column_seq;
                $column->deleted = 0;
                //chk($column,'$column',true);
                $DB->update_record('local_ubdocument_table_columns', $column);
            }
        }
    }
    return true;
}

/**
 * 테이블 스키마 정보 추출
 * 
 * @global type $DB
 * @global type $CFG
 * @param type $tablename
 * @param type $reset
 * @return \stdClass
 */
function local_ubdocument_getTableDefinition($lang = null, $reset = false) {
	global $DB, $CFG;

        if (empty($lang)) $lang = current_language ();
        
	$cache = cache::make('local_ubdocument', 'table_definition');
	if($reset) $cache->delete($lang);
        
	$table_definition = $cache->get($lang);
        // chk($table_definition,'$table_definition',true);
	if ($table_definition === false) {
            
            local_ubdocument_updateTableSchema($lang);
            
            $table_definition = array();

            $sql = "
                SELECT 
                    CONCAT(t.id, '_', c.id) uniq,
                    c.tid table_id, c.id column_id, t.physical_name table_physical_name, t.logical_name table_logical_name, t.comment table_comment,
                    c.column_seq, c.physical_name column_physical_name, c.logical_name column_logical_name, c.column_type, c.column_length, c.column_nullable, c.column_key, c.column_default, c.column_comment
                FROM {local_ubdocument_tables} t
                JOIN {local_ubdocument_table_columns} c ON c.tid = t.id
                WHERE t.deleted = 0 AND c.deleted = 0 AND t.lang = :lang
                ORDER BY t.physical_name, c.column_seq";
            if ($datas = $DB->get_records_sql($sql, array('lang'=>$lang))) {
                $prv_table = '';
                $table_num = 0;
                $column_num = 0;
                foreach($datas as $v) {
                    if ($prv_table != $v->table_physical_name) {
                        if ($prv_table != '') $table_num++;
                        $column_num = 0;
                        $prv_table = $v->table_physical_name;
                        $table_definition[$table_num] = new stdClass();
                        $table_definition[$table_num]->table_id = $v->table_id;
                        $table_definition[$table_num]->physical_name = $v->table_physical_name;
                        $table_definition[$table_num]->logical_name = $v->table_logical_name;
                        $table_definition[$table_num]->comment = $v->table_comment;
                        $table_definition[$table_num]->columns = array();
                    }

                    $column = new stdClass();
                    $column->column_id = $v->column_id;
                    $column->column_seq = $v->column_seq;
                    $column->physical_name = $v->column_physical_name;
                    $column->logical_name = $v->column_logical_name;
                    $column->column_type = $v->column_type;
                    $column->column_length = $v->column_length;
                    $column->column_key = $v->column_key;
                    $column->column_nullable = $v->column_nullable;
                    $column->column_default = $v->column_default;
                    $column->column_comment = $v->column_comment;
                    $table_definition[$table_num]->columns[$column_num++] = $column;
                }
            }
            $cache->set($lang, $table_definition);
        }
	return $table_definition;
}

/**
 * 무들내의 모든 강좌를 가져오는 함수
 * 
 * @global type $DB
 * @return \stdClass
 */
function get_allCourses(){
    global $DB;//moodle 내부의 DB(폴더)에서 함수를 불러옴 import랑 비슷함

    //  $sql = 'SELECT * FROM mdl_course WHERE id != 1';
    $sql = 'SELECT id as id, sortorder, shortname, FROM_UNIXTIME(timecreated) as timecreated FROM mdl_course WHERE id != 1 ORDER BY sortorder';
    
    // $sql = "SELECT * FROM {course} WHERE id != 1"; //여러 곳에서 사용할 수 있는 sql문, ""쓰는 이유: 프리픽스(mdl_)를 자동 적용
    $datas = $DB->get_records_sql($sql);    // object type으로 return

    return $datas;//반환값
}

/**
 * 무들내의 모든 사용자를 가져오는 함수
 * 
 * @global type $DB
 * @return \stdClass
 */
function get_allUsers(){
    global $DB;//moodle 내부의 DB(폴더)에서 함수를 불러옴 import랑 비슷함

    $sql = 'SELECT * FROM mdl_user WHERE id != 1 ORDER BY id';
    // $sql = "SELECT * FROM {course} WHERE id != 1"; //여러 곳에서 사용할 수 있는 sql문, ""쓰는 이유: 프리픽스(mdl_)를 자동 적용
    $datas = $DB->get_records_sql($sql);    // object type으로 return

    return $datas;//반환값
}

/**
 * 무들내의 모든 사용자를 가져오는 함수
 * 
 * @global type $DB
 * @return \stdClass
 */
function get_allTables(){
    global $DB;//moodle 내부의 DB(폴더)에서 함수를 불러옴 import랑 비슷함

    $sql = 'SELECT * FROM mdl_local_ubdocument_tables WHERE id <447 ORDER BY id';
    // $sql = "SELECT * FROM {course} WHERE id != 1"; //여러 곳에서 사용할 수 있는 sql문, ""쓰는 이유: 프리픽스(mdl_)를 자동 적용
    $datas = $DB->get_records_sql($sql);    // object type으로 return

    return $datas;//반환값
}


/**
 * 무들내의 모든 사용자를 가져오는 함수
 * 
 * @global type $DB
 * @return \stdClass
 */
function get_allColumns(){
    global $DB;//moodle 내부의 DB(폴더)에서 함수를 불러옴 import랑 비슷함
  
    $html = ''; //초기화

    $sql = 'SELECT c.id, c.tid, t.physical_name, t.logical_name, t.comment, c.column_seq, c.column_type, c.physical_name as c_pname, c.logical_name as c_lname, column_comment , c.column_nullable as c_null, c.column_key as c_key
    FROM mdl_local_ubdocument_table_columns AS c 
    JOIN mdl_local_ubdocument_tables AS t 
    ON t.id=c.tid WHERE tid<447 ORDER BY c.id';

    if ($datas = $DB->get_records_sql($sql)) { 

        $old_tid = 0; //변수 생성
        foreach($datas as $v){
            if ($v->tid != $old_tid) { //tid가 old_tid와 같지 않으면
                if ($old_tid > 0) $html.="</tbody></table><br>"; //old_tid가 0이 아니면 tbody와 table을 닫고 여백
                $html.="<table class='table table-border'>"; 
                //.= 내부의 모든 것을 출력 여백까지도
                $html.= " 
                <thead>
                    <tr>
                        <td rowspan='2' colspan='2' style='font-weight: bold'>NO.{$v->tid}</td>
                        <th>테이블명</th>
                        <td colspan='2'>{$v->physical_name}</td>
                        <th>논리명</th>
                        <td>{$v->logical_name}</td>
                    </tr>
                    <tr>
                        <th>코멘트</th>
                        <td colspan='2'>{$v->comment}</td>
                        <th>작성자</th>
                        <td>길민기</td>
                    </tr>
                    <tr>
                        <th>SEQ</th>
                        <th>물리명</th>
                        <th>논리명</th>
                        <th>자료형</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>코멘트</th>
                    </tr>
                </thead>
                <tbody>";
                $old_tid = $v->tid; //old_tid에 tid의 값을 넣는다.
            }
            $html.="<tr>
                <td align='center'>{$v->column_seq}</td>
                <td>{$v->c_pname}</td>
                <td>{$v->c_lname}</td>
                <td align='center'>{$v->column_type}</td>
                <td align='center'>{$v->c_null}</td>
                <td align='center'>{$v->c_key}</td>
                <td align='center'>{$v->column_comment}</td>
            </tr>";
        }
        if ($old_tid > 0) $html.="</tbody></table><br>";
    } else { //에러 처리
        $html = "<tr><td colspan=2>데이터가 없습니다.</td></tr>";
    }

    

    //반복문
    // for($i=1; $i<$datas[count($datas)-1]->tid; $i=$i+1) {
    //     // $i=1, tid=1
    //     $datas[]

    //     // '<table class="table table-border">';
    //     //     '<tr>';
    //     //         '<td>테이블 번호</td>';
    //     //         '<td>물리명</td>';
    //     //         '<td>논리명</td>';
    //     //         '<td>자료형</td>';
    //     //     '</tr>';
    //     //     foreach($columns as $v){
    //     //         $sql = 'SELECT * FROM mdl_local_ubdocument_table_columns WHERE column_num = tid ORDER BY id ASC';
    //     //         '<tr>';
    //     //             "<td>{$v->tid}</td>";
    //     //             "<td>{$v->physical_name}</td>";
    //     //             "<td>{$v->logical_name}</td>";
    //     //             "<td>{$v->column_type}</td>";
    //     //         '</tr>';
    //     //         $column_num++;
    //     //     }
    //     // '</table>';
    // }

    return $html; //반환값
}


/**
 * 무들내의 모든 사용자를 가져오는 함수
 * 
 * @global type $DB
 * @return \stdClass
 */
function get_tabledefinition_en(){
    global $DB;//moodle 내부의 DB(폴더)에서 함수를 불러옴 import랑 비슷함
  
    $html = ''; //초기화

    $sql = 'SELECT c.id, c.tid-446 AS eng, t.physical_name, t.logical_name, t.`comment`, c.column_seq, c.column_type, c.physical_name as c_pname, c.logical_name as c_lname, column_comment, c.column_nullable as c_null, c.column_key as c_key
    FROM mdl_local_ubdocument_table_columns AS c 
    JOIN mdl_local_ubdocument_tables AS t 
    ON t.id=c.tid WHERE c.tid-446>0 ORDER BY c.id ';

    if ($datas = $DB->get_records_sql($sql)) { 

        $old_tid = 0; //변수 생성
        foreach($datas as $v){
            if ($v->eng != $old_tid) { //eng가 old_tid와 같지 않으면
                if ($old_tid > 0) $html.="</tbody></table><br>"; //old_tid가 0이 아니면 tbody와 table을 닫고 여백
                $html.="<table class='table table-border'>"; 
                //.= 내부의 모든 것을 출력 여백까지도
                $html.= " 
                <thead>
                    <tr>
                        <td rowspan='2' colspan='2' style='font-weight: bold'>NO.{$v->eng}</td>
                        <th>Table name</th>
                        <td colspan='2'>{$v->physical_name}</td>
                        <th>Logical name</th>
                        <td>{$v->logical_name}</td>
                    </tr>
                    <tr>
                        <th>Comment</th>
                        <td colspan='2'>{$v->comment}</td>
                        <th>Writer</th>
                        <td>Kilminki</td>
                    </tr>
                    <tr>
                        <th>SEQ</th>
                        <th>Physical name</th>
                        <th>Logical name</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>";
                $old_tid = $v->eng; //old_tid에 tid의 값을 넣는다.
            }
            $html.="<tr>
                <td align='center'>{$v->column_seq}</td>
                <td>{$v->c_pname}</td>
                <td>{$v->c_lname}</td>
                <td align='center'>{$v->column_type}</td>
                <td align='center'>{$v->c_null}</td>
                <td align='center'>{$v->c_key}</td>
                <td align='center'>{$v->column_comment}</td>
            </tr>";
        }
        if ($old_tid > 0) $html.="</tbody></table><br>";
    } else { //에러 처리
        $html = "<tr><td colspan=2>데이터가 없습니다.</td></tr>";
    }
    return $html; //반환값
}

//방문자 카운터
function showCounter(){
    global $DB;

    $data = $DB->get_field('visit', 'visitor', array());
    //DB get_field : visit 테이블의 visitor 컬럼의 필드 데이터를 가져옴
    return $data;
}

function visitCounter(){
    global $DB;

    $data = $DB->get_record('visit', array());
    //DB get_record : visit 테이블에서 데이터를 가져옴

    $object_good = new stdClass();
    //비어있는 클래스 생성
    $object_good->id = $data->id;
    //멤버 변수에 접근
    $object_good->visitor = $data->visitor + 1;
    //멤버 변수에 접근

    $DB->update_record('visit', $object_good);
    //DB update_record : visit 테이블에 변수 값을 업데이트
}

//좋아요와 싫어요
function showFun(){
    global $DB;

    $data = $DB->get_field('good', 'point', array());
    //DB get_field : good 테이블의 point 컬럼의 필드 데이터를 가져옴
    return $data;
}

function showHate(){
    global $DB;

    $data = $DB->get_field('good', 'loss', array());
    //DB get_field : good 테이블의 loss 컬럼의 필드 데이터를 가져옴
    return $data;
}

function likeFun(){ 
    global $DB, $USER;

    $data = $DB->get_record('good', array());       //DB get_record : good 테이블에서 데이터를 가져옴

    $object_good = new stdClass();              // 비어있는 클래스 생성
    $object_good->id = $data->id;               // 업데이트 시 매핑을 위한 id 값을 할당
    $object_good->point = $data->point + 1;     // 불러온 토탈 카운트 수 + 1

    $user_data = $DB->get_record('user', array('id'=>$USER->id));
    $user_data->gooder = 1;

    $DB->update_record('good', $object_good);       //DB update_record : good 테이블에 변수 값을 업데이트
    $DB->update_record('user', $user_data);  //DB update_record : usre 테이블에 gooder 필드를 업데이트 (참여x -> 참여o)
}

function hateFun(){ 
    global $DB, $USER;

    $data = $DB->get_record('good', array());       //DB get_record : good 테이블에서 데이터를 가져옴

    $object_good = new stdClass();              // 비어있는 클래스 생성
    $object_good->id = $data->id;               // 업데이트 시 매핑을 위한 id 값을 할당
    $object_good->loss = $data->loss + 1;     // 불러온 토탈 카운트 수 + 1

    $user_data = $DB->get_record('user', array('id'=>$USER->id));
    $user_data->gooder = 1;

    $DB->update_record('good', $object_good);       //DB update_record : good 테이블에 변수 값을 업데이트
    //print_r($object_good_user); die;
    $DB->update_record('user', $user_data);  //DB update_record : usre 테이블에 gooder 필드를 업데이트 (참여x -> 참여o)

    // echo "<script>alert(\"이 활동을 좋아합니다.\");</script>";
}

// 현재 접속자의 gooder 필드를 가져옴
function getUserGooder(){
    global $DB, $USER;

    $data = $DB->get_field('user', 'gooder', array('id'=>$USER->id));
    return $data;
}
