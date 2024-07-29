<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use App\Models\UsefulLink;
use App\Services\IdUserService;
use App\Services\PermissionService;

class UsefulLinkController extends Controller
{
    protected $permissionService;
    protected $idUserService;

    public function __construct(IdUserService $idUserService, PermissionService $permissionService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
    }

    public function getByUser()
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {
                $idUser = $this->idUserService->getAuthenticatedIdUser()['id_user'];
                $usefulLink = UsefulLink::where('id_worker',$idUser)->get();
                return response()->json($usefulLink);
            }

            return response()->json(["message" => "You do not have the rights"], 401);

        }catch (\Exception $exception){
            return response()->json($exception);
        }
    }

    public function create(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if($permissions["isWorker"] == true){

                $usefulLink = UsefulLink::create([
                    'wording' => $request->wording,
                    'link' => $request->link,
                    'id_worker' => $this->idUserService->getAuthenticatedIdUser()['id_user'],
                ]);
                return response()->json(["message" => "Link create"], 201);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        }catch (\Exception $exception){
            return response()->json($exception);
        }
    }


    public function delete(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {
                $this->validate($request, [
                    'id_useful_link' => 'required|integer',
                ]);

                $usefulLink = UsefulLink::findOrFail($request->id_useful_link);

                $usefulLink->delete();

                return response()->json(['message' => 'UsefulLink deleted successfully']);
            }
            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (Exception $exception) {
            return response()->json($exception);

        }
    }

    public function update(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {
                $this->validate($request, [
                    "wording" => "required",
                    "link" => "required",
                ]);

                $usefulLink = UsefulLink::find($request->id_useful_link);

                if (!$usefulLink) {
                    return response()->json(["message" => "Useful Link not found"], 404);
                }

                $usefulLink->update([
                    "wording" => $request->wording,
                    "link" => $request->link,
                    "id_worker" => $this->idUserService->getAuthenticatedIdUser()["id_user"],
                ]);

                return response()->json(["message" => "Useful Link updated"], 200);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function show($id_useful_link)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $usefulLink = UsefulLink::find($id_useful_link);

                if (!$usefulLink) {
                    return response()->json(["message" => "Useful Link not found"], 404);
                }


                return response()->json($usefulLink, 200);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
