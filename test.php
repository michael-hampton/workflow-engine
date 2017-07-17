<?php
function dirList($directory, $sortOrder)
{

    //Get each file and add its details to two arrays
    $results = array();
    $handler = opendir($directory);
    while ($file = readdir($handler)) {
        if ($file != '.' && $file != '..' && $file != "robots.txt" && $file != ".htaccess" && !is_dir($directory . $file)) {
            $currentModified = filectime($directory . "/" . $file);
            $file_names[] = $file;
            $file_dates[] = $currentModified;
        }
    }
    closedir($handler);

    //Sort the date array by preferred order
    if ($sortOrder == "newestFirst") {
        arsort($file_dates);
    } else {
        asort($file_dates);
    }

    //Match file_names array to file_dates array
    $file_names_Array = array_keys($file_dates);
//    foreach ($file_names_Array as $idx => $name) {
//        $name = $file_names[$name];
//    }
    $file_dates = array_merge($file_dates);

    $i = 0;

    $arrFiles = array();

    //Loop through dates array and then echo the list
    foreach ($file_dates as $file_dates) {
        $date = $file_dates;
        $j = $file_names_Array[$i];
        $file = $file_names[$j];
        $i++;

        $arrFiles[$j]['filename'] = $file;
        $arrFiles[$j]['date_created'] = $date;
    }

    return $arrFiles;
}

$directory = $_SERVER['DOCUMENT_ROOT'] . "public/img/";
$arrFiles = dirList($directory, "newestFirst");

?>

<div class="m-t-md">
    <div class="pull-right">
        <button type="button" class="btn btn-w-m btn-danger DeleteFile">Delete</button>
        <button type="button" class="btn btn-w-m btn-primary Apply">Apply</button>
        <button type="button" class="btn btn-w-m btn-warning Restore">Restore Default</button>
        <i style="font-size: 32px;line-height: 36px;float: right; cursor: pointer;" class="fa fa-cloud-upload Upload m-l-xs"></i>
    </div>

    <strong>Found <?= count($arrFiles) ?> files.</strong>
</div>


<div class="wrapper wrapper-content">
    <div class="row">

        <div class="col-lg-9 animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                    <?php
                    foreach ($arrFiles as $arrFile) {

                        echo '<div class="file-box">
                                <div class="file" filename="' . base64_encode($arrFile['filename']) . '">
                                    <a href="#"> <span class="corner"></span>

                                        <div class="icon">
                                            <i class="fa fa-file"></i>
                                        </div>
                                        <div class="file-name">
                                            ' . $arrFile['filename'] . '<br>
                                            <small>Added: ' . date("F d Y H:i:s.", $arrFile['date_created']) . '</small>
                                        </div>
                                    </a>

                                <i style="font-size: 22px;" class="fa fa-check-square-o selectedLogos"></i>
                        </div>

                        </div>';
                    }
                    ?>


                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Upload File</h4>
                <small class="font-bold">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</small>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="post" id="fileinfo" name="fileinfo" onsubmit="return submitForm();">
                    <div class="form-group">
                        <label>Sample Input</label> <input id="fileopen" name="fileopen" type="file" placeholder="file" class="form-control" multiple="multiple">
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary SaveUpload">Save changes</button>
            </div>
        </div>
    </div>
</div>


<?php $this->partial("partials/footer"); ?>

