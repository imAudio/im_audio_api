<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerSkill;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;

class WorkerSkillController extends Controller
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
        try {
            $permissions = $this->permissionService->getPermissions();

            if($permissions["isMasterAudio"]){

                $workerSkill = WorkerSkill::all();
                return response()->json($workerSkill);
            }else{
                return response()->json(["message" => "You do not have the rights"], 401);
            }

        }catch (\Exception $exception){
            error_log('Exception during creation: ' . $exception->getMessage());
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
    public function create(Request $request)
    {
        try{
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isMasterAudio"]) {

                for ($i = 0 ; $i < count($request["initial_skill"]); $i++){
                    if ($request["initial_skill"][$i] != $request["update_skill"][$i]){
                        if($request["initial_skill"][$i]["assigned"] == true){
                            WorkerSkill::where("id_skill",$request["update_skill"][$i]["id_skill"])
                                ->where("id_worker",$request->id_worker)->delete();
                        }else{

                            WorkerSkill::create([
                                "id_worker" => $request->id_worker,
                                "id_skill" => $request["initial_skill"][$i]["id_skill"],
                                "id_master_audio" => $this->idUserService->getAuthenticatedIdUser()['id_user']
                            ]);
                        }
                    }else{

                    }

                }
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

                return response()->json($data,201);
            }else{
                return response()->json(["message" => "You do not have the rights"], 401);
            }
        }catch (\Exception $exception){
            error_log('Exception during creation: ' . $exception->getMessage());
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


    public function show($id_worker)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $workerSkill = WorkerSkill::where("id_worker",$id_worker)->get();

                if(count($workerSkill) > 0){
                    $data = $workerSkill->map(function ($skill){
                        return [
                            "worker" => [
                                "id_worker" => $skill->id_worker,
                                "firstname" => $skill->worker->user->firstname,
                                "lastname" => $skill->worker->user->lastname,
                            ],
                            'skill' => [
                                "id_skill" => $skill->skill->id_skill,
                                "content" => $skill->skill->content,
                                "color" => $skill->skill->color
                            ],
                            "master_audio" =>[
                                "id_master_audio" => $skill->masterAudio->id_worker,
                                "firstname" => $skill->masterAudio->worker->user->firstname,
                                "lastname" => $skill->masterAudio->worker->user->lastname,
                            ]
                        ];
                    });
                    return response()->json($data);
                }else{
                    return response()->json(['message' => 'id_worker unknown']);
                }
            }
            return response()->json(["message" => "You do not have the rights"],401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function skillSort($id_worker)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isMasterAudio"] == true) {
                $userToken = $this->idUserService->getAuthenticatedUser();

                $skills = Skill::where("id_master_audio", $userToken->worker->id_user)->get();

                $workerSkills = WorkerSkill::where("id_worker", $id_worker)->pluck('id_skill')->toArray();

                $skillSort = [];

                foreach ($skills as $skill) {
                    $skillSort[] = [
                        "id_skill" => $skill->id_skill,
                        "content" => $skill->content,
                        "color" => $skill->color,
                        "assigned" => in_array($skill->id_skill, $workerSkills),
                    ];
                }

                return response()->json($skillSort);

            } else {
                return response()->json(["message" => "You do not have the rights"], 401);
            }

        } catch (\Exception $exception) {
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }



    //public function update(Request $request)
    //{
    //    try {
    //        $permissions = $this->permissionService->getPermissions();
    //        if ($permissions["isMasterAudio"] == true) {
    //            $workerSkill = WorkerSkill::where("id_worker",$id_worker)->where("id_skill")get();
    //            if (!$workerSkill) {
    //                return response()->json(["message" => "WorkerSkill not found"], 404);
    //
    //            }
    //            $workerSkill->update([
    //                "content" => $request->information,
    //                "id_master_audio" => $this->idUserService->getAuthenticatedIdUser()['id_user']
    //            ]);
    //            return response()->json($workerSkill);
    //        }
    //        return response()->json(["message" => "You do not have the rights"], 401);
    //    }catch (\Exception $exception) {
    //        error_log('Exception during patch: ' . $exception->getMessage());
    //
    //        throw $exception;
    //pas finis trop chiant celon loukas
    //    }
    //}


    public function destroy(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isMasterAudio"] == true) {
                $this->validate($request, [
                    'id_worker' => 'required|integer',
                    'id_skill' => 'required|integer',
                ]);

                $workerSkill = WorkerSkill::where('id_worker', $request->id_worker)
                    ->where('id_skill', $request->id_skill)
                    ->first();

                if ($workerSkill){
                    DB::table('worker_skill')
                        ->where('id_worker', $request->id_worker)
                        ->where('id_skill', $request->id_skill)
                        ->delete();
                    return response()->json(['message' => 'WorkerSkill Delete']);
                }

                return response()->json(['message' => 'WorkerSkill unknown']);

            }
            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (\Exception $exception) {
            error_log('Exception during Delete: ' . $exception->getMessage());

            throw $exception;

        }
    }
}
