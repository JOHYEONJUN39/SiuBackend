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
   * $params : params::profile or params::post,
   * return : AWS S3にアップロードしたファイル経路URI */
  public function storeImage($imageFile, $fileName, params $params) {
    $extension = $imageFile->getClientOriginalExtension();
    $image = Storage::disk('s3')->putFileAs($params == params::profile ? '/profile-image' : '/post-image', $imageFile, $fileName.'.'.$extension);
    $path = env('AWS_CLOUDFRONT_URL');
    $imagePath = $path.$image;
    return $image ? $imagePath : $image;
  }

  /** 
   * $fileName : 削除したいフアィル名
   */

  public function destroyImage($imageURL) {
    $url = parse_url($imageURL);
    $filePath = $url['path'];
    $checkFile = Storage::disk('s3')->exists($filePath);
      if($checkFile) {
        // 왜 항상 false를 반환하는지 모르겠다
        Storage::disk('s3')->delete($filePath);
    }
    return $checkFile;
  }

}

?>