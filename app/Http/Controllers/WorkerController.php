<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\User;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class WorkerController extends Controller
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

    public function byMasterAudio()
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true){
                $userToken = $this->idUserService->getAuthenticatedUser();

                $worker = User::leftJoin('worker', 'user.id_user', '=', 'worker.id_user')
                ->where("id_worker",$userToken->worker->id_user)
                ->whereNotNull('worker.id_user')
                    ->with([
                        "workerSkill",
                        "workerSkill.skill",
                    ])
                    ->get();

                $data = $worker->map(function ($worker){
                    return [
                        "id_user" => $worker->id_user,
                        "lastname" => $worker->lastname,
                        "firstname"=> $worker->firstname,
                        "email"=> $worker->email,
                        "skills" => $worker->workerSkill->map(function ($skill) {
                            return [
                                "id_skill" => $skill->id_skill,
                                "content" => $skill->skill->content,
                                "color" => $skill->skill->color
                            ];
                        })

                    ];
                });

                return response()->json($data);

            }else{
                return response()->json(["message" => "You do not have the rights"], 401);
            }
        }catch (\Exception $exception){
            throw $exception;
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
