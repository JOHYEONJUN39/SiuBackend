<?php

namespace App\Helpers;
use Exception;
use Illuminate\Support\Facades\Storage;


enum params {
  case post;
  case profile;
}

class ImageHelper {
  /**
   * $imageFile : アップロードしたいファイル,
   * $fileName : ファイル名,
   * return : AWS S3にアップロードしたファイル経路URI */
  public function storeImage($imageFile, $fileName, params $params) {
    $image = Storage::disk('s3')->putFileAs($params == params::profile ? '/profile-image' : '/post-image', $imageFile, $fileName.'.jpg');
    $path = env('AWS_CLOUDFRONT_URL');
    $imagePath = $path.$image;
    return ($image == false) ? $imagePath : $image;
  }

  public function destroyImage($path, params $params) {
    $delete = Storage::disk('s3')->delete($params == params::profile ? '/profile-image' : '/post-image'.$path);
    return $delete;
  }

}

?>