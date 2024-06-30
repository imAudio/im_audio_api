<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use App\Services\IdUserService;
use Illuminate\Http\Request;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Hash;
use mysql_xdevapi\Exception;
use function Laravel\Prompts\error;

class PatientController extends Controller
{
    protected $permissionService;
    protected $idUserService;
    public function __construct(IdUserService $idUserService,PermissionService $permissionService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
    }

    public function create(Request $request)
    {

        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $this->validate($request, [
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'email' => 'required',
                ]);

                $user = User::create([
                    'firstname' => $request->firstName,
                    'lastname' => $request->lastName,
                    'email' => $request->email,
                    'password' => Hash::make(generateRandomPassword()),
                    'id_worker' => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                    ]);


                $patient = Patient::create([
                    'id_user' => $user->id_user,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city' => $request->city,
                    'postal_code' => $request->postalCode,
                    'id_audio_center' => $request->audioCenter,
                    'social_security_number' => $request->socialSecurity,
                    'date_birth' => $request->date,
                ]);
                return response()->json(["message" => "Patien create"],201);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function show($id)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {
                $patientData = Patient::with([
                    'user',
                    'event' => function ($query) {
                        $query->orderBy('start', 'desc');
                    },
                    'event.audioCenter',
                    'event.eventType',
                    'patientNote',
                    'patientNote.worker.user',
                    'setSail.worker',
                    'setSail.dome',
                    'setSail.patient',
                    'setSail.device',
                    'attributMcq',
                    'attributMcq.mcq',
                    'attributMcq.worker',

                ])->find($id);

                if (!$patientData) {
                    return response()->json(["error" => "Patient not found"], 404);
                }

                $creat = $patientData->created_at->format('d/m/Y');

                $patient = [
                    'lastname' => $patientData->user->lastname,
                    'firstname' => $patientData->user->firstname,
                    'email' => $patientData->user->email,
                    'phone' => $patientData->phone,
                    'address' => $patientData->address,
                    'city' => $patientData->city,
                    'postalCode' => $patientData->postal_code,
                    'creat' => $creat,
                    'worker' => [
                        'firstname' => $patientData->user->worker->firstname,
                        'lastname' => $patientData->user->worker->lastname,
                    ]
                ];


                $event = $patientData->event->map(function ($event) {
                    return [
                        'id_event' => $event->id_event,
                        'start' => $event->start,
                        'description' => $event->description,
                        'state' => $event->state,
                        'audioCenter' => $event->audioCenter->name,
                        'eventType' => [
                            'content' => $event->eventType->content,
                            'background_color' => $event->eventType->background_color,
                        ]

                    ];
                });


                $note = $patientData->patientNote->map(function ($note) {
                    $creat = $note->created_at->format('d/m/Y');
                    return [
                        'id_patient_note' => $note->id_patient_note,
                        'content' => $note->content,
                        'creat' => $creat,
                        'worker' => [
                            'firstName' => $note->worker->user->firstname,
                            'lastName' => $note->worker->user->lastname,
                        ]

                    ];
                });

                $device = $patientData->setSail->map(function ($device) {
                    return [
                        'sizeEarpiece' => $device->size_earpiece,
                        'worker' => [
                            'firstName' => $device->worker !== null ? $device->worker->firstname : null,
                            'lastName' => $device->worker !== null ? $device->worker->lastname : null,
                        ],
                        'dome' => [
                            'size' => $device->dome !== null ? $device->dome->size : null,
                            'state' => $device->dome !== null ? $device->dome->state : null,
                        ],
                        'device' => [
                            'serialNumber' => $device->device !== null ? $device->device->serial_number : null,
                            'state' => $device->device !== null ? $device->device->state : null,
                        ],
                        'model' => [
                            'type' => $device->device->deviceModel->deviceType->content,//
                            'content' => $device->device !== null && $device->device->deviceModel !== null ? $device->device->deviceModel->content : null,
                            'state' => $device->device !== null && $device->device->deviceModel !== null ? $device->device->deviceModel->energy : null,
                            'batteryType' => $device->device !== null && $device->device->deviceModel !== null ? $device->device->deviceModel->battery_type : null,
                            'batteryTypeBackgroundColor' => $device->device !== null && $device->device->deviceModel !== null ? $device->device->deviceModel->battery_type_background_color : null,
                        ]
                    ];
                });

                $mcq = $patientData->attributMcq->map(function ($mcq){
                    $creat = $mcq->created_at->format('d/m/Y');
                    return [
                        'state' => $mcq->state,
                        'worker' => [
                            'firstName' => $mcq->worker->user->firstname,
                            'lastName' => $mcq->worker->user->lastname,
                        ],
                        'creat' => $creat,
                        'mcq' => [
                            'content' => $mcq->mcq->content,
                            'type' => $mcq->mcq->type,
                        ]
                    ];
                });


                $result = [
                    'patient' => $patient,
                    'event' => $event,
                    'note' => $note,
                    'device' => $device,
                    'mcq' => $mcq,
                ];

                return response()->json($result);

            } else {
                return response()->json(["message" => "You do not have the rights"], 401);
            }
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {

    }


    public function destroy($id)
    {

    }

    public function autocomplete($query)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if($permissions["isWorker"] == true){
                $users = User::leftJoin('patient', 'user.id_user', '=', 'patient.id_user')
                    ->where(function ($q) use ($query) {
                        $q->where('user.firstname', 'LIKE', '%' . $query . '%')
                            ->orWhere('user.lastname', 'LIKE', '%' . $query . '%')
                            ->orWhere('patient.phone', 'LIKE', '%' . $query . '%');
                    })
                    ->whereNotNull('patient.id_user')
                    ->select('user.*','patient.phone')
                    ->get();



                $formattedPatients = $users->map(function ($user) {
                    return [
                        'label' => $user->firstname . ' ' . $user->lastname . ' - ' . $user->phone,
                        'value' => $user->id_user,
                    ];
                });
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }



            return response()->json($formattedPatients);
        }catch (Exception $exception){
            return response()->json($exception);
        }

    }
}
function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
}
