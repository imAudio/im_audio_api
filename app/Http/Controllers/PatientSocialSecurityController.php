<?php

namespace App\Http\Controllers;

use App\Models\PatientSocialSecurity;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Exception;
use Illuminate\Http\Request;

class PatientSocialSecurityController extends Controller
{
    protected $permissionService;
    protected $idUserService;
    public function __construct(IdUserService $idUserService,PermissionService $permissionService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
    }

    public function showByPatient($id_patient)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $patientSocialSecurity = PatientSocialSecurity::where("id_patient",$id_patient)->first();

                return response()->json($patientSocialSecurity);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function update(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $patientSocialSecurity = PatientSocialSecurity::where("id_patient", $request->id_patient)->first();

                if ($patientSocialSecurity) {
                    $patientSocialSecurity->update([
                        'social_security_number' => $request->social_security_number,
                        'date_open' => $request->date_open,
                        'date_close' => $request->date_close,
                        'situation' => $request->situation,
                        'special_situation' => $request->special_situation,
                        'cash_register_code' => $request->cash_register_code
                    ]);

                    return response()->json($patientSocialSecurity);
                } else {
                    return response()->json(["message" => "Patient Social Security record not found"], 404);
                }
            } else {
                return response()->json(["message" => "You do not have the rights"], 401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }
    public function destroy($id)
    {

    }
}
