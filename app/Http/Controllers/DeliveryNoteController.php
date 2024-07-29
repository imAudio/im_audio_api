<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteDevice;
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

    public function getByDevice($id_device)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $deliveryNoteDevice = DeliveryNoteDevice::where("id_device", $id_device)->first();

                if (!$deliveryNoteDevice) {
                    return response()->json(["message" => "Device not found"], 404);
                }

                $deliveryNote = [
                    "id_delivery_note" => $deliveryNoteDevice->deliveryNote->id_delivery_note,
                    "name" => $deliveryNoteDevice->deliveryNote->name,
                    "manufactured" => $deliveryNoteDevice->deliveryNote->deviceManufactured->content,
                    "number_device" => $deliveryNoteDevice->deliveryNote->number_device,
                    "audio_center" => [
                        "id_audio_center" => $deliveryNoteDevice->deliveryNote->audioCenter->id_audio_center,
                        "name" => $deliveryNoteDevice->deliveryNote->audioCenter->name,
                    ],
                    "worker" => [
                        "id_worker" => $deliveryNoteDevice->deliveryNote->id_worker,
                        "first_name" => $deliveryNoteDevice->deliveryNote->worker->user->firstname,
                        "last_name" => $deliveryNoteDevice->deliveryNote->worker->user->lastname,
                    ]
                ];


                $devices = DeliveryNoteDevice::where("id_delivery_note", $deliveryNoteDevice->deliveryNote->id_delivery_note)->with("device")->get();

                $devicesFormated = $devices->map(function ($device) {
                    return [
                        "id_device" =>   $device->id_device,
                        "serial_number"  => $device->device->serial_number,
                        "content"  => $device->device->deviceModel->content,
                    ];
                });

                return response()->json(["deliveryNote" => $deliveryNote, "devices" => $devicesFormated,],200);

            } else {
                return response()->json(["message" => "You do not have the rights"], 401);
            }

        } catch (Exception $exception) {
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }

}
