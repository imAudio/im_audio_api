<?php

namespace App\Http\Controllers;

use App\Services\IdUserService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Models\UserDocument;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;

class UserDocumentController extends Controller
{
    protected $permissionService;
    protected $idUserService;

    public function __construct(IdUserService $idUserService, PermissionService $permissionService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
    }

    public function create(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {
                $this->validate($request, [
                    'document' => 'required|file|mimes:pdf,doc,docx,jpg,png',
                ]);

                if ($file = $request->file('document')) {
                    $path = $file->store('documents/user_' . $request->id_user, 'public');

                    $document = new UserDocument();
                    $document->id_user = $request->id_user;
                    $document->type = $request->type;
                    $document->file_name = $file->getClientOriginalName();
                    $document->file_path = $path;
                    $document->file_type = $file->getClientMimeType();
                    $document->id_worker = $this->idUserService->getAuthenticatedIdUser()['id_user'];
                    $document->save();

                    return response()->json(['message' => 'Document uploaded successfully', 'document' => $document], 201);
                }

                return response()->json(['message' => 'File upload failed'], 500);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
            return response()->json($exception);
        }
    }

    public function download($id_document)
    {
        $document = UserDocument::findOrFail($id_document);
        $filePath = storage_path('app/public/' . $document->file_path);

        if (file_exists($filePath)) {
            return response()->download($filePath, $document->file_name);
        } else {
            return response()->json(['error' => 'File not found.'], 404);
        }
    }

    public function getDocumentByUser($id_user)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {
                $documents = UserDocument::where('id_user', $id_user)->get();
                return response()->json($documents, 200);
            }

            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
            return response()->json($exception);
        }
    }

    public function delete(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {
                $this->validate($request, [
                    'id_document' => 'required|integer|exists:user_document,id_document',
                ]);

                $document = UserDocument::findOrFail($request->id_document);


                Storage::disk('public')->delete($document->file_path);

                $document->delete();

                return response()->json(['message' => 'Document deleted successfully']);
            }
            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (Exception $exception) {
            return response()->json($exception);

        }
    }
}