<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script>
    $(document).ready(function () {
        $(".selectedLogos").on("click", function () {
            $(".selectedLogos").removeClass("fa-check-square").addClass("fa-check-square-o");
            $(this).removeClass("fa-check-square-o");
            $(this).addClass("fa-check-square");
        });

        $(".SaveUpload").on("click", function () {
            $("#fileinfo").submit();
        });

        $("#fileinfo").submit(function (evt) {
            evt.preventDefault();
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: '/index/test2',
                type: 'POST',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                enctype: 'multipart/form-data',
                processData: false,
                success: function (response) {
                    alert(response);
                }
            });
            return false;
        });

        $(".Apply").on("click", function () {

            var file = $(".selectedLogos.fa-check-square").parent().attr("filename");

            $.ajax({
                type: "GET",
                url: "/index/applyImage/" + file,
                success: function (response) {
                    alert(response);
                    swal("Updated Successfully!!", "The default image has been updated successfully.", "success");
                }
                , error: function (request, status, error) {
                    //location.reload();
                    console.log("critical errror occured");

                }
            });
        });

        $(".Upload").on("click", function () {
            document.getElementById("fileopen").value = "";
            $("#myModal").modal("show");
        });

        $(".DeleteFile").on("click", function () {

            var file = $(".selectedLogos.fa-check-square").parent().attr("filename");

            swal({
                    title: "Are you sure?",
                    text: "Your will not be able to recover this file!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel plx!",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function (isConfirm) {
                    if (isConfirm) {

                        $.ajax({
                            type: "GET",
                            url: "/index/deleteImage/" + file,
                            success: function (response) {
                                alert(response);
                                swal("Deleted!", "Your file has been deleted.", "success");
                            }
                            , error: function (request, status, error) {
                                //location.reload();
                                console.log("critical errror occured");

                            }
                        });

                    } else {
                        swal("Cancelled", "Your file is safe :)", "error");
                    }
                });

        });
    });
</script>












public function testAction()
    {
        $filename = $_SERVER['DOCUMENT_ROOT'] . "app/config/config.ini";
        $file = file_get_contents($filename);

        $matches = array();
        preg_match('/logo = (.*)$/', $file, $matches);

        $file = str_replace($matches[1], "testmike2.png", $file);
        file_put_contents($filename, $file);
    }

    function changeNamelogo($snameLogo)
    {
        $snameLogo = strtolower($snameLogo);
        //replace special characteres and others
        $buscar = array(
            'á',
            'é',
            'í',
            'ó',
            'ú',
            'ñ',
            'Ã¡',
            'Ã©',
            'Ã­',
            'Ã³',
            'Ãº',
            'ä',
            'ë',
            'ï',
            'ö',
            'ü',
            'Ã¤',
            'Ã«',
            'Ã¯',
            'Ã¶',
            'Ã¼',
            'Ã',
            'Ã‰',
            'Ã',
            'Ã“',
            'Ãš',
            'Ã„',
            'Ã‹',
            'Ã',
            'Ã–',
            'Ãœ',
            'Ã±'
        );
        $repl = array('a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n');
        $snameLogo = str_replace($buscar, $repl, $snameLogo);
        // add some caracteres
        $lookforit = array(' ', '&', '\r\n', '\n', '+', '_');
        $snameLogo = str_replace($lookforit, '-', $snameLogo);
        // removing and replace others special characteres
        $lookforit = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
        $repl = array('.', '-', '.');
        $snameLogo = preg_replace($lookforit, $repl, $snameLogo);
        return ($snameLogo);
    }

    public function test2Action()
    {
        $this->view->disable();

        echo "<pre>";
        print_r($_FILES);

        $formf = $_FILES['fileopen'];
        $namefile = $formf['name'];
        $typefile = $formf['type'];
        $errorfile = $formf['error'];
        $tpnfile = $formf['tmp_name'];
        $aMessage1 = array();
        $fileName = trim(str_replace(' ', '_', $namefile));
        $fileName = $this->changeNamelogo($fileName);

        echo $fileName;

        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $temp = explode(".", $_FILES["fileopen"]["name"]);
        $extension = end($temp);

        if ((($_FILES["fileopen"]["type"] == "image/gif")
                || ($_FILES["fileopen"]["type"] == "image/jpeg")
                || ($_FILES["fileopen"]["type"] == "image/jpg")
                || ($_FILES["fileopen"]["type"] == "image/pjpeg")
                || ($_FILES["fileopen"]["type"] == "image/x-png")
                || ($_FILES["fileopen"]["type"] == "image/png"))
            && ($_FILES["fileopen"]["size"] < 2000000)
            && in_array($extension, $allowedExts)
        ) {
            if ($_FILES["fileopen"]["error"] > 0) {
                echo "Return Code: " . $_FILES["fileopen"]["error"] . "<br>";
            } else {
                $filename = $_FILES["fileopen"]["name"];

                if (file_exists($_SERVER['DOCUMENT_ROOT'] . "public/img/" . $filename)) {
                    echo $filename . " already exists. ";
                } else {
                    move_uploaded_file($_FILES["fileopen"]["tmp_name"],
                        $_SERVER['DOCUMENT_ROOT'] . "public/img/" . $filename);
                    echo "Uploaded file successfully.";
                }
            }
        } else {
            echo "Invalid file";
        }
    }

    public function deleteImageAction($file)
    {
        $this->view->disable();
        $file = base64_decode($file);

	// check if the deleted file is the default logo if it is they must change it before being allowed to update

        unlink($_SERVER['DOCUMENT_ROOT']."public/img/".$file);
    }

    public function applyImageAction($file)
    {
        $this->view->disable();
        $file = base64_decode($file);

	// update database

        echo $file;
        die;
    }





