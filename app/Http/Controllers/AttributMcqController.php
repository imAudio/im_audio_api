<?php

namespace App\Http\Controllers;

use App\Models\AttributMcq;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class AttributMcqController extends Controller
{

    protected $idUserService;
    protected $permissionService;
    public function __construct(IdUserService $idUserService,PermissionService $permissionService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        //
    }

    public function create(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true) {

                $this->validate($request, [
                    'id_mcq' => 'required',
                    'id_patient' => 'required',
                ]);


                $attributMcq = AttributMcq::create([
                    "id_mcq" => $request->id_mcq,
                    "id_patient" => $request->id_patient,
                    "id_worker" => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                    "state" => "attente",
                ]);

                $creat = $attributMcq->created_at->format('d/m/Y');

                $attributMcqFormated =  [
                    'id_mcq' => $attributMcq->id_mcq,
                    'state' => $attributMcq->state,
                    'worker' => [
                        'firstName' => $attributMcq->worker->user->firstname,
                        'lastName' => $attributMcq->worker->user->lastname,
                    ],
                    'creat' => $creat,
                    'created_at' => $attributMcq->created_at,
                    'mcq' => [
                        'content' => $attributMcq->mcq->content,
                        'type' => $attributMcq->mcq->type,
                    ]
                ];

                return response()->json(['message' => 'attributMcq is add','attributMcq' => $attributMcqFormated],201);
            }
        } catch (\Exception $e) {
            error_log('Exception during creation: ' . $e->getMessage());
            throw $e;
        }
    }



    public function show($id)
    {
        //
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
