<?php

namespace App\Http\Controllers\Api;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordController extends Controller
{
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validated = Validator::make($request->all(),
                [
                    'token' => 'required',
                    'email' => 'required|email',
                    'password' => 'required|confirmed'
                ]);

            if($validated->fails()){
                return response()->json([
                    'errors' => $validated->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $status = Password::reset([
                'token' => $request->input('token'),
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'password_confirmation' => $request->input('password_confirmation'),
            ], function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->input('password')),
                    'remember_token' => Str::random(60)
                ]);
                $user->save();
                $user->tokens()->delete();
                event(PasswordReset::class);
            });

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'status' => true,
                    'message' => 'Password reset successfully!'
                ], Response::HTTP_CREATED);
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