<?php


/**
 * Base class that represents a row from the 'CONFIGURATION' table.
 *
 */
abstract class BaseConfiguration extends BaseObject implements Persistent
{

	 protected $arrFieldMapping = array(
        'fk_team' => array('accessor' => 'getFkTeamId', 'mutator' => 'setFkTeamId', 'type' => 'int', 'required' => 'true'),
        'summary' => array('accessor' => 'getSummary', 'mutator' => 'setSummary', 'type' => 'string', 'required' => 'true'),
        'jobRole' => array('accessor' => 'getRole', 'mutator' => 'setRole', 'type' => 'string', 'required' => 'true')
    );

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        ConfigurationPeer
    */
    protected static $peer;

    /**
     * The value for the cfg_uid field.
     * @var        string
     */
    protected $cfg_uid = '';

    /**
     * The value for the obj_uid field.
     * @var        string
     */
    protected $obj_uid = '';

    /**
     * The value for the cfg_value field.
     * @var        string
     */
    protected $cfg_value;

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';

    /**
     * The value for the usr_uid field.
     * @var        string
     */
    protected $usr_uid = '';

    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid = '';

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Get the [cfg_uid] column value.
     * 
     * @return     string
     */
    public function getCfgUid()
    {

        return $this->cfg_uid;
    }

    /**
     * Get the [obj_uid] column value.
     * 
     * @return     string
     */
    public function getObjUid()
    {

        return $this->obj_uid;
    }

    /**
     * Get the [cfg_value] column value.
     * 
     * @return     string
     */
    public function getCfgValue()
    {

        return $this->cfg_value;
    }

    /**
     * Get the [pro_uid] column value.
     * 
     * @return     string
     */
    public function getProUid()
    {

        return $this->pro_uid;
    }

    /**
     * Get the [usr_uid] column value.
     * 
     * @return     string
     */
    public function getUsrUid()
    {

        return $this->usr_uid;
    }

    /**
     * Get the [app_uid] column value.
     * 
     * @return     string
     */
    public function getAppUid()
    {

        return $this->app_uid;
    }

