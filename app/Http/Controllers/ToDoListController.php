<?php

namespace App\Http\Controllers;

use App\Models\ToDoList;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
class ToDoListController extends Controller
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
                    'content' => 'required',
                    'audioCenter' => 'required',
                ]);

                $toDoList = new ToDoList();
                $toDoList->content = $request->json('content');
                $toDoList->category = $request->json('category');
                $toDoList->date = $request->json('date');
                $toDoList->id_user = $request->json('patient');
                $toDoList->id_audio_center = $request->json('audioCenter');
                $toDoList->id_worker = $this->idUserService->getAuthenticatedIdUser()['id_user'];


                $toDoList->save();

                return response()->json(['message' => 'todolsit is add'],201);
            }
        } catch (\Exception $e) {
            error_log('Exception during creation: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getByAudioCenter($id_audio_center)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {
                $toDoLists = ToDoList::where('id_audio_center',$id_audio_center)
                    ->where("is_deleted",0)
                    ->get();

                $data = $toDoLists->map(function ($toDoList){
                    $creat = $toDoList->created_at->format('d/m/Y');
                    return [
                        'id_to_do_list' => $toDoList->id_to_do_list,
                        'content' => $toDoList->content,
                        'category' => $toDoList->category,
                        'date' => $toDoList->date,
                        'worker' => [
                            'firstName' => $toDoList->worker->user->firstname,
                            'lastName' => $toDoList->worker->user->lastname,
                        ],
                        'user' => $toDoList->user ? [
                            'id_user' => $toDoList->id_user ?: null,
                            'firstName' => $toDoList->user->firstname ?: null,
                            'lastName' => $toDoList->user->lastname ?: null,
                        ] : null,
                        'creat' => $creat,


                    ];
                });



                return response()->json($data);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        }catch (\Exception $exception) {
            error_log('Exception during creation: ' . $exception->getMessage());
            throw $exception;
        }

    }
    public function show($id)
    {
        //
    }



    public function update(Request $request, $id)
    {
        //
    }
    public function make($id_to_do_list)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {
                $toDoList = ToDoList::find($id_to_do_list);
                $toDoList->is_deleted = 1;
                $toDoList->save();

                return response()->json(["message" => "To-do list make"]);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        }catch (\Exception $exception){
            error_log('Exception during patch: ' . $exception->getMessage());
            throw $exception;
        }
    }

    public function destroy($id)
    {
        //
    }
}
