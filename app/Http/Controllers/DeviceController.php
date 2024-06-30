<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SetSail;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class DeviceController extends Controller
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

    }

    public function getSetSailByState($state,$idAudioCenter)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $devices = Device::whereHas('setSail', function ($query) use ($state,$idAudioCenter) {
                    $query->where('state', $state);
                    $query->where('id_audio_center', $idAudioCenter);
                })->with('setSail.patient')->get();

                $data = $devices->map(function ($device) {

                    return [
                        'id_device' => $device->id_device,
                        'sav_date' => $device->sav_date,
                        'serial_number' => $device->serial_number,
                        'patient' => [
                            'lastName' => $device->setSail->patient->lastname,
                            'firstName' => $device->setSail->patient->firstname,
                        ],
                    ];
                });

                return response()->json($data);
            }

            return response()->json(["message" => "You do not have the rights"], 401);

        }catch (\Exception $exception){
            return response()->json($exception);
        }
    }

    public function getByState($state, $idAudioCenter, $idManufactured, $contentModele)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {
                $query = Device::where("id_audio_center", $idAudioCenter)
                    ->where("state", $state);

                if ($idManufactured != 0) {
                    $query->whereHas('deviceModel', function ($query) use ($idManufactured) {
                        $query->where('id_device_manufactured', $idManufactured);
                    });
                }

                if (!empty($contentModele)) {
                    $query->whereHas('deviceModel', function ($query) use ($contentModele) {
                        $query->where('content', 'LIKE', '%' . $contentModele . '%');
                    }); 
                }

                $devices = $query->with(['deviceModel' => function ($query) use ($idManufactured) {
                    if ($idManufactured != 0) {
                        $query->where('id_device_manufactured', $idManufactured);
                    }
                }])->get();

                $data = $devices->map(function ($device) {
                    return [
                        "id_device" => $device->id_device,
                        "serial_number" => $device->serial_number,
                        "sav_date" => $device->sav_date,
                        "model" => [
                            "id_device_model" => $device->deviceModel->id_device_model,
                            "content" => $device->deviceModel->content,
                        ],
                       "type" => [
                            "id_device_type" => $device->deviceModel->deviceType->id_device_type,
                            "content" => $device->deviceModel->deviceType->content,
                        ],
                        "manufactured" => [
                            "id_device_manufactured" => $device->deviceModel->deviceManufactured->id_device_manufactured,
                            "content" => $device->deviceModel->deviceManufactured->content,
                        ],
                    ];
                });

                return response()->json($data);

            }

            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (\Exception $exception) {
            return response()->json($exception);
        }
    }



    public function create()
    {

    }

    public function show($id)
    {

    }

    public function update(Request $request, $id)
    {

    }

    public function destroy($id)
    {

    }
}
