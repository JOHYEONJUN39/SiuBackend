<?php

namespace App\Helpers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;


class ValidateHelper {

  public function validate(Request $request, array $rules) {
    try {
      $validated = $request->validate($rules);
    } catch(ValidationException $e) {
      // It will return errMsg from ValidationException
      throw new Exception($e->getMessage(), 422);
    }
    return $validated;
  }

}

?>