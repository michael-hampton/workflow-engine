<?php

class Password
{

    /**
     * Internal method: validate user input 
     * @author Marcelo Cuiza
     * @access protected
     * @param String $value (required)
     * @param Array or String $types ( string | int | float | boolean | path | nosql )
     * @param String $valType ( validate | sanitize )
     * @return String $value
     */
    public function validateInput ($value, $types = 'string', $valType = 'sanitize')
    {
        if ( !isset ($value) || empty ($value) )
        {
            return '';
        }

        if ( is_array ($types) && sizeof ($types) )
        {
            foreach ($types as $type) {
                if ( $valType == 'sanitize' )
                {
                    $value = $this->sanitizeInputValue ($value, $type);
                }
                else
                {
                    $value = $this->validateInputValue ($value, $type);
                }
            }
        }
        elseif ( is_string ($types) )
        {
            if ( $types == 'sanitize' || $types == 'validate' )
            {
                $valType = $types;
                $types = 'string';
            }
            if ( $valType == 'sanitize' )
            {
                $value = $this->sanitizeInputValue ($value, $types);
            }
            else
            {
                $value = $this->validateInputValue ($value, $types);
            }
        }

        return $value;
    }

    /**
     * @param $value
     * @param $type
     * @return bool|int|mixed|string
     */
    public function sanitizeInputValue ($value, $type)
    {

        switch ($type) {
            case 'float':
                $value = filter_var ($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                break;
            case 'int':
                $value = (int) filter_var ($value, FILTER_SANITIZE_NUMBER_INT);
                break;
            case 'boolean':
                $value = (boolean) filter_var ($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                break;
            case 'path':
                if ( !file_exists ($value) )
                {
                    if ( !is_dir ($value) )
                    {
                        $value = '';
                    }
                }
                break;
            case 'nosql':
                $value = (string) filter_var ($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
                if ( preg_match ('/\b(or|and|xor|drop|insert|update|delete|select)\b/i', $value, $matches, PREG_OFFSET_CAPTURE) )
                {
                    $value = substr ($value, 0, $matches[0][1]);
                }
                break;
            default:
                $value = (string) filter_var ($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        }

        return $value;
    }

    /**
     * @param $value
     * @param $type
     * @throws Exception
     */
    public function validateInputValue ($value, $type)
    {

        switch ($type) {
            case 'float':
                $value = str_replace (',', '.', $value);
                if ( !filter_var ($value, FILTER_VALIDATE_FLOAT) )
                {
                    throw new \Exception ('not a float value');
                }
                break;
            case 'int':
                if ( !filter_var ($value, FILTER_VALIDATE_INT) )
                {
                    throw new \Exception ('not a int value');
                }
                break;
            case 'boolean':
                if ( !preg_match ('/\b(yes|no|false|true|1|0)\b/i', $value) )
                {
                    throw new \Exception ('not a boolean value');
                }
                break;
            case 'path':
                if ( !file_exists ($value) )
                {
                    if ( !is_dir ($value) )
                    {
                        throw new \Exception ('not a valid path');
                    }
                }
                break;
            case 'nosql':
                if ( preg_match ('/\b(or|and|xor|drop|insert|update|delete|select)\b/i', $value) )
                {
                    throw new \Exception ('sql command found');
                }
                break;
            default:
                if ( !is_string ($value) )
                {
                    throw new \Exception ('not a string value');
                }
        }
    }

    public function getPasswordHashType ()
    {
        $passwordHashConfig = $this->getPasswordHashConfig ();
        return $passwordHashConfig['current'];
    }

    public function hashPassword ($pass, $hashType = '', $includeHashType = false)
    {
        if ( $hashType == '' )
        {
            $hashType = $this->getPasswordHashType ();
        }

        $hashType = $this->validateInput ($hashType);
        $pass = $this->validateInput ($pass);

        eval ("\$var = hash('" . $hashType . "', '" . $pass . "');");

        if ( $includeHashType )
        {
            $var = $hashType . ':' . $var;
        }

        return $var;
    }

    public function getPasswordHashConfig ()
    {

        $passwordHashConfig = array('current' => 'md5', 'previous' => 'md5');

        return $passwordHashConfig;
    }

    public function verifyHashPassword ($pass, $userPass)
    {
        $passwordHashConfig = $this->getPasswordHashConfig ();
        $hashTypeCurrent = $passwordHashConfig['current'];
        $hashTypePrevious = $passwordHashConfig['previous'];
        if ( ($this->hashPassword ($pass, $hashTypeCurrent) == $userPass) || ($pass === $hashTypeCurrent . ':' . $userPass) )
        {
            return true;
        }
        if ( ($this->hashPassword ($pass, $hashTypePrevious) == $userPass) || ($pass === $hashTypePrevious . ':' . $userPass) )
        {
            return true;
        }
        
        return false;
    }

}
