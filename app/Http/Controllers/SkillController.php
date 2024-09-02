<?php
namespace App\Http\Controllers;
use App\Models\Skill;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class SkillController extends Controller{
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

                $skill = Skill::all();
                return response()->json($skill);
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
                $this->validate($request, [
                    'information' => 'required'
                ]);
                $skill = Skill::create([
                    "content" => $request->information,
                    "id_master_audio" => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                    "color" => $request->color
                ]);
                return response()->json($skill,201);
            }else{
                return response()->json(["message" => "You do not have the rights"], 401);
            }
        }catch (\Exception $exception){
            error_log('Exception during creation: ' . $exception->getMessage());
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


    public function show($id_skill)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $skill = Skill::find($id_skill);
                
                if($skill){
                    $data = [
                        'id_skill' => $skill->id_skill,
                        "content"=> $skill->content,
                        "id_master_audio" => $skill->id_master_audio,
                        "color" => $skill->color,
                    ];
                    return response()->json($data);
                }
                return response()->json(['message' => 'id_skill unknown']);

            }
            return response()->json(["message" => "You do not have the rights"],401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function update(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isMasterAudio"] == true) {
                $skill = Skill::find($request->id_skill);
                if (!$skill) {
                    return response()->json(["message" => "skill not found"], 404);

                }
                $skill->update([
                    "content" => $request->information,
                    "id_master_audio" => $this->idUserService->getAuthenticatedIdUser()['id_user']
                ]);
                return response()->json($skill);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        }catch (\Exception $exception){
            error_log('Exception during patch: ' . $exception->getMessage());

            throw $exception;
        }
    }




    public function destroy(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isMasterAudio"] == true) {
                $this->validate($request, [
                    'id_skill' => 'required|integer',
                ]);

                $skill = Skill::find($request->id_skill);
                if($skill){
                    $skill->delete();
                    return response()->json(['message' => 'Skill deleted successfully']);
                }
                return response()->json(['message' => 'id_Skill unknown']);

            }
            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (\Exception $exception) {
            error_log('Exception during Delete: ' . $exception->getMessage());

            throw $exception;

        }
    }
}
