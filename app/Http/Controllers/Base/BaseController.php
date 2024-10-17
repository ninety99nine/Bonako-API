<?php

namespace App\Http\Controllers\Base;

use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Prepare the output for the response.
     *
     * @param mixed $output
     * @param string $status
     * @return JsonResponse
     */
    protected function prepareOutput($output, string $status = Response::HTTP_OK): JsonResponse|view
    {
        if($output instanceof View) return $output;
        return response()->json($output, $status);
    }
}
