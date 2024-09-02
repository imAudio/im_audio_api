<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    protected $idUserService;
    protected $permissionService;
    public function __construct(IdUserService $idUserService,PermissionService $permissionService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
    }
    public function index()
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if($permissions["isMasterAudio"]){

                $presence = Presence::all();
                return response()->json($presence);
            }else{
                return response()->json(["message" => "You do not have the rights"], 401);
            }

        }catch (\Exception $exception){
            error_log('Exception during creation: ' . $exception->getMessage());
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
    public function create(Request $request){
        try {
            $permissions = $this->permissionService->getPermissions();

            if($permissions["isMasterAudio"]){

                $presence = Presence:: ;


                return response()->json($data);
            }else{
                return response()->json(["message" => "You do not have the rights"], 401);
            }
        }catch (\Exception $exception){
            error_log('Exception during creation: ' . $exception->getMessage());
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



    public function show($id_audio_center)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if($permissions["isMasterAudio"]){

                $presence = Presence::where("id_audio_center",$id_audio_center)
                    ->get();

                $data = $presence->map(function ($presence){
                    return [
                      "worker" => [
                          "id_worker" => $presence->id_worker,
                          "firsname" => $presence->worker->user->firstname,
                          "lastname" => $presence->worker->user->lastname
                      ],
                        "days" => $presence->days,
                    ];
                });

                return response()->json($data);
            }else{
                return response()->json(["message" => "You do not have the rights"], 401);
            }
        }catch (\Exception $exception){
            error_log('Exception during creation: ' . $exception->getMessage());
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {

    }


    public function destroy($id)
    {

    }
}
