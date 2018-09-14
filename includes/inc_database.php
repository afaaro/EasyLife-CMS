<?php
//Deny direct access
defined("_LOAD") or die("Access denied");


/**
 * functions related to database
 * 
 */
function db_select($query) {
    global $db;

    return $db->_select($query);
}

function db_update($tbl, $fields, $cond) {
    global $db;

    return $db->update($tbl, $fields, $cond);
}

function db_insert($tbl, $fields) {
    global $db;

    return $db->insert($tbl, $fields);
}

function db_delete($tbl, $cond) {
    global $db;

    return $db->delete($tbl, $cond);
}

function db_count($tbl, $fields = '*', $cond = false) {
    global $db;
    return $db->count($tbl,$fields,$cond);
}