<?php

namespace App\Http\Controllers;

use App\Models\DeviceType;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Exception;
use Illuminate\Http\Request;

class DeviceTypeController extends Controller
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
            if($permissions["isWorker"] == true){

                $deviceType = DeviceType::get();

                return response()->json($deviceType);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
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
