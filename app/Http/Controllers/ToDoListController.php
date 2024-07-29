<?php

namespace App\Http\Controllers;

use App\Models\ToDoList;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use Illuminate\Support\Carbon;

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
                    'information' => 'required',
                    'audioCenter' => 'required',
                ]);


                $toDoList = ToDoList::create([
                    "content" => $request->information,
                    "category" => $request->category,
                    "date" => $request->date,
                    "id_user" => $request->patient,
                    "id_audio_center" => $request->audioCenter,
                    "id_worker" => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                ]);

                $creat = $toDoList->created_at->format('d/m/Y');

                $toDoListFormated = [
                    'id_to_do_list' => $toDoList->id_to_do_list,
                    'information' => $toDoList->content,
                    'category' => $toDoList->category,
                    'date' => $toDoList->date->toDateString(),
                    'worker' => [
                        'id_worker' => $toDoList->worker->user->id_user,
                        'firstName' => $toDoList->worker->user->firstname,
                        'lastName' => $toDoList->worker->user->lastname,
                    ],
                    'user' => $toDoList->user ? [
                        'id_user' => $toDoList->id_user ?: null,
                        'firstName' => $toDoList->user->firstname ?: null,
                        'lastName' => $toDoList->user->lastname ?: null,
                    ] : null,
                    'creat' => $creat,
                    'id_audio_center'=>$toDoList->id_audio_center,
                ];

                return response()->json(['message' => 'todolsit is add','toDoList' => $toDoListFormated],201);
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
                    ->whereDate('date', '<=', Carbon::now()->toDateString())
                    ->orderBy("date","asc")
                    ->get();

                $data = $toDoLists->map(function ($toDoList){
                    $creat = $toDoList->created_at->format('d/m/Y');
                    return [
                        'id_to_do_list' => $toDoList->id_to_do_list,
                        'information' => $toDoList->content,
                        'category' => $toDoList->category,
                        'date' => $toDoList->date->toDateString(),
                        'worker' => [
                            'id_worker' => $toDoList->worker->user->id_user,
                            'firstName' => $toDoList->worker->user->firstname,
                            'lastName' => $toDoList->worker->user->lastname,
                        ],
                        'user' => $toDoList->user ? [
                            'id_user' => $toDoList->id_user ?: null,
                            'firstName' => $toDoList->user->firstname ?: null,
                            'lastName' => $toDoList->user->lastname ?: null,
                        ] : null,
                        'creat' => $creat,
                        'id_audio_center'=>$toDoList->id_audio_center,
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



    public function update(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $toDoList = ToDoList::find($request->id_to_do_list);

                if (!$toDoList) {
                    return response()->json(["message" => "ToDoList not found"], 404);
                }

                $toDoList->update([
                    "content" => $request->information,
                    "category" => $request->category,
                    "date" => $request->date,
                    "id_user" => $request->id_user,
                    "id_audio_center" => $request->id_audio_center,
                    "id_worker" => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                ]);

                $creat = $toDoList->created_at->format('d/m/Y');

                $toDoListFormated = [
                    'id_to_do_list' => $toDoList->id_to_do_list,
                    'information' => $toDoList->content,
                    'category' => $toDoList->category,
                    'date' => $toDoList->date->toDateString(),
                    'worker' => [
                        'id_worker' => $toDoList->worker->user->id_user,
                        'firstName' => $toDoList->worker->user->firstname,
                        'lastName' => $toDoList->worker->user->lastname,
                    ],
                    'user' => $toDoList->user ? [
                        'id_user' => $toDoList->id_user ?: null,
                        'firstName' => $toDoList->user->firstname ?: null,
                        'lastName' => $toDoList->user->lastname ?: null,
                    ] : null,
                    'creat' => $creat,
                    'id_audio_center'=>$toDoList->id_audio_center,
                ];
                return response()->json(["message" => "To-do list update","toDoList" => $toDoListFormated]);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        }catch (\Exception $exception){
            error_log('Exception during patch: ' . $exception->getMessage());

            throw $exception;

        }
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
