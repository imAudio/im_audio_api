<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceState;
use App\Models\SetSail;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class SetSailController
{
    protected $permissionService;
    protected $idUserService;

    public function __construct(IdUserService $idUserService, PermissionService $permissionService,DeviceController $deviceController)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
        $this->deviceController = $deviceController;
    }

    public function create(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {
                $setSail = SetSail::create([
	                "id_patient" => $request->id_patient,
	                "id_device" => $request->id_device,
                    "id_worker" => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                    "side" => $request->side,
                ]);
                DeviceState::create([
                    "id_device" => $request->id_device,
                    "state" => "Essai",
                    "id_worker" => $this->idUserService->getAuthenticatedIdUser()['id_user']
                ]);

                $device = Device::with([
                    'setSail.worker',
                    'setSail.dome',
                    'setSail.patient',
                    'setSail.device',
                ])->find($request->id_device);



                $newDevice = [
                    'sizeEarpiece' => $device->setSail->size_earpiece !== null ? $device->setSail->size_earpiece : null,
                    'side' => $device->setSail->side !== null ? $device->setSail->side : null,
                    'worker' => [
                        'firstName' => $device->setSail->worker !== null ? $device->setSail->worker->firstname : null,
                        'lastName' => $device->setSail->worker !== null ? $device->setSail->worker->lastname : null,
                    ],
                    'dome' => [
                        'size' => $device->setSail->dome !== null ? $device->setSail->dome->size : null,
                        'state' => $device->setSail->dome !== null ? $device->setSail->dome->state : null,
                    ],
                    'device' => [
                        'id_device' => $device->id_device,
                        'serialNumber' =>$device->serial_number,
                        'state' => $this->deviceController->getStateDevice($device->id_device),
                    ],
                    'model' => [
                        'type' => $device->deviceModel->deviceType->content,
                        'content' => $device !== null && $device->deviceModel !== null ? $device->deviceModel->content : null,
                        'state' => $device !== null && $device->deviceModel !== null ? $device->deviceModel->energy : null,
                        'batteryType' => $device !== null && $device->deviceModel !== null ? $device->deviceModel->battery_type : null,
                        'batteryTypeBackgroundColor' => $device !== null && $device->deviceModel !== null ? $device->deviceModel->battery_type_background_color : null,
                    ],
                    'info_model' => [
                        'manufactured' => $device->deviceModel->deviceManufactured->content,
                        'color' => $device->deviceColor->content,
                    ],
                    'created_at' => $device->created_at
                ];

                return response()->json($newDevice,201);
            }

            return response()->json(["message" => "You do not have the rights"], 401);

        }catch (\Exception $exception){
            return response()->json($exception);
        }
    }


}
