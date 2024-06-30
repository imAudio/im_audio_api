<?php

namespace App\Http\Controllers;

use App\Models\PatientNote;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use mysql_xdevapi\Exception;

class PatientNoteController extends Controller
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
                    'content' => 'required',
                    'id_patient' => 'required',
                ]);

                $note = PatientNote::create([
                    'content' => $request->content,
                    'id_patient' => $request->id_patient,
                    'id_worker' => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                ]);


                return response()->json(["message" => "Note Create"],201);
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

    public function update(Request $request, $id)
    {

    }


    public function destroy($id)
    {

    }

    public function getByPatient($idPatient)
    {
        try{
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true) {
                $notes = PatientNote::where('id_patient', $idPatient)
                    ->where('is_deleted',0)
                    ->get();

                if ($notes->isEmpty()) {
                    $patientNotes = ["Aucune note enregistrée"];
                } else {
                    foreach ($notes as $note) {
                        $creat = substr($note->created_at, 0, 10);
                        $creat = implode('/', array_reverse(explode('-', $creat)));


                        $patientNotes[] = [
                            'id_patient_note' => $note->id_patient_note,
                            'content' => $note->content,
                            'creat' => $creat,
                            'worker' => [
                                'firstName' => $note->worker->user->firstname,
                                'lastName' => $note->worker->user->lastname
                            ]
                        ];
                    }
                }
                return response()->json($patientNotes);
            }
        }catch (Exception $exception){
            return response()->json($exception);
        }

    }
}
