<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class EventTypeController extends Controller
{

    protected $permissionService;
    protected $idUserService;
    public function __construct(IdUserService $idUserService,PermissionService $permissionService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
    }
    public function index()
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {
                $eventTypes = EventType::get();

                $data = $eventTypes->map(function ($eventType){
                   return [
                     'id_event_type' => $eventType->id_event_type,
                     'content' => $eventType->content,
                     'default_duration' => $eventType->default_duration,
                   ];
                });
                return response()->json($data);
            }
            return response()->json(["message" => "You do not have the rights"],401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function create()
    {
        //
    }


    public function show($id)
    {
        //
    }




    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
}
