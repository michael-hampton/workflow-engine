<?php
// Free Ajax-PHP File Manager - from: http://coursesweb.net/

// Class with actions for directories
class Dirs {
  static public $res = '';    // additional string with response from accessed method
  static $dir_tree = ['dirs'=>[], 'files'=>[]];

  // get and return a multi-dimensional array with all the directories and their data
  static public function dirTree() {
    $re_ar = [];

    function getFilesData($path){
      $files = 0;
      $dirs = 0;
      $tmp = listDirectory($path);
      foreach($tmp as $ff){
        if($ff == '.' || $ff == '..') continue;
        else if(is_file($path .'/'. $ff)) {
          $files++;
          if($path == MAIN_ROOT) Dirs::$dir_tree['files'][] = Dirs::setFileData($path .'/'. $ff);
        }
        else if(is_dir($path .'/'. $ff)) $dirs++;
      }

      return ['files'=>$files, 'dirs'=>$dirs];
    }
    function GetDirs($path){
      $ret = $sort = [];
      $files = listDirectory(fixPath($path), 0);
      foreach($files as $f){
        $fullPath = rtrim($path, '/') .'/'.$f;
        if(!is_dir(fixPath($fullPath)) || $f == '.' || $f == '..') continue;
        $tmp = getFilesData(fixPath($fullPath));
        $ret[$fullPath] = ['path'=>$fullPath, 'files'=>$tmp['files'], 'dirs'=>$tmp['dirs']];
        $sort[$fullPath] = $f;
      }
      natcasesort($sort);
      foreach($sort as $k => $v) {
        $tmp = $ret[$k];
        Dirs::$dir_tree['dirs'][] = ['p'=>$tmp['path'], 'f'=>$tmp['files'], 'd'=>$tmp['dirs']];
        GetDirs($tmp['path']);
      }
    }

    $root_dirs = getFilesPath();    // get Root dirs

    // get and output all the dirs
    $nr_rd = count($root_dirs);
    for($i=0; $i<$nr_rd; $i++) {
      $tmp = getFilesData(fixPath($root_dirs[$i]));
      Dirs::$dir_tree['dirs'][] = ['p'=>$root_dirs[$i], 'f'=>$tmp['files'], 'd'=>$tmp['dirs']];
      GetDirs($root_dirs[$i]);
    }
    return Dirs::$dir_tree;
  }

  // return Array with dat of file from $path
  static function setFileData($path) {
    $size = filesize($path);
    $time = filemtime($path);
    $tmp = @getimagesize($path);
    $w = 0;
    $h = 0;
    if($tmp){
      $w = $tmp[0];
      $h = $tmp[1];
    }
    return ['p'=>str_replace(['"', $_SERVER['DOCUMENT_ROOT']], ['\\"', ''], $path), 's'=>$size, 't'=>$time, 'w'=>$w, 'h'=>$h];
  }

  // Returns array with files data in dir $path
  static public function dirFiles($path) {
    $re = [];
    $files = listDirectory($path, 0);
    natcasesort($files);

    foreach ($files as $f){
      if(!is_file($path .'/'. $f)) continue;
      else $re[] = Dirs::setFileData($path .'/'. $f);
    }

    return $re;
  }

  // create directory $name in $path. Returns response string
  static public function createDir($path, $name) {
   if(is_dir($path)){
      if(mkdir($path .'/'. $name, octdec(DIRPERMISSIONS))) $re = 'ok';
      else {
        $re = 'E_CreateDirFailed';
        self::$res = ' '. $name;
      }
    }
    else {
      $re = 'E_InvalidPath' .' '. $path;
      self::$res = ' '. $path;
    }

    return $re;
  }

  // receive dir-path, and string with constant name. Return 1 if allowed permission, otherwise 0
  static protected function affectDir($path, $const) {
    $ar_root = array_map('fixPath', getFilesPath());
    $re = (!in_array($path, $ar_root) || (defined($const) && constant($const) == 1)) ? 1 : 0;
    return $re;
  }

  // delete directory with $path. Returns response string
  static public function deleteDir($path) {
    if(is_dir($path)){
      $re = 'ok';
      if(self::affectDir($path, 'DEL_ROOT') == 0) $re = 'E_CannotDelRoot';
      else {
        $iterator = new RecursiveDirectoryIterator($path);
        foreach(new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
          if($file->isFile()) unlink($file->getPathname());
          else @rmdir($file->getPathname());
       }
       if(file_exists($path)) $re = @rmdir($path) ? 'ok' : 'E_DeleteDir';
      }
    }
    else {
      $re = 'E_InvalidPath';
      self::$res = ' '. $path;
    }

    return $re;
  }

