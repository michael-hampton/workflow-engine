<?php
// Free Ajax-PHP File Manager - from: http://coursesweb.net/

// return array with text language
function loadTL($lang){
  $lang = ($lang == '') ? 'en' : $lang;
  $langPath = $_SERVER['DOCUMENT_ROOT'].'/FormBuilder/app/libraries/fileman/lang/'. $lang .'.json';
  if(!is_file($langPath)) $langPath = 'lang/en.json';
  $re = json_decode(file_get_contents($langPath), true);

  return $re;
}
// return text language associated to $tab
function getTL($tab) {
  GLOBAL $tl;
  return isset($tl[$tab]) ? $tl[$tab] : $tab;
}

// Make the $path absolute, and clean
function fixPath($path){
  $path = trim(strip_tags($path));
  $path = $_SERVER['DOCUMENT_ROOT'].'/'. rtrim($path, '/');
  $path = str_replace(['\\', '//'], '/', $path);
  return $path;
}

// returns array with root added in FILES_ROOT, or fullpath of "fileman/uploads"
function getFilesPath(){
  $ret = str_replace(' ', '', FILES_ROOT);
  if(!$ret){
    $ret = FmFile::FixPath(BASE_PATH.'/uploads');
    $ret = str_replace(FmFile::FixPath($_SERVER['DOCUMENT_ROOT']), '', $ret);
  }
  $ret = explode(',', trim($ret));
  
  return $ret;
}
function listDirectory($path){
  $ret = @scandir($path);
  if($ret === false){
    $ret = array();
    if(file_exists($path)) $d = opendir($path);
    if(isset($d)){
      while(($f = readdir($d)) !== false){
        $ret[] = $f;
      }
      closedir($d);
    }
  }
  
  return $ret;
}

// Class for Dirs and Files name
class FmFile{
  static public function CheckWritable($dir){
    $ret = false;
    if(self::CreatePath($dir)){
      $dir = self::FixPath($dir.'/');
      $testFile = 'writetest.txt';
      $f = @fopen($dir.$testFile, 'w', false);
      if($f){
        fclose($f);
        $ret = true;
        @unlink($dir.$testFile);
      }
    }

    return $ret;
  }
  static function CanUploadFile($filename){
    $ret = false;
    $forbidden = array_filter(preg_split('/[^\d\w]+/', strtolower(FORBIDDEN_UPLOADS)));
    $allowed = array_filter(preg_split('/[^\d\w]+/', strtolower(ALLOWED_UPLOADS)));
    $ext = FmFile::GetExtension($filename);

    if((empty($forbidden) || !in_array($ext, $forbidden)) && (empty($allowed) || in_array($ext, $allowed)))
      $ret = true;

    return $ret;
  }
  static function canEditFile($filename){
    $ret = false;
    $allowed = array_filter(preg_split('/[^\d\w]+/', strtolower(EDITFILE)));
    $ext = FmFile::GetExtension($filename);
    if(empty($allowed) || in_array($ext, $allowed)) $ret = true;

    return $ret;
  }
  static function IsImage($fileName){
    $ret = false;
    $ext = strtolower(self::GetExtension($fileName));
    if(in_array($ext, ['jpg', 'jpeg', 'jpe', 'png', 'gif', 'ico'])) $ret = true;
    return $ret;
  }

  /**
   * Returns MIME type of $filename
   *
   * @param string $filename
   * @return string
   */
  static function GetMIMEType($filename){
    $type = 'application/octet-stream';
    $ext = self::GetExtension($filename);

    switch(strtolower($ext)){
      case 'jpg':  $type = 'image/jpeg';break;
      case 'jpeg': $type = 'image/jpeg';break;
      case 'gif':  $type = 'image/gif';break;
      case 'png':  $type = 'image/png';break;
      case 'bmp':  $type = 'image/bmp';break;
      case 'tiff': $type = 'image/tiff';break;
      case 'tif':  $type = 'image/tiff';break;
      case 'pdf':  $type = 'application/pdf';break;
      case 'rtf':  $type = 'application/msword';break;
      case 'doc':  $type = 'application/msword';break;
      case 'xls':  $type = 'application/vnd.ms-excel'; break;
      case 'zip':  $type = 'application/zip'; break;
      case 'swf':  $type = 'application/x-shockwave-flash'; break;
      default: $type = 'application/octet-stream';
    }

    return $type;
  }

  /**
   * Replaces any character that is not letter, digit or underscore from $filename with $sep
   *
   * @param string $filename
   * @param string $sep
   * @return string
   */
  static function CleanupFilename($filename, $sep = '_'){
    $str = '';
    if(strpos($filename,'.')){
      $ext = self::GetExtension($filename) ;
      $name = self::GetName($filename);
    }
    else{
      $ext = '';
      $name = $filename;
    }
    $str = mb_ereg_replace("[^ a-zA-Z\\_\\d\\.]|\\s", $sep, $name);
    $str = mb_ereg_replace("$sep+", $sep, $str).'.'.$ext;

    return $str;
  }

  /**
   * Returns file extension without dot
   *
   * @param string $filename
   * @return string
   */
  static function GetExtension($filename) {
    $ar_ext = explode('.', $filename);
    $ext = end($ar_ext);

    return strtolower($ext);
  }

