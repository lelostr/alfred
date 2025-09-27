<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller {

    public function successResponse($message = null, $data = [], $code = 200) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function errorResponse($message = null, $errors = [], $code = 400) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => empty($errors) ? [$message] : $errors,
        ], $code);
    }
}
