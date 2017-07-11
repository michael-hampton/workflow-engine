<?php

require_once 'D.php';

use Phalcon\Di;

class Mysql2 extends D
{

    public $db;
    private $adapter = "task_manager";

    public function __construct ()
    {
        require_once 'registry.php';
        $objRegistry = new Registry ($this->adapter);
        $this->_di = $objRegistry->getNewConnection ();

        $this->db = $this->_di['db'];

        //echo '<pre>';
        //print_r($this->db);
        //die;
    }

    public function setAdapter ($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Sets the connection externally
     */
    public function setConnection ($connection)
    {
        $this->db = $connection;
    }

    public function cleanInput ($data)
    {
        // http://svn.bitflux.ch/repos/public/popoon/trunk/classes/externalinput.php
        // +----------------------------------------------------------------------+
        // | Copyright (c) 2001-2006 Bitflux GmbH                                 |
        // +----------------------------------------------------------------------+
        // | Licensed under the Apache License, Version 2.0 (the "License");      |
        // | you may not use this file except in compliance with the License.     |
        // | You may obtain a copy of the License at                              |
        // | http://www.apache.org/licenses/LICENSE-2.0                           |
        // | Unless required by applicable law or agreed to in writing, software  |
        // | distributed under the License is distributed on an "AS IS" BASIS,    |
        // | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
        // | implied. See the License for the specific language governing         |
        // | permissions and limitations under the License.                       |
        // +----------------------------------------------------------------------+
        // | Author: Christian Stocker <chregu@bitflux.ch>                        |
        // +----------------------------------------------------------------------+
        //
		// Kohana Modifications:
        // * Changed double quotes to single quotes, changed indenting and spacing
        // * Removed magic_quotes stuff
        // * Increased regex readability:
        //   * Used delimeters that aren't found in the pattern
        //   * Removed all unneeded escapes
        //   * Deleted U modifiers and swapped greediness where needed
        // * Increased regex speed:
        //   * Made capturing parentheses non-capturing where possible
        //   * Removed parentheses where possible
        //   * Split up alternation alternatives
        //   * Made some quantifiers possessive
        // Fix &entity\n;
        $data = str_replace (array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace ('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace ('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode ($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace ('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace ('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace ('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace ('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace ('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace ('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace ('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace ('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace ('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }
        while ($old_data !== $data);

        return $data;
    }

    public function parms ($string, $data)
    {
        try {
            $indexed = $data == array_values ($data);
            foreach ($data as $k => $v) {
                if ( is_string ($v) )
                    $v = "'$v'";
                if ( $indexed )
                    $string = preg_replace ('/\?/', $v, $string, 1);
                else
                    $string = str_replace (":$k", $v, $string);
            }

            return $string;
        } catch (Exception $ex) {
            print_r($data);
            die($string);
        }
    }

    //functions
    public function checkVar ($var)
    {
        $var = str_replace ("\n", " ", $var);
        $var = str_replace (" ", "", $var);
        if ( isset ($var) && !empty ($var) && $var != '' )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function isAjax ()
    {
        if ( !empty ($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower ($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function lastInsertId ()
    {
        return $this->db->lastInsertId ();
    }

    public function _query ($sql, $arrParameters = array())
    {
        return $this->queryDatabase ($sql, $arrParameters, TRUE);
    }

    public function _select ($table, $fields_array = array(), $where_params_array = array(), $order_array = array(), $limit = NULL, $offset = NULL)
    {

        // if fields have been specified then set them to be included in the query,
        // if not set a wildcard to return everything
        if ( !empty ($fields_array) )
        {

            $fields = implode (', ', $fields_array);
        }
        else
        {

            $fields = '*';
        }

        // create the basic query
        $query = "
		SELECT {$fields}
		FROM {$table}
		";

        $bind_params_array = array();

        // if there are any WHERE parameters then add them to the query and the bind params array
        if ( !empty ($where_params_array) )
        {

            $query .= "WHERE
            ";
            $count = 0;
            foreach ($where_params_array as $key => $value) {

                $bind_params_array[$count] = $value;

                if ( $count > 0 )
                {
                    $query .= " AND ";
                }
                $query .= "`{$key}` = ?
                ";
                $count++;
            }
        }

        // if an order by setting has been received then add it to the query
        if ( !empty ($order_array) )
        {

            $query.= "ORDER BY
            ";
            foreach ($order_array as $key => $value) {

                $query.= "{$key} {$value},
            ";
            }
            $query = rtrim (trim ($query), ',');
        }

        // if an LIMIT setting has been received then add it to the query
        if ( is_numeric ($limit) )
        {
            $query.= "
            LIMIT {$limit}
            ";
        }

        // if an OFFSET setting has been received then add it to the query
        if ( $limit !== null && $offset != NULL )
        {
            if ( !is_int ($offset) )
            {

                D::P (['ERROR' => 'Non integer passed to function as OFFSET value'], true);
                return FALSE;
            }

            $query.= "
            OFFSET {$offset}
            ";
        }

        return $this->queryDatabase ($query, $bind_params_array, TRUE);
    }

    public function queryDatabase ($query, $arrParameters, $isSelect)
    {
        //die($this->parms($query, $arrParameters));
        if ( $isSelect === TRUE )
        {
            try {
                $result = $this->db->query ($query, $arrParameters);

                $this->logInfo (json_encode ($arrParameters));
                $this->logInfo ($this->parms ($query, $arrParameters));

                $arrResultSet = $result->fetchAll ();

                return $arrResultSet;
            } catch (Exception $e) {
                //echo $this->parms($query, $arrParameters);
                //die($e->getMessage());
                $this->setLog ($e);
            }
        }
        else
        {
            try {
                $this->db->execute ($query, $arrParameters);
                $this->logInfo ($this->parms ($query, $arrParameters));
                return $this->db->lastInsertId ();
            } catch (Exception $e) {
                //echo $this->parms($query, $arrParameters);
                //die($e->getMessage());
                $this->setLog ($e, $this->parms ($query, $arrParameters));
            }
        }
    }

    public function _update ($table, $data, $where)
    {
        if ( empty ($table) || empty ($data) )
        {
            return false;
        }

        list( $fields, $placeholders, $values ) = $this->prep_query ($data, 'update');

        //Format where clause
        $where_clause = '';
        $where_values = '';
        $count = 0;

        foreach ($where as $field => $value) {
            if ( $count > 0 )
            {
                $where_clause .= ' AND ';
            }

            $where_clause .= $field . '=?';
            $where_values[] = $value;

            $count++;
        }

        $values = array_merge ($values, $where_values);

        $query = "UPDATE {$table} SET {$placeholders} WHERE {$where_clause}";

        $this->queryDatabase ($query, $values, FALSE);
    }

    public function _insert ($table, $data, $format = '')
    {
        // Check for $table or $data not set

        if ( empty ($table) || empty ($data) )
        {
            return false;
        }

        list( $fields, $placeholders, $values ) = $this->prep_query ($data);

        $query = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";

        $id = $this->queryDatabase ($query, $values, false);
        return $id;
    }

    private function prep_query ($data, $type = 'insert')
    {
        // Instantiate $fields and $placeholders for looping
        $fields = '';
        $placeholders = '';
        $values = array();

        // Loop through $data and build $fields, $placeholders, and $values
        foreach ($data as $field => $value) {
            $fields .= "{$field},";
            $values[] = $value;

            if ( $type == 'update' )
            {
                $placeholders .= "`" . $field . "`" . '=?,';
            }
            else
            {
                $placeholders .= '?,';
            }
        }

        // Normalize $fields and $placeholders for inserting
        $fields = substr ($fields, 0, -1);
        $placeholders = substr ($placeholders, 0, -1);

        return array($fields, $placeholders, $values);
    }

    public function _delete ($table, $where_params_array = array())
    {


        // create the basic update query
        $query = "
        DELETE FROM {$table}
        ";

        $bind_params_array = array();

        // if there are any WHERE parameters then add them to the query and the bind params array
        if ( !empty ($where_params_array) )
        {

            $count = 0;
            $query .= "WHERE
            ";
            foreach ($where_params_array as $key => $value) {

                $bind_params_array[$count] = $value;

                if ( $count > 0 )
                {
                    $query .= " AND ";
                }
                $query .= "`{$key}` = ?
                ";
                $count++;
            }
        }
        else
        {

            // DELETE all from table has been disabled as a safeguard in case anyone
            // accidentally forgets to pass the second parameter
            return FALSE;
        }

        return $this->queryDatabase ($query, $bind_params_array, false);
    }

}
