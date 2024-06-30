<?php

namespace App\Http\Controllers;

use App\Models\DeviceManufactured;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class DeviceManufacturedController extends Controller
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

                $deviceManufactureds =  DeviceManufactured::get();

                $data = $deviceManufactureds->map(function ($deviceManufactured){
                   return [
                       "id_device_manufactured" => $deviceManufactured->id_device_manufactured,
                       "content" => $deviceManufactured->content,
                   ];
                });
                return response()->json($data);
            }
            return response()->json(["message" => "You do not have the rights"],401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }


    public function create(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isMasterAudio"] == true) {
                $this->validate($request, [
                    "name" => "required",
                ]);

                DeviceManufactured::create([
                    "content" => $request->name
                ]);

                return response()->json(["message" => "DeviceManufactured create" ],201);
            }
            return response()->json(["message" => "You do not have the rights"],401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }



    public function show($id)
    {
        //
    }


    public function edit($id)
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
