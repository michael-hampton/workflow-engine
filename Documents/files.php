<?php
// Free Ajax-PHP File Manager - from: http://coursesweb.net/

// Class with actions for files
class Files {
  static public $res = '';    // additional string with response from accessed method

  // copy the file from $path to $new_path. Returns response string
  static public function copyFile($path, $new_path) {
    if(is_file($path)){
      $new_path = $new_path .'/'. FmFile::MakeUniqueFilename($new_path, basename($path));
      if(copy($path, fixPath($new_path))) $re = 'ok';
      else $re = 'E_CopyFile';
    }
    else $re = 'E_InvalidPath';

    return $re;
  }

  // delete the file from $path. Returns response string
  static public function deleteFile($path) {
    if(is_file($path)){
      if(unlink($path)) $re = 'ok';
      else  {
        $re = 'E_DeletеFile';
        self::$res = ' '. basename($path);
      }
    }
    else $re = 'E_InvalidPath';

    return $re;
  }

  // download the file from $path. Returns header and the file
  static public function downloadFile($path) {
    if(is_file($path)){
      $file = urldecode(basename($path));
      header('Content-Disposition: attachment; filename="'.$file.'"');
      header('Content-Type: application/force-download');
      readfile($path);
    }
    else {
      self::$res = ' '. $path;
      return 'E_InvalidPath';
    }
  }

  // put the content $filecnt to file $path. Returns array with file size, time, or string with message
  static public function editFile($path, $filecnt) {
      if(!FmFile::canEditFile($path)) $re = 'E_FileExtensionForbidden';
      else if(!file_exists($path)) {
        $re = 'E_InvalidPath';
        self::$res = ' '. $path;
      }
      else if(file_put_contents($path, $filecnt)) {
        $re = ['ok'=>'S_EditFile', 'size'=>filesize($path), 'time'=>filemtime($path)];
      }
      else {
        $re = 'E_EditFile';
        self::$res = ' '. $path;
      }

    return $re;
  }

  // moves file from $path to $new_path. Returns response string
  static public function moveFile($path, $new_path) {
    if(!$new_path) $new_path = MAIN_ROOT;
    if(is_file($path)){
      if(file_exists($new_path)) {
        $re = 'E_MoveFileAlreadyExists';
        self::$res = ' '. basename($new_path);
      }
      else if(rename($path, fixPath($new_path))) $re = 'ok';
      else {
        $re = 'E_MoveFile';
        self::$res = ' '.basename($path);
      }
    }
    else $re = 'E_InvalidPath';

    return $re;
  }

  // rename the file from $path with new $name. Returns response string
  static public function renameFile($path, $name) {
    if(is_file($path)){
      if(!FmFile::CanUploadFile($name)) {
        $re = 'E_FileExtensionForbidden';
        self::$res = ' ".'.FmFile::GetExtension($name).'"';
      }
      elseif(rename($path, dirname($path) .'/'. FmFile::CleanupFilename($name))) $re = 'ok';
      else {
        $re = 'E_RenameFile';
        self::$res = ' '.basename($path);
      }
    }
    else $re = 'E_InvalidPath';

    return $re;
  }

  // calls FmImage methods to get hummb of image/s from $path
  static public function getThumb($path) {
    @chmod(dirname($path), octdec(DIRPERMISSIONS));
    @chmod($path, octdec(FILEPERMISSIONS));

    $w = intval(empty($_GET['width']) ? 100 : $_GET['width']);
    $h = intval(empty($_GET['height']) ? 0 : $_GET['height']);

    header('Content-type: '.FmFile::GetMIMEType(basename($path)));
    if($w && $h) FmImage::CropCenter($path, null, $w, $h);
    else FmImage::Resize($path, null, $w, $h);
  }

  // Upload file to $path. Returns response string
  static public function upload($path) {
    $re = '';
    if(is_dir($path)){
      if(!empty($_FILES['files']) && is_array($_FILES['files']['tmp_name'])){
        $errors = $errorsExt = array();
        foreach($_FILES['files']['tmp_name'] as $k=>$v){
          $filename = $_FILES['files']['name'][$k];
          $filename = FmFile::MakeUniqueFilename($path, $filename);
          $filePath = $path .'/'. $filename;
          if(!FmFile::CanUploadFile($filename)) $errorsExt[] = $filename;
          else if(!move_uploaded_file($v, $filePath)) $errors[] = $filename;
          if(FmFile::IsImage($filename) && (intval(MAX_IMAGE_WIDTH) > 0 || intval(MAX_IMAGE_HEIGHT) > 0)) FmImage::Resize($filePath, $filePath, intval(MAX_IMAGE_WIDTH), intval(MAX_IMAGE_HEIGHT));
        }
        if($errors && $errorsExt)  $re = 'E_UploadNotAll';
        else if($errorsExt) $re = 'E_FileExtensionForbidden';
        else if($errors) $re = 'E_UploadNotAll';
        else $re = 'S_UploadFile';
      }
      else $re = 'E_UploadNoFiles';
    }
    else $re = 'E_InvalidPath';

    return '<script>
parent.fileUploaded("'. $re .'");
</script>';
  }
}