<?php
class configurationClass
{
 /**
     * saveConfig
     *
     * @param object &$object
     * @param array &$from
     * @return void
     */
    public function saveConfig($cfg, $obj, $pro = '', $usr = '', $app = '')
    {
        $aFields = array('CFG_UID' => $cfg, 'OBJ_UID' => $obj, 'PRO_UID' => $pro, 'USR_UID' => $usr, 'APP_UID' => $app, 'CFG_VALUE' => serialize($this->aConfig)
        );
        if ($this->Configuration->exists($cfg, $obj, $pro, $usr, $app)) {
            $this->Configuration->update($aFields);
        } else {
            $this->Configuration->create($aFields);
            $this->Configuration->update($aFields);
        }
    }
}