  // move directory from $path to $new_path. Returns response string
  static public function moveDir($path, $new_path) {
    if(is_dir($path)){
      if(self::affectDir($path, 'MOVE_ROOT') == 0) $re = 'E_CannotMoveRoot';
      else if(mb_strpos($new_path, $path) === 0) $re = 'E_CannotMoveDirToChild';
      else {
        $new_path = fixPath($new_path);
        if(file_exists($new_path .'/'. basename($path))) $re = 'E_DirAlreadyExists';
        else if(rename($path, $new_path .'/'. basename($path))) $re = 'ok';
        else {
          $re = 'E_MoveDir';
          self::$res = ' '. basename($path);
        }
      }
    }
    else $re = 'E_MoveDirInvalisPath';

    return $re;
  }

  // copy directory from $path to $new_path. Returns response string
  static public function copyDir($path, $new_path) {
    if(is_dir($path)){
      $re = [];

      // get iterator object with all dirs and files in $path source, to copy them
      $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);

      // store the folders and files which to copy, in $dirs , $files
      $dirs = $files = [];
      foreach($iterator as $item) {
        $name = str_ireplace($path, '', FmFile::FixPath($item->getPathname()));
        if($item->isDir()) $dirs[] = $name;
        else $files[] = $name;
      }
      $nr_d = count($dirs);
      $nr_f = count($files);

      // make $new_path full-path from system root, and create base folder where to copy
      $new_path = $_SERVER['DOCUMENT_ROOT']. FmFile::FixPath($new_path) . DIRECTORY_SEPARATOR . basename($path);
      if(!is_dir($new_path)) mkdir($new_path, octdec(DIRPERMISSIONS));

      // create destination folders, if not exists
      for($i=0; $i<$nr_d; $i++) {
        $dir = $new_path . $dirs[$i];
        if(!is_dir($dir)) {
          if(!@mkdir($dir, octdec(DIRPERMISSIONS))) $re[] = getTL('E_Create') .': '. $dirs[$i];
        }
      }

      // copy the files
      for($i=0; $i<$nr_f; $i++) {
        if(!@copy($path . $files[$i], $new_path . $files[$i])) $re[] = getTL('E_Copy') .': '. $files[$i];
      }

      $re = (count($re) == 0) ? ['ok'] : $re;
    }
    else $re = 'E_CopyDirInvalidPath';

    return $re;
  }

  // rename directory from $path with $new_name. Returns response string
  static public function renameDir($path, $new_name) {
    if(is_dir($path)){
      if(self::affectDir($path, 'RENAME_ROOT') == 0) $re = 'E_CannotRenameRoot';
      else if(rename($path, dirname($path) .'/'. $new_name)) $re = 'ok';
      else $re = 'E_RenameDir';
    }
    else  $re = 'E_RenameDirInvalidPath';

    return $re;
  }

  // download directory from $path. Returns header and ZIP archive
  static public function downloadDir($path) {
    @ini_set('memory_limit', -1);
    if(!class_exists('ZipArchive')) $re = '<script>alert("'.  addslashes(getTL('E_ZipArchive')) .'");</script>';
    else{
      function ZipAddDir($path, $zip, $zipPath){
        $d = opendir($path);
        $zipPath = str_replace('//', '/', $zipPath);
        if($zipPath && $zipPath != '/') $zip->addEmptyDir($zipPath);

        while(($f = readdir($d)) !== false){
          if($f == '.' || $f == '..') continue;
          $filePath = $path.'/'.$f;
          if(is_file($filePath)) $zip->addFile($filePath, ($zipPath ? $zipPath .'/' : '').$f);
          elseif(is_dir($filePath)) ZipAddDir($filePath, $zip, ($zipPath ? $zipPath .'/' : '').$f);
        }
        closedir($d);
      }
      function ZipDir($path, $zipFile, $zipPath = ''){
        $zip = new ZipArchive();
        $zip->open($zipFile, ZIPARCHIVE::CREATE);
        ZipAddDir($path, $zip, $zipPath);
        $zip->close();
      }

      try{
        $filename = basename($path);
        $zipFile = $filename. '.zip';
        $zipPath = BASE_PATH. '/tmp/'. $zipFile;
        ZipDir($path, $zipPath);

        // To clean all html out put before send zip content to browser
        ob_clean();
        ob_end_flush();

        // Send zip content to browser
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="'. $zipFile. '"');
        header('Content-Type: application/force-download');
        readfile($zipPath);
        function deleteTmp($zipPath){
          @unlink($zipPath);
        }
        register_shutdown_function('deleteTmp', $zipPath); exit;
      }
      catch(Exception $ex){
        $re = '<script>alert("'.  addslashes(getTL('E_CreateArchive')) .'");</script>';
      }
    }
    return $re;
  }
}