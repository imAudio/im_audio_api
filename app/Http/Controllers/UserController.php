<?php

namespace App\Http\Controllers;

use App\Models\MasterAudio;
use App\Models\Patient;
use App\Models\Worker;
use App\Services\PermissionService;

class UserController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = auth()->user()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }


        return response()->json([
            'user' => $user,
        ]);
    }

    public function getPermission()
    {
        $permissions = $this->permissionService->getPermissions();
        return response()->json($permissions);
    }
}
