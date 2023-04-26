<?php

namespace OxygenModule\Pages;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentChildCycleException extends \Exception {

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse {
        return response()->json([
            'content' => 'Refusing to set parent->child relationship that will result in a cycle',
            'status' => 'failed'
        ]);
    }

}