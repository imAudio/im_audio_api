<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Exception;
use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    protected $permissionService;
    protected $idUserService;
    public function __construct(IdUserService $idUserService,PermissionService $permissionService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
    }

    public function getByAudioCenter($id_audio_center){
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $deliveryNote = DeliveryNote::where("id_audio_center",$id_audio_center)
                    ->orderBy('created_at', 'desc')
                    ->get();

                return response()->json($deliveryNote);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }
}
