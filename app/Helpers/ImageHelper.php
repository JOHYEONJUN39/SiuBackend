<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Storage;


class ImageHelper {

  /**
   * $imageFile : アップロードしたいファイル,
   * $fileName : ファイル名,
   * return : AWS S3にアップロードしたファイル経路URI */
  public function storeProfileImage($imageFile, $fileName) {
    $imagePath = env('AWS_CLOUDFRONT_URL').Storage::disk('s3')->putFileAs('/profile-image', $imageFile, $fileName.'.jpg');
    return $imagePath;
  }

  public function storePostImage($imageFile, $fileName) {
    $imagePath = env('AWS_CLOUDFRONT_URL').Storage::disk('s3')->putFileAs('/post-image', $imageFile, $fileName.'.jpg');
    return $imagePath;
  }

}

?>