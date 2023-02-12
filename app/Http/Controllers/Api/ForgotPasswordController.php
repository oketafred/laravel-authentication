<?php

namespace App\Http\Controllers\Api;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validated = Validator::make($request->all(),
                [
                    'email' => 'required|email'
                ]);

            if($validated->fails()){
                return response()->json([
                    'errors' => $validated->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $status = Password::sendResetLink([
                'email' => $request->input('email')
            ]);

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Password reset email link sent!'
                ], Response::HTTP_OK);
            }
            return response()->json([
                'message' => __($status)
            ]);
        } catch (Throwable $throwable) {
            return response()->json([
                'message' => $throwable->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
