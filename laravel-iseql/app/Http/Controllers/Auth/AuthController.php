<?php

namespace App\Http\Controllers\Auth;

use http\Client\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;

class AuthController extends Controller{

    public function authenticate(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $message = ['errors' => $validator->messages()->all()];
             Response::json($message, 202);
        } else {
            $credentials = $request->only('email', 'password');

            try {
                $token = JWTAuth::attempt($credentials);

                if ($token) {
                    $message = ['success' => $token];
                    return Response::json(["token" => $token], 200);
                } else {
                    $message = ['errors' => "Invalid credentials"];
                    return  Response::json($message, 202);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'could_not_create_token'], 500);
            }
        }
    }
}
