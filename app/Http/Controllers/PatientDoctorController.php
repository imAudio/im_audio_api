<?php

namespace App\Http\Controllers;

use App\Models\PatientDoctor;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Exception;
use Illuminate\Http\Request;

class PatientDoctorController extends Controller
{
    protected $permissionService;
    protected $idUserService;
    public function __construct(IdUserService $idUserService, PermissionService $permissionService, UserDocumentController $userDocumentController)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
        $this->userDocumentController = $userDocumentController;
    }
    public function index()
    {
    }
    public function create(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $this->validate($request, [
                    "id_user" => "required",
                    "id_doctor" => "required",
                    "date_prescription" => "required",
                ]);

                $doctor = PatientDoctor::create([
                    'id_user' => $request->id_user,
                    'id_doctor' => $request->id_doctor,
                    'date_prescription' => $request->date_prescription,
                    'id_worker' => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                ]);
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
                if ($request->file('document')) {
                    $document = $this->userDocumentController->uploadDocument($request->file('document'), $request->id_user, "ordonnance");
                }

                return response()->json(["message" => "create","doctor" =>$prescription, "document" => $document],201);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }
    public function show($id)
    {

    }
    public function update(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $patientDoctor = PatientDoctor::find($request->id_patient_doctor);

                if (!$patientDoctor){
                    return response()->json(["message" => "PatientDoctor not found"]);
                }

                $patientDoctor->update([
                    'id_user' => $request->id_user,
                    'id_doctor' => $request->id_doctor,
                    'date_prescription' => $request->date_prescription,
                    'id_worker' => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                ]);

                return response()->json($patientDoctor);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }
    public function destroy(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $patientDoctor = PatientDoctor::find($request->id_patient_doctor);
                if (!$patientDoctor){
                    return response()->json(["message" => "Doctor not found"]);
                }
                $patientDoctor->delete();

                return response()->json(["message" => "Delete success"]);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }
}
