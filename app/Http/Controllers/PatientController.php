<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientPhone;
use App\Models\PatientSocialSecurity;
use App\Models\User;
use App\Services\IdUserService;
use App\Http\Controllers\DeviceController;
use Illuminate\Http\Request;
use App\Services\PermissionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use mysql_xdevapi\Exception;
use function Laravel\Prompts\error;
use function PHPUnit\Framework\isEmpty;


class PatientController extends Controller
{
    protected $permissionService;
    protected $idUserService;
    public function __construct(IdUserService $idUserService,PermissionService $permissionService, DeviceController $deviceController)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
        $this->deviceController = $deviceController;
    }

    public function create(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if($permissions["isWorker"] == true){

                $this->validate($request, [
                    'firstName' => 'required',
                    'lastName' => 'required',
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
                    'address' => $request->address,
                    'city' => $request->city,
                    'postal_code' => $request->postalCode,
                    'id_audio_center' => $request->audioCenter,
                    'date_birth' => $request->date,
                ]);

                if ($request->phone){
                    $phone = PatientPhone::create([
                        'phone' => $request->phone,
                        'id_patient' => $user->id_user
                    ]);
                }

                //PatientSocialSecurity::create([
                //    'id_patient' => $user->id_user,
                //    'social_security_number' => $request->social_security_number,
                //]);

                $data = [
                    "label" => $user->firstname . ' ' .   $user->lastname,
		            "value" => $user->id_user
                ];

                return response()->json(["message" => "Patien create","id_user" => $user->id_user,"data" => $data],201);
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
                    'patientPhone',
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
                    'attributMcq' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    },
                    'attributMcq.mcq',
                    'attributMcq.worker',
                    'patientDoctor',
                    'patientSocialSecurity'

                ])->find($id);

                if (!$patientData) {
                    return response()->json(["error" => "Patient not found"], 404);
                }

                $creat = $patientData->created_at->format('d/m/Y');
                if($patientData->date_birth !== null){
                    $patientBirthDate = Carbon::parse($patientData->date_birth);
                    $currentDate = Carbon::now();
                    $age = $currentDate->diffInYears($patientBirthDate);
                }else{
                    $age = null;
                }



                $patient = [
                    'lastName' => $patientData->user->lastname,
                    'firstName' => $patientData->user->firstname,
                    'email' => $patientData->user->email,
                    'address' => $patientData->address,
                    'city' => $patientData->city,
                    'postalCode' => $patientData->postal_code,
                    'date_birth' => $patientData->date_birth,
                    'age' => $age,
                    'gender' => $patientData->gender,
                    'creat' => $creat,
                    'worker' => [
                        'firstName' => $patientData->user->worker->firstname,
                        'lastName' => $patientData->user->worker->lastname,
                    ]
                ];
                $phone  = $patientData->patientPhone->map(function ($phone) {
                    return [
                        'id_patient_phone' => $phone->id_patient_phone,
                        'number' => $phone->phone
                    ];
                });

                $pastEvent = [];
                $futureEvent = [];

                $dataEvent = $patientData->event->map(function ($event) use (&$pastEvent, &$futureEvent) {
                    $creat = $event->created_at->format('d/m/Y');
                    $e = [
                        'id_event' => $event->id_event,
                        'start' => $event->start,
                        'description' => $event->description,
                        'state' => $event->state,
                        'audioCenter' => $event->audioCenter->name,
                        'eventType' => [
                            'content' => $event->eventType->content,
                            'background_color' => $event->eventType->background_color,
                        ],
                        'creat' => $creat,
                        'worker' => [
                            'id_worker' =>  $event->worker->id_user,
                            'firstName' => $event->worker->user->firstname,
                            'lastName' => $event->worker->user->lastname,
                        ]
                    ];

                    // Comparer la date de l'événement avec la date actuelle
                    if (Carbon::parse($event->start)->isPast()) {
                        $pastEvent[] = $e;
                    } else {
                        $futureEvent[] = $e;
                    }
                });

                $pastEvent = collect($pastEvent)->sortByDesc('start')->values()->all();

                $futureEvent = collect($futureEvent)->sortByDesc('start')->values()->all();

                $event = [
                    'past_events' => $pastEvent,
                    'future_events' => $futureEvent,
                ];



                $note = $patientData->patientNote->map(function ($note) {
                    $creat = $note->created_at->format('d/m/Y');
                    return [
                        'id_patient_note' => $note->id_patient_note,
                        'content' => nl2br($note->content),
                        'creat' => $creat,
                        'worker' => [
                            'id_worker' =>  $note->worker->id_user,
                            'firstName' => $note->worker->user->firstname,
                            'lastName' => $note->worker->user->lastname,
                        ]

                    ];
                });

                $device = $this->deviceController->processDeviceInfo($patientData->setSail);


                $mcq = $patientData->attributMcq->map(function ($mcq){
                    $creat = $mcq->created_at->format('d/m/Y');
                    return [
                        'id_mcq' => $mcq->id_mcq,
                        'state' => $mcq->state,
                        'worker' => [
                            'firstName' => $mcq->worker->user->firstname,
                            'lastName' => $mcq->worker->user->lastname,
                        ],
                        'creat' => $creat,
                        'created_at' => $mcq->created_at,
                        'mcq' => [
                            'content' => $mcq->mcq->content,
                            'type' => $mcq->mcq->type,
                        ]
                    ];
                });

                if (!empty($patientData->patientDoctor[0])){
                    $doctor =$patientData->patientDoctor[0] ;

                    $creat = $doctor->created_at->format('d/m/Y');
                    $prescription = [
                        'id_patient_doctor' => $doctor->id_patient_doctor,
                        'creat' => $creat,
                        'date_prescription' => $doctor->date_prescription,
                        'doctor' => [
                            'id_doctor' => $doctor->id_doctor,
                            'name' => $doctor->doctor->name,
                            'finess' => $doctor->doctor->finess,
                            'rpps' => $doctor->doctor->rpps,
                            'type' => $doctor->doctor->type,
                        ]
                    ];
                }else{
                    $prescription=[];
                }
                if (!empty($patientData->patientSocialSecurity[0])){
                    $social =$patientData->patientSocialSecurity[0] ;

                    $socialSecurity = [
                        'social_security_number' => $social->social_security_number,
                        'date_open' => $social->date_open,
                        'date_close' => $social->date_close,
                        'situation' => $social->situation,
                        'special_situation' => $social->special_situation,
                        'cash_register_code' => $social->cash_register_code,
                    ];
                }else{
                    $socialSecurity=[];
                }

                $result = [
                    'patient' => $patient,
                    'phone' => $phone,
                    'event' => $event,
                    'note' => $note,
                    'device' => $device,
                    'mcq' => $mcq,
                    'prescription' => $prescription,
                    'social_security' => $socialSecurity
                ];

                return response()->json($result);

            } else {
                return response()->json(["message" => "You do not have the rights"], 401);
            }
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $patient = Patient::with([
                    'user',
                ])->find($request->id_patient);

                $patient->user->update([
                    'lastname' => $request->lastName,
                    'firstname' => $request->firstName,
                    'email' => $request->email,
                ]);

                $patient->update([
                    'date_birth' => $request->date_birth,
                    'address' => $request->address,
                    'postal_code' => $request->postalCode,
                    'city' => $request->city,
                    'gender' => $request->gender,
                ]);

                if (!$patient) {
                    return response()->json(["error" => "Patient not found"], 404);
                }

                $creat = $patient->created_at->format('d/m/Y');
                if($patient->date_birth !== null){
                    $patientBirthDate = Carbon::parse($patient->date_birth);
                    $currentDate = Carbon::now();
                    $age = $currentDate->diffInYears($patientBirthDate);
                }else{
                    $age = null;
                }

                $patient = [
                    'lastName' => $patient->user->lastname,
                    'firstName' => $patient->user->firstname,
                    'email' => $patient->user->email,
                    'address' => $patient->address,
                    'city' => $patient->city,
                    'postalCode' => $patient->postal_code,
                    'date_birth' => $patient->date_birth,
                    'gender' => $patient->gender,
                    'creat' => $creat,
                    'worker' => [
                        'firstName' => $patient->user->worker->firstname,
                        'lastName' => $patient->user->worker->lastname,
                    ]
                ];

                return response()->json(["patientInfo" =>$patient],201);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function destroy($id)
    {

    }

    public function autocomplete($query)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {
                $users = User::leftJoin('patient', 'user.id_user', '=', 'patient.id_user')
                    ->leftJoin('patient_phone', 'patient.id_user', '=', 'patient_phone.id_patient')
                    ->where(function ($q) use ($query) {
                        $q->where('user.firstname', 'LIKE', '%' . $query . '%')
                            ->orWhere('user.lastname', 'LIKE', '%' . $query . '%')
                            ->orWhere('patient_phone.phone', 'LIKE', '%' . $query . '%');
                    })
                    ->whereNotNull('patient.id_user')
                    ->select('user.*', DB::raw('GROUP_CONCAT(patient_phone.phone SEPARATOR ", ") as phones'))
                    ->groupBy('user.id_user')
                    ->get();

                $formattedPatients = $users->map(function ($user) {
                    return [
                        'label' => $user->firstname . ' ' . $user->lastname . ' - ' . $user->phones,
                        'value' => $user->id_user,
                    ];
                });
            } else {
                return response()->json(["message" => "You do not have the rights"], 401);
            }

            return response()->json($formattedPatients);
        } catch (Exception $exception) {
            return response()->json($exception);
        }
    }

    public function edit(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {
                $patient = Patient::find($request->id_patient);
                $label = $request->input('label');
                $patient->$label = $request->value;
                $patient->save();
            } else {
                return response()->json(["message" => "You do not have the rights"], 401);
            }

            return response()->json(["social_security_number" =>$patient->social_security_number]);
        } catch (Exception $exception) {
            return response()->json($exception);
        }
    }

    public function routeTest()
    {
        try {
            $permission = $this->permissionService->getPermissions();

            if ($permission["isWorker"] == true){

                    $patient = Patient::with([
                        'user'
                    ])
                    ->find(69);

                return response()->json($patient);
            }else{
                return response()->json(["message" => "You do not have the rights"], 401);
            }

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
