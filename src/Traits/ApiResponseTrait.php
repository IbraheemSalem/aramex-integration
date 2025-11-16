<?php

namespace Ibraheem\AramexIntegration\Traits;

trait ApiResponseTrait
{
    /**
     * Return success response.
     */
    protected function success($data = null, $message = 'Success', $statusCode = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return error response.
     */
    protected function error($message = 'Error', $errorCode = null, $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errorCode) {
            $response['error_code'] = $errorCode;
        }

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return data response.
     */
    protected function data($data, $message = 'Success', $statusCode = 200)
    {
        return $this->success($data, $message, $statusCode);
    }
}

