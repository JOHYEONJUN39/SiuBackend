<?php

namespace App\Helpers;
use Exception;
use Illuminate\Support\Facades\Storage;


class ImageHelper {

  /**
   * $imageFile : アップロードしたいファイル,
   * $fileName : ファイル名,
   * return : AWS S3にアップロードしたファイル経路URI */
  public function storeProfileImage($imageFile, $fileName) {
    $image = Storage::disk('s3')->putFileAs('/profile-image', $imageFile, $fileName.'.jpg');
    $path = env('AWS_CLOUDFRONT_URL');
    $imagePath = $path.$image;
    return ($image == false) ? $imagePath : $image;
  }

  public function storePostImage($imageFile, $fileName) {
    $image = Storage::disk('s3')->putFileAs('/post-image', $imageFile, $fileName.'.jpg');
    $path = env('AWS_CLOUDFRONT_URL');
    $imagePath = $path.$image;
    return ($image == false) ? $imagePath : $image;
  }

  public function deletePostImage($path) {
    try {
      $a = Storage::disk('s3')->delete('/profile-image/'.$path);
      return $a;
    } catch(Exception $e) {
      return $e;
    }
  }

}

?>