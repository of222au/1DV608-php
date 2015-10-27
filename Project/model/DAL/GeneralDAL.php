<?php

namespace model;

/**
 * Class GeneralDAL
 * contains some general DAL functions (use class by extending it)
 * @package model
 */
class GeneralDAL {

    /**
     * Used to create mysqli statement IN with question marks for each item in array
     * @param $array
     * @return string
     */
    protected function getStatementINQuestionMarks($array) {
        $result = '';
        $first = true;
        foreach($array as $item) {
            $result .= ($first ? '' : ',') . '?';
            $first = false;
        }
        return "IN ( " . $result . " ) ";
    }

    /**
     * Used to get string of mysqli parameter types for each item in array
     * @param $array
     * @return string
     */
    protected function getBindParamINParamTypes($array, $type) {
        $result = '';
        foreach($array as $item) {
            $result .= $type;
        }
        return $result;
    }

    /**
     * Needs when using function call_user_func_array needs values to be referenced
     * @param $arr
     * @return array
     */
    protected function makeValuesReferenced($arr){
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;

    }
}