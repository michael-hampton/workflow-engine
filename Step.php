<?php
class Step extends BaseStep
{
 /*
    * update the step information using an array with all values
    * @param array $fields
    * @return variant
    */
    public function update ($fields)
    {
        try {
            $this->load( $fields['STEP_UID'] );
            $this->loadObject( $fields );
            if ($this->validate()) {
                $result = $this->save();
                return $result;
            } else {
                throw (new Exception( "Failed Validation in class " . get_class( $this ) . "." ));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

}
