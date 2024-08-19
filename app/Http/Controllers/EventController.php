<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\IdUserService;
use App\Services\PermissionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $permissionService;
    protected $idUserService;
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
            if ($permissions["isWorker"] == true) {
                $this->validate($request, [
                    'id_audio_center' => 'required',
                    'start' => 'required',
                    'end' => 'required',
                ]);

                Event::create([
                    'id_audio_center' => $request->id_audio_center,
                    'id_user' => $request->id_user,
                    'id_type_event' => $request->id_type_event,
                    'start' => $request->start,
                    'end' => $request->end,
                    'description' => $request->description,
                    'id_worker' => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                ]);
                return response()->json(["message" => "Event create"],201);
            }
            return response()->json(["message" => "You do not have the rights"],401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }


    public function show($id_event)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $event = Event::find($id_event);

                if($event){
                        $data = [
                            'id_event' => $event->id_event,
                            'start' => $event->start,
                            'end' => $event->end,
                            'description' => $event->description,
                            'state' => $event->state,
                            'audio_center' => [
                                'id_audio_center' => $event->id_audio_center,
                                'name' => $event->audioCenter->name
                            ],
                            'event_type' => [
                               'content' =>  $event->eventType->content,
                               'id_event_type' => $event->id_type_event,
                            ],
                            'user' => [
                                'id_user' => $event->user ? $event->user->id_user : null,
                                'lastname' => $event->user ? $event->user->lastname : null,
                                'firstname' => $event->user ? $event->user->firstname : null,
                            ]

                        ];
                    return response()->json($data);
                }
                return response()->json(['message' => 'id_event unknown']);

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
            if ($permissions["isWorker"] == true) {
                $this->validate($request, [
                    'id_audio_center' => 'required',
                    'start' => 'required',
                    'end' => 'required',
                ]);

                $event = Event::find($request->id_event);

                if (!$event) {
                    return response()->json(["message" => "Event not found"], 404);
                }

                $event->update([
                    'id_audio_center' => $request->id_audio_center,
                    'id_user' => $request->id_user,
                    'id_type_event' => $request->id_type_event,
                    'start' => $request->start,
                    'end' => $request->end,
                    'description' => $request->description,
                    'id_worker' => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                ]);

                return response()->json(["message" => "Event updated"], 200);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {
                $this->validate($request, [
                    'id_event' => 'required|integer',
                ]);

                $event = Event::find($request->id_event);
                if($event){
                    $event->delete();
                    return response()->json(['message' => 'Event deleted successfully']);
                }
                return response()->json(['message' => 'id_event unknown']);

            }
            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (Exception $exception) {
            return response()->json($exception);

        }
    }
    public function getByAudioCenterAndDate(Request $request,$id_audio_center)
    {
        
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                // Récupération de plage horaire
                $start = $request->input('start');
                $end = $request->input('end');

                // Ajustement de +1 -1 semaine de la plage horaire
                $start = strtotime(date("Y-m-d", strtotime($start)) . " -1 week");
                $end = strtotime(date("Y-m-d", strtotime($end)) . " +1 week");

                // Plage horaire mis au bon format
                $startDate = Carbon::parse($start);
                $endDate = Carbon::parse($end);

                $events = Event::where("id_audio_center",$id_audio_center)
                    ->whereBetween('start', [$startDate, $endDate])->get();

                $data = $events->map(function ($event){
                    return [
                        'id_event' => $event->id_event,
                        'start' => $event->start,
                        'end' => $event->end,
                        'description' => $event->description,
                        'backgroundColor' => $event->eventType->background_color,
                        'title' => $event->eventType->content,
                        'state' => $event->state,
                        'user' => [
                            'id_user' => $event->id_user ?? null,
                            'lastname' => $event->user->lastname ?? null,
                            'firstname' => $event->user->firstname ?? null
                        ] ?? null
                    ];
                });

                return response()->json($data);
            }
            return response()->json(["message" => "You do not have the rights"],401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function editState(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {
                $this->validate($request, [
                    'id_event' => 'required|integer',
                ]);

                $event = Event::find($request->id_event);
                if($event){
                    $event->state = $request->state;
                    $event->save();
                    return response()->json(['message' => 'Event patch state successfully']);
                }
                return response()->json(['message' => 'id_event unknown']);

            }
            return response()->json(["message" => "You do not have the rights"], 401);
        }catch (\Exception $exception){
            return response()->json($exception,500);
        }
    }
}
