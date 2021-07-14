<?php

/**
 * 데이터 스키마에서 기본값 적용
 * 
 * @global type $DB
 * @global type $CFG
 * @return boolean
 * @throws Exception
 */
function local_ubdocument_updateTableSchema($lang=null) {
    global $DB, $CFG;
 
    if (empty($lang)) $lang = current_language ();
    
    $sql = ""
    . "SELECT "
        . "CONCAT(t1.table_name, '_field_', column_name) field_key, ordinal_position column_seq, t1.table_name, t1.table_comment, column_name, data_type, column_type, column_key, is_nullable, column_default, extra, column_comment "
    . "FROM "
        . "(SELECT table_name, table_comment FROM information_schema.TABLES WHERE table_schema='".$CFG->dbname."') t1, "
        . "(SELECT table_name, column_name, data_type, column_type, column_key, is_nullable, column_default, extra, column_comment, ordinal_position FROM information_schema.COLUMNS WHERE table_schema='".$CFG->dbname."') t2 "
    . "WHERE t1.table_name = t2.table_name "
    . "ORDER BY t1.table_name, ordinal_position";
    
    if ($datas = $DB->get_records_sql($sql)) {
                
        // 초기화
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
    $sql = 'SELECT id as id, sortorder, shortname, FROM_UNIXTIME(timecreated) as timecreated FROM mdl_course WHERE id != 1';
    
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

    $sql = 'SELECT * FROM mdl_user WHERE id != 1';
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

    $sql = 'SELECT * FROM mdl_local_ubdocument_tables WHERE id <447';
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

    $sql = 'SELECT * FROM mdl_local_ubdocument_table_columns ORDER BY tid, column_seq'; //tid와 column_seq 순으로 정렬
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
                        <th>NO.</th>
                        <th>물리명</th>
                        <th>논리명</th>
                        <th>자료형</th>
                    </tr>
                </thead>
                <tbody>";
                $old_tid = $v->tid; //old_tid에 tid의 값을 넣는다.
            }

            $html.="<tr>
                <td>{$v->column_seq}</td>
                <td>{$v->physical_name}</td>
                <td>{$v->logical_name}</td>
                <td>{$v->column_type}</td>
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