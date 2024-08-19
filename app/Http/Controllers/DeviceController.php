<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteDevice;
use App\Models\Device;
use App\Models\DeviceState;
use App\Models\DeviceTransfer;
use App\Models\Patient;
use App\Models\SetSail;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function getByStateAudioCenter($state, $idAudioCenter)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                // Sous-requête pour les derniers états des appareils
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
                    ->leftJoin('device_state as ds2', function ($join) use ($state) {
                        $join->on('device.id_device', '=', 'ds2.id_device')
                            ->on('ds2.created_at', '=', 'latest_state.latest_created_at')
                            ->where('ds2.state', '=', $state);
                    })
                    ->leftJoinSub($transferSubQuery, 'latest_transfer', function ($join) {
                        $join->on('device.id_device', '=', 'latest_transfer.id_device');
                    })
                    ->leftJoin('device_transfer as dt2', function ($join) use ($idAudioCenter) {
                        $join->on('device.id_device', '=', 'dt2.id_device')
                            ->on('dt2.created_at', '=', 'latest_transfer.latest_created_at')
                            ->where('dt2.id_audio_center', '=', $idAudioCenter);
                    })
                    ->whereNotNull('ds2.id_device')
                    ->whereNotNull('dt2.id_device')
                    ->with(['deviceState', 'deviceTransfer'])
                    ->select('device.*', 'ds2.state as latest_state', 'ds2.created_at as latest_state_created_at', 'dt2.id_audio_center as latest_transfer_audio_center', 'dt2.created_at as latest_transfer_created_at')

                    ->orderBy('ds2.created_at', 'desc')

                    ->get();


                $data = $devices->map(function ($device) {
                    return [
                        "id_device" => $device->id_device,
                        "serial_number" => $device->serial_number,
                        "state" => $device->deviceState->first()->state,
                        "date_state" => $device->deviceState->first()->created_at->toDateString(),
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
            Log::error('Error in getByStateAudioCenter:', ['exception' => $exception]);
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }

    public function getByModelStateAudioCenter(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $state = $request->query('state');
                $idAudioCenter = $request->query('id-audio-center');
                $idDeviceModel = $request->query('id-device-model');

                // Sous-requête pour les derniers états des appareils
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
                    ->leftJoin('device_state as ds2', function ($join) use ($state) {
                        $join->on('device.id_device', '=', 'ds2.id_device')
                            ->on('ds2.created_at', '=', 'latest_state.latest_created_at')
                            ->where('ds2.state', '=', $state);
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
                    ->where('device.id_device_model', '=', $idDeviceModel)
                    ->whereNotNull('ds2.id_device')
                    ->whereNotNull('dt2.id_device')
                    ->with(['deviceState', 'deviceTransfer'])
                    ->select('device.*', 'ds2.state as latest_state', 'ds2.created_at as latest_state_created_at', 'dt2.id_audio_center as latest_transfer_audio_center', 'dt2.created_at as latest_transfer_created_at')
                    ->orderBy('ds2.created_at', 'desc')
                    ->get();

                $data = $devices->map(function ($device) {
                    return [
                        "id_device" => $device->id_device,
                        "serial_number" => $device->serial_number,
                        "state" => $device->deviceState->first()->state,
                        "date_state" => $device->deviceState->first()->created_at->toDateString(),
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
            Log::error('Error in getByStateAudioCenter:', ['exception' => $exception]);
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }



    public function create(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $devices = $request->devices;
                $createdDevices = [];

                $id_worker = $this->idUserService->getAuthenticatedIdUser()['id_user'];

                $deliveryNote = DeliveryNote::create([
                    "name" => $request->name,
                    "number_device" => $request->number_device,
                    "id_audio_center" => $request->id_audio_center,
                    "id_worker" => $id_worker,
                    "id_device_manufactured" => $request->id_device_manufactured,
                ]);

                foreach ($devices as $device) {

                    $device['id_worker'] = $id_worker;

                    $createdDevice = Device::create($device);
                    $createdDevices[] = $createdDevice;

                    DeviceState::create([
                        "id_device" => $createdDevice->id_device,
                        "state" => "Stock",
                        "id_worker" =>  $createdDevice->id_worker,
                    ]);

                    DeviceTransfer::create([
                        "id_device" => $createdDevice->id_device,
                        "id_audio_center" => $device["id_audio_center"],
                        "id_worker" =>  $createdDevice->id_worker,
                    ]);

                    DeliveryNoteDevice::create([
                        "id_delivery_note" => $deliveryNote->id_delivery_note,
                        "id_device" => $createdDevice->id_device,
                        "id_worker" => $id_worker,
                    ]);
                }

                return response()->json(["message" => "Devices created successfully", "devices" => $createdDevices], 201);
            }

            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (\Exception $exception) {
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }

    public function getHistoryState($id_device)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $historyState = DeviceState::where("id_device",$id_device)->get();

                $data = $historyState->map(function ($state){
                    $creat = $state->created_at->format('d/m/Y');
                    return [
                        "state" => $state->state,
                        "worker" => [
                            "firstName" => $state->worker !== null ? $state->worker->firstname : null,
                            "lastName" => $state->worker !== null ? $state->worker->lastname : null,
                        ],
                        "created_at" => $creat
                    ];
                });
                return response()->json($data);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }

    public function getHistoryTransfer($id_device)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $historyTransfer = DeviceTransfer::where("id_device",$id_device)->get();

                $data = $historyTransfer->map(function ($transfer){
                    $creat = $transfer->created_at->format('d/m/Y');
                    return [
                        "audio_center" => $transfer->audioCenter->name,
                        "worker" => [
                            "firstName" => $transfer->worker !== null ? $transfer->worker->firstname : null,
                            "lastName" => $transfer->worker !== null ? $transfer->worker->lastname : null,
                        ],
                        "created_at" => $creat
                    ];
                });
                return response()->json($data);
            }

            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (\Exception $exception) {
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }


    public function show($id_device)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {
                $stateSubQuery = DB::table('device_state as ds1')
                    ->select('ds1.id_device', DB::raw('MAX(ds1.created_at) as latest_created_at'))
                    ->where('ds1.id_device', $id_device)
                    ->groupBy('ds1.id_device');

                $transferSubQuery = DB::table('device_transfer as dt1')
                    ->select('dt1.id_device', DB::raw('MAX(dt1.created_at) as latest_created_at'))
                    ->where('dt1.id_device', $id_device)
                    ->groupBy('dt1.id_device');

                $device = Device::joinSub($stateSubQuery, 'latest_state', function ($join) {
                    $join->on('device.id_device', '=', 'latest_state.id_device');
                })
                    ->join('device_state as ds2', function ($join) {
                        $join->on('device.id_device', '=', 'ds2.id_device')
                            ->on('ds2.created_at', '=', 'latest_state.latest_created_at');
                    })
                    ->joinSub($transferSubQuery, 'latest_transfer', function ($join) {
                        $join->on('device.id_device', '=', 'latest_transfer.id_device');
                    })
                    ->join('device_transfer as dt2', function ($join) {
                        $join->on('device.id_device', '=', 'dt2.id_device')
                            ->on('dt2.created_at', '=', 'latest_transfer.latest_created_at');
                    })
                    ->with([
                        'deviceState' => function($query) {
                            $query->orderByDesc('created_at')->limit(1);
                        },
                        'deviceTransfer' => function($query) {
                            $query->orderByDesc('created_at')->limit(1);
                        },
                        'deviceModel.deviceType',
                        'deviceModel.deviceManufactured'
                    ])
                    ->where('device.id_device', $id_device)
                    ->first();

                if ($device) {
                    $data = [
                        "id_device" => $device->id_device,
                        "serial_number" => $device->serial_number,
                        "state" => $device->deviceState->first()->state ?? null,
                        "date_state" => $device->deviceState->first()->created_at ?? null,
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
                        "latest_transfer" => [
                            "id_audio_center" => $device->deviceTransfer->first()->id_audio_center ?? null,
                            "date_transfer" => $device->deviceTransfer->first()->created_at ?? null,
                        ]
                    ];

                    return response()->json($data);
                } else {
                    return response()->json(["message" => "Device not found"], 404);
                }
            }

            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (\Exception $exception) {
            return response()->json(["message" => $exception->getMessage()], 500);
        }
    }
    public function getStateDevice($id_device)
    {
        $stateSubQuery = DB::table('device_state as ds1')
            ->select('ds1.id_device', DB::raw('MAX(ds1.created_at) as latest_created_at'))
            ->where('ds1.id_device', $id_device)
            ->groupBy('ds1.id_device');

        $transferSubQuery = DB::table('device_transfer as dt1')
            ->select('dt1.id_device', DB::raw('MAX(dt1.created_at) as latest_created_at'))
            ->where('dt1.id_device', $id_device)
            ->groupBy('dt1.id_device');

        $device = Device::joinSub($stateSubQuery, 'latest_state', function ($join) {
            $join->on('device.id_device', '=', 'latest_state.id_device');
        })
            ->join('device_state as ds2', function ($join) {
                $join->on('device.id_device', '=', 'ds2.id_device')
                    ->on('ds2.created_at', '=', 'latest_state.latest_created_at');
            })
            ->joinSub($transferSubQuery, 'latest_transfer', function ($join) {
                $join->on('device.id_device', '=', 'latest_transfer.id_device');
            })
            ->join('device_transfer as dt2', function ($join) {
                $join->on('device.id_device', '=', 'dt2.id_device')
                    ->on('dt2.created_at', '=', 'latest_transfer.latest_created_at');
            })
            ->with([
                'deviceState' => function($query) {
                    $query->orderByDesc('created_at')->limit(1);
                },
                'deviceTransfer' => function($query) {
                    $query->orderByDesc('created_at')->limit(1);
                },
                'deviceModel.deviceType',
                'deviceModel.deviceManufactured'
            ])
            ->where('device.id_device', $id_device)
            ->first();

        if ($device) {
            $state = $device->deviceState->first()->state;
        }

        return $state;
    }

    public function update(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {


                $device = Device::find($request->id_device);

                if (!$device) {
                    return response()->json(["message" => "Device not found"], 404);
                }

                $device->update([
                    'serial_number' => $request->serial_number,
                    'id_device_color' => $request->id_device_color,
                    'id_device_model' => $request->id_device_model,
                    'id_worker' => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                ]);

                return response()->json(["message" => "Device updated"], 200);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function destroy($id)
    {

    }

    public function editState(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $devices = $request->all();

                foreach ($devices as $device) {
                    $device['id_worker'] = $this->idUserService->getAuthenticatedIdUser()['id_user'];
                    DeviceState::create($device);
                }

                return response()->json(["message" => "Devices edit state successfully"]);
            }

            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (\Exception $exception) {
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }

    public function transfer(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $devices = $request->all();

                foreach ($devices as $device) {
                    $device['id_worker'] = $this->idUserService->getAuthenticatedIdUser()['id_user'];

                    DeviceTransfer::create($device);
                }

                return response()->json(["message" => "Devices edit transfer successfully"]);
            }

            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (\Exception $exception) {
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }

    public function autocomplete($query, $id_audio_center, $type)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                // Sous-requête pour obtenir le dernier état de chaque appareil
                $stateSubQuery = DB::table('device_state as ds1')
                    ->select('ds1.id_device', DB::raw('MAX(ds1.created_at) as latest_created_at'))
                    ->groupBy('ds1.id_device');

                // Sous-requête pour obtenir le dernier transfert de chaque appareil
                $transferSubQuery = DB::table('device_transfer as dt1')
                    ->select('dt1.id_device', DB::raw('MAX(dt1.created_at) as latest_created_at'))
                    ->groupBy('dt1.id_device');

                // Requête principale pour joindre les appareils avec leur dernier état et transfert
                $devicesQuery = Device::joinSub($stateSubQuery, 'latest_state', function ($join) {
                    $join->on('device.id_device', '=', 'latest_state.id_device');
                })
                    ->join('device_state as ds2', function ($join) {
                        $join->on('device.id_device', '=', 'ds2.id_device')
                            ->on('ds2.created_at', '=', 'latest_state.latest_created_at');
                    })
                    ->joinSub($transferSubQuery, 'latest_transfer', function ($join) {
                        $join->on('device.id_device', '=', 'latest_transfer.id_device');
                    })
                    ->join('device_transfer as dt2', function ($join) use ($id_audio_center) {
                        $join->on('device.id_device', '=', 'dt2.id_device')
                            ->on('dt2.created_at', '=', 'latest_transfer.latest_created_at')
                            ->where('dt2.id_audio_center', '=', $id_audio_center); // Filtrer par id_audio_center
                    })
                    ->where('ds2.state', 'Stock') // Filtrer par le dernier état "Stock"
                    ->where('device.serial_number', 'LIKE', '%' . $query . '%');

                // Ajouter conditionnellement la relation deviceModel.deviceType pour filtrer les résultats
                if ($type == 1) {
                    $devicesQuery = $devicesQuery->whereHas('deviceModel.deviceType', function ($query) {
                        $query->where('id_device_type', 1);
                    });
                } else {
                    $devicesQuery = $devicesQuery->whereHas('deviceModel.deviceType', function ($query) {
                        $query->where('id_device_type', '!=', 1);
                    });
                }

                // Charger les relations avec `with`
                $devicesQuery = $devicesQuery->with([
                    'deviceState' => function ($query) {
                        $query->orderByDesc('created_at')->limit(1);
                    },
                    'deviceModel.deviceManufactured'
                ]);

                $devices = $devicesQuery->get();


                $formattedDevices = $devices->map(function ($device) {
                    return [
                        'label' => $device->serial_number . " " . $device->deviceModel->content,
                        'value' => $device->id_device,
                    ];
                });

                return response()->json($formattedDevices);
            } else {
                return response()->json(["message" => "You do not have the rights"], 401);
            }

        } catch (Exception $exception) {
            return response()->json($exception);
        }
    }

    public function deviceByIdPatient(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {
                $devicePatient = Patient::with('setSail.device.deviceModel.deviceManufactured', 'setSail.device.deviceColor')->find($request->query('id-patient'));

                if (!$devicePatient) {
                    return response()->json(["error" => "Patient not found"], 404);
                }

                $deviceData = $this->processDeviceInfo($devicePatient->setSail);

                return response()->json($deviceData);

                return response()->json();
            } else {
                return response()->json(["message" => "You do not have the rights"], 401);
            }
        }catch (Exception $exception) {
            return response()->json($exception);
        }
    }

    public function processDeviceInfo($devices)
    {
        $leftDevice = [];
        $rightDevice = [];
        $otherDevice = [];

        foreach ($devices as $device) {
            $newDevice = [
                'sizeEarpiece' => $device->size_earpiece,
                'side' => $device->side,
                'worker' => [
                    'firstName' => $device->worker !== null ? $device->worker->firstname : null,
                    'lastName' => $device->worker !== null ? $device->worker->lastname : null,
                ],
                'dome' => [
                    'size' => $device->dome !== null ? $device->dome->size : null,
                    'state' => $device->dome !== null ? $device->dome->state : null,
                ],
                'device' => [
                    'id_device' => $device->device->id_device,
                    'serialNumber' => $device->device !== null ? $device->device->serial_number : null,
                    'state' => $this->getStateDevice($device->device->id_device),
                ],
                'model' => [
                    'type' => $device->device->deviceModel->deviceType->content,
                    'content' => $device->device !== null && $device->device->deviceModel !== null ? $device->device->deviceModel->content : null,
                    'state' => $device->device !== null && $device->device->deviceModel !== null ? $device->device->deviceModel->energy : null,
                    'batteryType' => $device->device !== null && $device->device->deviceModel !== null ? $device->device->deviceModel->battery_type : null,
                    'batteryTypeBackgroundColor' => $device->device !== null && $device->device->deviceModel !== null ? $device->device->deviceModel->battery_type_background_color : null,
                ],
                'info_model' => [
                    'manufactured' => $device->device->deviceModel->deviceManufactured->content,
                    'color' => $device->device->deviceColor->content,
                ],
                'created_at' => $device->created_at
            ];
            if ($device->side == "left") {
                $leftDevice[] = $newDevice;
            } else if ($device->side == "right") {
                $rightDevice[] = $newDevice;
            } else {
                $otherDevice[] = $newDevice;
            }
        }
        return [
            "left" => $leftDevice,
            "right" => $rightDevice,
            "other" => $otherDevice,
        ];
    }
}
