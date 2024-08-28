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

    public function getByAudioCenterToCalendar(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $idAudioCenter = $request->query('id-audio-center');
                $start = $request->input('start');
                $end = $request->input('end');

                // Ajustement de +1 -1 semaine de la plage horaire
                $start = strtotime(date("Y-m-d", strtotime($start)) . " -1 week");
                $end = strtotime(date("Y-m-d", strtotime($end)) . " +1 week");

                // Plage horaire mis au bon format
                $startDate = \Carbon\Carbon::parse($start);
                $endDate = Carbon::parse($end);

                $toDoLists = ToDoList::where('id_audio_center', $idAudioCenter)
                    ->where("is_deleted", 0)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get()
                    ->groupBy(function ($toDoList) {
                        return $toDoList->date->toDateString();
                    });

                $data = collect();

                foreach ($toDoLists as $date => $toDos) {
                    $startTime = \Carbon\Carbon::createFromTime(9, 0, 0); // Commence à 9h00
                    foreach ($toDos as $toDoList) {
                        if ($toDoList->category == 1) {
                            $backgroundColor = "#ffefd5";
                            $title = "Autre";
                        } else {
                            $backgroundColor = "#F70CCC";
                            $title = "Facturation";
                        }

                        $data->push([
                            'id_to_do_list' => $toDoList->id_to_do_list,
                            'description' => $toDoList->content,
                            'start' => $toDoList->date->toDateString() . "T" . $startTime->format('H:i:s') . ".000000Z",
                            'backgroundColor' => $backgroundColor,
                            'title' => $title,
                            'user' => $toDoList->user ? [
                                'id_user' => $toDoList->id_user ?: null,
                                'firstName' => $toDoList->user->firstname ?: null,
                                'lastName' => $toDoList->user->lastname ?: null,
                            ] : null,
                            'worker' => [
                                'id_worker' => $toDoList->worker->user->id_user,
                                'firstName' => $toDoList->worker->user->firstname,
                                'lastName' => $toDoList->worker->user->lastname,
                            ],
                        ]);

                        $startTime->addMinutes(60); // Incrément de 30 minutes
                    }
                }

                return response()->json($data);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
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