  /**
   * Returns file name without extension
   *
   * @param string $filename
   * @return string
   */
  static function GetName($filename) {
    $name = '';
    $tmp = mb_strpos($filename, '?');
    if($tmp !== false)  $filename = mb_substr ($filename, 0, $tmp);
    $dotPos = mb_strrpos($filename,'.');
    if($dotPos !== false) $name = mb_substr($filename, 0, $dotPos);
    else $name = $filename;

    return $name;
  }
  static function GetFullName($filename) {
    $tmp = mb_strpos($filename, '?');
    if($tmp !== false)
      $filename = mb_substr ($filename, 0, $tmp);
    $filename = basename($filename);

    return $filename;
  }
  static public function FixPath($path){
    $path = mb_ereg_replace('[\\\/]+', '/', $path);
    return $path;
  }
  /**
   * creates unique file name using $filename( " - Copy " and number is added if file already exists) in directory $dir
   *
   * @param string $dir
   * @param string $filename
   * @return string
   */
  static function MakeUniqueFilename($dir, $filename){
    $temp = '';
    $dir .= '/';
    $dir = self::FixPath($dir.'/');
    $ext = self::GetExtension($filename);
    $name = self::GetName($filename);
    $name = mb_ereg_replace(' - Copy \\d+$', '', $name);
    if($ext) $ext = '.'.$ext;
    if(!$name) $name = 'file';

    $i = 0;
    do{
      $temp = ($i? $name." - Copy $i".$ext: $name.$ext);
      $i++;
    }while(file_exists($dir.$temp));

    return $temp;
  }
  /**
   * creates unique directory name using $name( " - Copy " and number is added if directory already exists) in directory $dir
   *
   * @param string $dir
   * @param string $name
   * @return string
   */
  static function MakeUniqueDirname($dir, $name){
    $temp = '';
    $dir = self::FixPath($dir.'/');
    $name = mb_ereg_replace(' - Copy \\d+$', '', $name);
    if(!$name) $name = 'directory';

    $i = 0;
    do{
      $temp = ($i? $name." - Copy $i": $name);
      $i++;
    }while(is_dir($dir.$temp));

    return $temp;
  }
}
// End Class for Dirs and Files name

// Class for Images
class FmImage{
  public static function GetImage($path){
    $img = null;
    switch(FmFile::GetExtension(basename($path))){
      case 'png':
        $img = imagecreatefrompng($path);
        break;
      case 'gif':
        $img = imagecreatefromgif($path);
        break;
      default:
        $img = imagecreatefromjpeg($path);
    }
    return $img;
  }
  public static function OutputImage($img, $type, $destination = '', $quality = 90){
    if(is_string($img))
      $img = self::GetImage ($img);
    switch(strtolower($type)){
      case 'png':
        imagepng($img, $destination);
        break;
      case 'gif':
        imagegif($img, $destination);
        break;
      default:
        imagejpeg($img, $destination, $quality);
    }
  }
  public static function Resize($source, $destination, $width = '150',$height = 0, $quality = 90) {
    $tmp = getimagesize($source);
    $w = $tmp[0];
    $h = $tmp[1];
    $r = $w / $h;

    if($w <= ($width + 1) && (($h <= ($height + 1)) || (!$height && !$width))){
      if($source != $destination) self::OutputImage($source, FmFile::GetExtension(basename($source)), $destination, $quality);
      return;
    }
    
    $newWidth = $width;
    $newHeight = floor($newWidth / $r);
    if(($height > 0 && $newHeight > $height) || !$width){
      $newHeight = $height;
      $newWidth = intval($newHeight * $r);
    }

    $thumbImg = imagecreatetruecolor($newWidth, $newHeight);
    $img = self::GetImage($source);
    imagecopyresampled($thumbImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $w, $h);

    self::OutputImage($thumbImg, FmFile::GetExtension(basename($source)), $destination, $quality);
  }
  public static function CropCenter($source, $destination, $width, $height, $quality = 90) {
    $tmp = getimagesize($source);
    $w = $tmp[0];
    $h = $tmp[1];
    if(($w <= $width) && (!$height || ($h <= $height))){
      self::OutputImage(self::GetImage($source), FmFile::GetExtension(basename($source)), $destination, $quality);
    }
    $ratio = $width / $height;
    $top = $left = 0;

    $cropWidth = floor($h * $ratio);
    $cropHeight = floor($cropWidth / $ratio);
    if($cropWidth > $w){
      $cropWidth = $w;
      $cropHeight = $w / $ratio;
    }
    if($cropHeight > $h){
      $cropHeight = $h;
      $cropWidth = $h * $ratio;
    }

    if($cropWidth < $w){
       $left = floor(($w - $cropWidth) / 2);
    }
    if($cropHeight < $h){
       $top = floor(($h- $cropHeight) / 2);
    }

    self::Crop($source, $destination, $left, $top, $cropWidth, $cropHeight, $width, $height, $quality);
  }
  public static function Crop($source, $destination, $x, $y, $cropWidth, $cropHeight, $width, $height, $quality = 90) {
    $thumbImg = imagecreatetruecolor($width, $height);
    $img = self::GetImage($source);
    imagecopyresampled($thumbImg, $img, 0, 0, $x, $y, $width, $height, $cropWidth, $cropHeight);

    self::OutputImage($thumbImg, FmFile::GetExtension(basename($source)), $destination, $quality);
  }
}
// End Class For Images

// set Constants wits $fm_conf data, and main-root (1st)
if($fm_conf){
  // if dir-root set in session, add it in FILES_ROOT too
  if(isset($_SESSION[$fm_conf['SESSION_PATH_KEY']]) && $_SESSION[$fm_conf['SESSION_PATH_KEY']] != '') $fm_conf['FILES_ROOT'] = $_SESSION[$fm_conf['SESSION_PATH_KEY']];

  foreach ($fm_conf as $k=>$v) define($k, $v);
}
else exit('Error parsing configuration');

define('MAIN_ROOT', fixPath(getFilesPath()[0]));
if(DEL_ROOT == 0 && !is_dir(MAIN_ROOT)) @mkdir(MAIN_ROOT, octdec(DIRPERMISSIONS));