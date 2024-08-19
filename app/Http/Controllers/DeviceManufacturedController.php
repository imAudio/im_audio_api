<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceManufactured;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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



    public function informationStorage($id_audio_center)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                // Sous-requête pour obtenir le dernier état de chaque appareil
                $stateSubQuery = DB::table('device_state as ds1')
                    ->select('ds1.id_device', DB::raw('MAX(ds1.created_at) as latest_created_at'))
                    ->groupBy('ds1.id_device');

                // Sous-requête pour les derniers transferts des appareils
                $transferSubQuery = DB::table('device_transfer as dt1')
                    ->select('dt1.id_device', DB::raw('MAX(dt1.created_at) as latest_created_at'))
                    ->groupBy('dt1.id_device');

                // Requête principale
                $devices = Device::leftJoinSub($stateSubQuery, 'latest_state', function ($join) {
                    $join->on('device.id_device', '=', 'latest_state.id_device');
                })
                    ->leftJoin('device_state as ds2', function ($join) {
                        $join->on('device.id_device', '=', 'ds2.id_device')
                            ->on('ds2.created_at', '=', 'latest_state.latest_created_at')
                            ->whereIn('ds2.state', ['Stock', 'Essai', 'SAV']); // Condition pour les états
                    })
                    ->leftJoinSub($transferSubQuery, 'latest_transfer', function ($join) {
                        $join->on('device.id_device', '=', 'latest_transfer.id_device');
                    })
                    ->leftJoin('device_transfer as dt2', function ($join) use ($id_audio_center) {
                        $join->on('device.id_device', '=', 'dt2.id_device')
                            ->on('dt2.created_at', '=', 'latest_transfer.latest_created_at')
                            ->where('dt2.id_audio_center', '=', $id_audio_center);
                    })
                    ->whereNotNull('ds2.id_device')
                    ->whereNotNull('dt2.id_device')
                    ->with(['deviceModel.deviceManufactured'])
                    ->select('device.*', 'ds2.state as latest_state', 'ds2.created_at as latest_state_created_at', 'dt2.id_audio_center as latest_transfer_audio_center', 'dt2.created_at as latest_transfer_created_at')
                    ->orderBy('ds2.created_at', 'desc')
                    ->get();

                // Grouper les appareils par fabricant
                $groupedDevices = $devices->groupBy(function ($device) {
                    return $device->deviceModel->deviceManufactured->id_device_manufactured;
                });

                // Préparer les données de sortie avec le nombre de dispositifs dans chaque groupe et leurs états
                $data = $groupedDevices->map(function ($devices, $id_device_manufacturer) {
                    $stateCounts = [
                        'stock' => 0,
                        'essai' => 0,
                        'sav' => 0,
                    ];

                    foreach ($devices as $device) {
                        $state = strtolower($device->latest_state);
                        if (isset($stateCounts[$state])) {
                            $stateCounts[$state]++;
                        } else {
                            $stateCounts[$state] = 1;
                        }
                    }

                    $manufacturerName = $devices->first()->deviceModel->deviceManufactured->content;

                    return [
                        'id' => $id_device_manufacturer,
                        'name' => $manufacturerName,
                        'device_count' => count($devices),
                        'devices' => $stateCounts
                    ];
                });

                return response()->json($data->values()); // Utilisation de `values` pour réindexer les clés
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
            return response()->json($exception, 500);
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

                $deviceManufacturer = DeviceManufactured::create([
                    "content" => $request->name,
                    "address" => $request->address,
                    "city" => $request->city,
                    "postal_code" => $request->postal_code,
                    "id_master_audio" => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                ]);

                return response()->json(["message" => "DeviceManufacturer create",'deviceManufacturer' =>$deviceManufacturer ],201);
            }
            return response()->json(["message" => "You do not have the rights"],401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }



    public function show(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {
                $idDeviceManufactured = $request->query('id-device-manufacturer');

                $deviceManufacturer = DeviceManufactured::find($idDeviceManufactured);

                $data = [
                    "name" => $deviceManufacturer->content,
                    "address" => $deviceManufacturer->address,
                    "city" => $deviceManufacturer->city,
                    "postal_code" =>$deviceManufacturer->postal_code,
                    "master_audio" => [
                        "id_user" => $deviceManufacturer->id_master_audio,
                        "firstName" => $deviceManufacturer->masterAudio->worker->user->firstname,
                        "lastName" => $deviceManufacturer->masterAudio->worker->user->lastname,
                    ]
                ];
                return response()->json([$data ]);
            }
            return response()->json(["message" => "You do not have the rights"],401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isMasterAudio"] == true) {
                $this->validate($request, [
                    "name" => "required",
                    "address" => "required",
                    "city" => "required",
                    "postal_code" => "required",
                ]);

                $deviceManufacturer = DeviceManufactured::find($request->id_device_manufacturer);

                if (!$deviceManufacturer) {
                    return response()->json(["message" => "Device manufacturer not found"], 404);
                }

                $deviceManufacturer->update([
                    'content' => $request->name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'postal_code' => $request->postal_code,
                ]);

                return response()->json(["message" => "Device manufacturer updated"], 200);
                return response()->json([],201);
            }
            return response()->json(["message" => "You do not have the rights"],401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function destroy($id)
    {
        //
    }
}
