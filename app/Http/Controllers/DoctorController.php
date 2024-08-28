<?php

namespace App\Http\Controllers;
use App\Models\Doctor;

use App\Services\IdUserService;
use App\Services\PermissionService;
use Exception;
use Illuminate\Http\Request;
use PhpParser\Comment\Doc;

class DoctorController extends Controller
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

    }
    public function create(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $this->validate($request, [
                    "name" => "required",
                    "type" => "required",
                    "finess" => "required",
                    "rpps" => "required",
                ]);

                $doctor = Doctor::create([
                    'name' => $request->name,
                    'type' => $request->type,
                    'finess' => $request->finess,
                    'rpps' => $request->rpps,
                ]);

                return response()->json(["message" => "create"],201);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function show($id)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $doctor = Doctor::find($id);

                return response()->json($doctor);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function autocomplete(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $doctors = Doctor::where("name", "LIKE", "%" . $request->name . "%",)
                    ->where("type",$request->type)
                    ->get();

                $doctorFormated = $doctors->map(function ($doctor) {
                    return [
                        'label' => $doctor->name,
                        'value' => $doctor->id_doctor,
                    ];
                });

                return response()->json($doctorFormated);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }
    public function update(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $doctor = Doctor::find($request->id_doctor);

                if (!$doctor){
                    return response()->json(["message" => "Doctor not found"]);
                }

                $doctor->update([
                    'name' => $request->name,
                    'type' => $request->type,
                    'finess' => $request->finess,
                    'rpps' => $request->rpps,
                ]);


                return response()->json($doctor);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $doctor = Doctor::find($request->id_doctor);
                if (!$doctor){
                    return response()->json(["message" => "Doctor not found"]);
                }
                $doctor->delete();

                return response()->json(["message" => "Delete success"]);
            }else{
                return response()->json(["message" => "You do not have the rights"],401);
            }

        }catch (Exception $exception){
            return response()->json($exception,500);
        }
    }
}
