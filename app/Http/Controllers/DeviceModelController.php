<?php

namespace App\Http\Controllers;

use App\Models\DeviceModel;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class DeviceModelController extends Controller
{
    protected $permissionService;
    protected $idUserService;

    public function __construct(IdUserService $idUserService, PermissionService $permissionService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
    }
    public function index()
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $deviceModel=DeviceModel::get();

                return response()->json($deviceModel);
            }

            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (\Exception $exception) {
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function byManufactured($id_manufactured)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $deviceModel=DeviceModel::where("id_device_manufactured",$id_manufactured)->get();

                return response()->json($deviceModel);
            }

            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (\Exception $exception) {
            return response()->json(["error" => $exception->getMessage()], 500);
        }
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
