<?php
/**
 * Class InputDocument
 */
class InputDocument extends  BaseInputDocument
{
    public function create()
    {
        try {
            if($this->validate()) {
                $this->save();
            } else {
                $msg = '';
                foreach ($this->getValidationFailures() as $strMessage) {
                    $msg .= $strMessage . "<br/>";
                }
                throw (new Exception( 'The row cannot be created! ' . $msg));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function update()
    {
        try {
            if($this->validate()) {
                $this->save();
            } else {
                $msg = '';
                foreach ($this->getValidationFailures() as $strMessage) {
                    $msg .= $strMessage . "<br/>";
                }
                throw (new Exception( 'The row cannot be created! ' . $msg));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function delete()
    {
        $this->delete();
    }
}
