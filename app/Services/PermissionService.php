<?php

namespace App\Services;

use App\Models\Worker;
use App\Models\Patient;
use App\Models\MasterAudio;
use Tymon\JWTAuth\Facades\JWTAuth;

class PermissionService
{
    public function getPermissions()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        $isWorker = Worker::where('id_user', $user->id_user)->exists();
        $isPatient = Patient::where('id_user', $user->id_user)->exists();
        $isMasterAudio = MasterAudio::where('id_worker', $user->id_user)->exists();

        return [
            'isWorker' => $isWorker,
            'isPatient' => $isPatient,
            'isMasterAudio' => $isMasterAudio
        ];
    }
}
