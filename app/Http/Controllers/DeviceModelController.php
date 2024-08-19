<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceModel;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function autocompleteByManufacured($id_device_manufacturer,$query)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $deviceModel = DeviceModel::where("id_device_manufactured",$id_device_manufacturer)
                    ->where("content","LIKE", '%' . $query . '%')
                    ->get();

                $data = $deviceModel->map(function ($model) {
                   return [
                       "label" => $model->content,
                       "value" => $model->id_device_model
                   ];
                });

                return response()->json($data);
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

    public function informationStorage(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $idDeviceManufacturer = $request->query('id-device-manufacturer');
                $idAudioCenter = $request->query('id-audio-center');

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
                    ->leftJoin('device_transfer as dt2', function ($join) use ($idAudioCenter) {
                        $join->on('device.id_device', '=', 'dt2.id_device')
                            ->on('dt2.created_at', '=', 'latest_transfer.latest_created_at')
                            ->where('dt2.id_audio_center', '=', $idAudioCenter);
                    })
                    ->leftJoin('device_model', 'device.id_device_model', '=', 'device_model.id_device_model')
                    ->where('device_model.id_device_manufactured', '=', $idDeviceManufacturer)
                    ->whereNotNull('ds2.id_device')
                    ->whereNotNull('dt2.id_device')
                    ->with(['deviceModel.deviceManufactured'])
                    ->select('device.*', 'device_model.id_device_model', 'ds2.state as latest_state', 'ds2.created_at as latest_state_created_at', 'dt2.id_audio_center as latest_transfer_audio_center', 'dt2.created_at as latest_transfer_created_at')
                    ->orderBy('ds2.created_at', 'desc')
                    ->get();

                // Récupérer tous les modèles pour le fabricant spécifié
                $allModels = DB::table('device_model')
                    ->where('id_device_manufactured', '=', $idDeviceManufacturer)
                    ->get()
                    ->keyBy('content');

                // Grouper les appareils par fabricant et modèle
                $groupedDevices = $devices->groupBy(function ($device) {
                    return $device->deviceModel->deviceManufactured->content; // Group by manufacturer
                });

                $data = $groupedDevices->map(function ($devices, $manufacturer) use ($allModels) {
                    $models = $devices->groupBy(function ($device) {
                        return $device->deviceModel->content; // Group by model
                    })->map(function ($devices, $model) {
                        $stateCounts = [
                            'stock' => 0,
                            'essai' => 0,
                            'sav' => 0,
                        ];

                        $idDeviceModel = $devices->first()->id_device_model;

                        foreach ($devices as $device) {
                            $state = strtolower($device->latest_state);
                            if (isset($stateCounts[$state])) {
                                $stateCounts[$state]++;
                            } else {
                                $stateCounts[$state] = 1;
                            }
                        }

                        return [
                            'id' => $idDeviceModel,
                            'name' => $model,
                            'device_count' => count($devices),
                            'devices' => $stateCounts
                        ];
                    });

                    foreach ($allModels as $model => $details) {
                        if (!$models->has($model)) {
                            $models[$model] = [
                                'id' => $details->id_device_model,
                                'name' => $model,
                                'device_count' => 0,
                                'devices' => [
                                    'stock' => 0,
                                    'essai' => 0,
                                    'sav' => 0,
                                ]
                            ];
                        }
                    }

                    return [
                        'manufacturer' => $manufacturer,
                        'models' => $models->values()
                    ];
                });

                return response()->json($data->values());
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
