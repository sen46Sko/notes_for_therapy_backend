<?php
namespace App\Helper;
use Storage;

class StorageHelper
{
  var $userId;
  var $folder;
  public function __construct($userId, $folder) {
    $this->userId = $userId;
    $this->folder = $folder;
  }

  function storeFile($file)
  {
    $location = 'user/' . $this->userId . '/' . $this->folder;
    $path = Storage::putFile($location, $file);
    $url = Storage::url($path);
    return $url;
  }

  function getFile($fileName) {
    $location = 'user/' . $this->userId . '/' . $this->folder . '/' . $fileName;
    return Storage::url($location);
  }
}
