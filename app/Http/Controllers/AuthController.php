<?php

namespace App\Http\Controllers;

use App\Mail\CodeResetMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;
use App\Models\User;
use function Symfony\Component\Translation\t;

class AuthController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = $this->jwt->attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'),200);
    }

    public function creatSendCodePassword(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
            ]);



            if(User::where("email", $request->email)->first()){
                $user = User::where("email", $request->email)->first();
            }else{
                return response()->json(["message" => "user not found"], 404);
            }

            $code_reset = generateRandomCode();
            $user->code_reset = Hash::make($code_reset);
            $user->code_time = Carbon::now()->addMinutes(15);

            $user->save();

            $data = [
                'message' => $code_reset,
            ];


            Mail::to($request->email)->send(new CodeResetMail($data));

            return response()->json([],204);

        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function checkCode(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
                'code_reset' => 'required',
            ]);

            $user = User::where("email",$request->email)->first();

            if(!$user){
                return response()->json(["message" => "user not found"],200);
            }

            if ($user->code_time <= date('Y-m-d h:i:s')){
                return response()->json(["message" => "code expired"],200);
            }

            if (Hash::check($request->code_reset, $user->code_reset)) {
                return response()->json(["message" => "ok"], 204);
            }

            return response()->json(["message" => "code invalid"], 400);

        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function newPassword(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
                'password' => 'required',
            ]);

            $user = User::where("email",$request->email)->first();

            if(!$user){
                return response()->json(["message" => "user not found"],200);
            }
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([], 204);

        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }

}
function generateRandomCode($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
}
