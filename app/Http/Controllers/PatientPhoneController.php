<?php

namespace App\Http\Controllers;

use App\Models\PatientPhone;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Exception;
use Illuminate\Http\Request;

class PatientPhoneController extends Controller
{
    protected $permissionService;
    protected $idUserService;
    public function __construct(IdUserService $idUserService,PermissionService $permissionService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
    }

    public function edit(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $this->validate($request, [
                    'phone' => 'required',
                ]);
                $patientPhones = $request->phone;

                $response = [];

                foreach ($patientPhones as $patientPhone) {

                    if ($patientPhone["id_patient_phone"] == "") {
                        $phone = PatientPhone::create([
                            'phone' => $patientPhone["number"],
                            'id_patient' => $request->id_patient
                        ]);
                    } else {
                        $phone = PatientPhone::find($patientPhone["id_patient_phone"]);
                        if ($phone) {
                            $phone->update([
                                'phone' => $patientPhone["number"]
                            ]);
                        }
                    }

                    $response[] = $phone;
                }

                // Utilisation de array_map pour transformer le tableau de réponses
                $formattedResponse = array_map(function ($r) {
                    return [
                        'id_patient_phone' => $r->id_patient_phone,
                        'number' => $r->phone,  // Assurez-vous d'utiliser 'phone' au lieu de 'number' pour correspondre au champ correct
                    ];
                }, $response);

                return response()->json(["phone" => $formattedResponse], 201);

            } else {
                return response()->json(["message" => "You do not have the rights"], 401);
            }
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


}