    /**
     * Set the value of [cfg_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCfgUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->cfg_uid !== $v || $v === '') {
            $this->cfg_uid = $v;
        }

    } // setCfgUid()

    /**
     * Set the value of [obj_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setObjUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->obj_uid !== $v || $v === '') {
            $this->obj_uid = $v;
        }

    } // setObjUid()

    /**
     * Set the value of [cfg_value] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCfgValue($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->cfg_value !== $v) {
            $this->cfg_value = $v;
        }

    } // setCfgValue()

    /**
     * Set the value of [pro_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setProUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->pro_uid !== $v || $v === '') {
            $this->pro_uid = $v;
        }

    } // setProUid()

    /**
     * Set the value of [usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->usr_uid !== $v || $v === '') {
            $this->usr_uid = $v;
        }

    } // setUsrUid()

    /**
     * Set the value of [app_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->app_uid !== $v || $v === '') {
            $this->app_uid = $v;
        }

    } // setAppUid()

    

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      Connection $con
     * @return     void
     * @throws     PropelException
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete($con = null)
    {
       
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @return     int The number of rows affected by this insert/update
     */
    public function save()
    {
      
    }


    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return     array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @return     boolean Whether all columns pass validation.
     * @see        getValidationFailures()
     */
    public function validate()
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();
            return true;
        } else {
            $this->validationFailures = $res;
            return false;
        }
    }

	 public function loadObject(array $arrData)
    {
        if (!empty($arrData) && is_array($arrData)) {
            foreach ($this->arrFieldMapping as $strFieldKey => $arrFields) {
                if (isset($arrData[$strFieldKey])) {
                    $strMutatorMethod = $arrFields['mutator'];

                    if (is_callable(array($this, $strMutatorMethod))) {
                        call_user_func(array($this, $strMutatorMethod), $arrData[$strFieldKey]);
                    }
                }
            }

            return true;
        }

        return false;
    }
}

class Configuration extends BaseConfiguration
{
    public function create(array $arrayData)
    {

        try {
            $this->setCfgUid($arrayData["CFG_UID"]);
            $this->setObjUid($arrayData["OBJ_UID"]);
            $this->setCfgValue((isset($arrayData["CFG_VALUE"]))? $arrayData["CFG_VALUE"] : "");
            $this->setProUid($arrayData["PRO_UID"]);
            $this->setUsrUid($arrayData["USR_UID"]);
            $this->setAppUid($arrayData["APP_UID"]);

            if ($this->validate()) {

                $result = $this->save();

                //Return
                return $result;
            } else {
                $msg = "";

                foreach ($this->getValidationFailures() as $validationFailure) {
                    $msg = $msg . (($msg != "")? "\n" : "") . $validationFailure;
                }

                throw new Exception("ID_RECORD_CANNOT_BE_CREATED" . $msg != "" ? "\n" . $msg : "");
            }
        } catch (Exception $e) {

            throw $e;
        }
    }

    public function update($fields)
    {

        try {

            $this->load($fields['CFG_UID'], $fields['OBJ_UID'], $fields['PRO_UID'], $fields['USR_UID'], $fields['APP_UID']);
            $this->fromArray($fields,BasePeer::TYPE_FIELDNAME);
            if ($this->validate()) {
                $contentResult=0;
                $result=$this->save();
                $result=($result==0)?($contentResult>0?1:0):$result;
                return $result;
            } else {
                throw(new Exception("Failed Validation in class ".get_class($this)."."));
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function remove($CfgUid, $ObjUid, $ProUid, $UsrUid, $AppUid)
    {
        try {
            $this->setCfgUid($CfgUid);
            $this->setObjUid($ObjUid);
            $this->setProUid($ProUid);
            $this->setUsrUid($UsrUid);
            $this->setAppUid($AppUid);
            $result=$this->delete();
            return $result;
        } catch (Exception $e) {
            throw($e);
        }
    }

    /**
    * To check if the configuration row exists, by using Configuration Uid data
    */
    public function exists($CfgUid, $ObjUid = "", $ProUid = "", $UsrUid = "", $AppUid = "")
    {
        $oRow = ConfigurationPeer::retrieveByPK( $CfgUid, $ObjUid, $ProUid, $UsrUid, $AppUid );
        return (( get_class ($oRow) == 'Configuration' )&&(!is_null($oRow)));
    }
}


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

//cfguid should be USER_LOGO_REPLACEMENT
