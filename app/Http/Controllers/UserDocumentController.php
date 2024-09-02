<?php

namespace App\Http\Controllers;

use App\Models\AudioCenter;
use App\Models\Company;
use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\DeviceModelCharacteristic;
use App\Models\Patient;
use App\Models\User;
use App\Services\IdUserService;
use App\Services\PdfService;
use App\Services\PermissionService;
use DateTime;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\UserDocument;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\Tcpdf\Fpdi;
use TCPDF;
use function PHPUnit\Framework\isEmpty;

class UserDocumentController extends Controller
{
    protected $permissionService;
    protected $idUserService;
    protected $pdfService;
    public function __construct(IdUserService $idUserService, PermissionService $permissionService, PdfService $pdfService)
    {
        $this->idUserService = $idUserService;
        $this->permissionService = $permissionService;
        $this->pdfService = $pdfService;
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
                    //$path = $file->store('documents/user_' . $request->id_user, 'public');

                    $document = $this->uploadDocument($file, $request->id_user, $request->type);

                    return response()->json(['message' => 'Document uploaded successfully', 'document' => $document], 201);
                }

                return response()->json(['message' => 'File upload failed'], 500);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
            return response()->json($exception);
        }
    }

    public function uploadDocument($file,$id_user,$type)
    {
        try {
            $path = $file->store('documents/user_' . $id_user, 'public');
            $document = new UserDocument();
            $document->id_user = $id_user;
            $document->type = $type;
            $document->file_name = $file->getClientOriginalName();
            $document->file_path = $path;
            $document->file_type = $file->getClientMimeType();
            $document->id_worker = $this->idUserService->getAuthenticatedIdUser()['id_user'];
            $document->save();

            return $document;
        }catch (\Exception $exception) {
            return $exception;
        }
    }

    public function download($id_document) // mettre une vérification du rôle
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
                $documents = UserDocument::where('id_user', $id_user)
                    ->where('type','!=','devis attente')->get();

                $data = $documents->map(function ($document){
                    $creat = $document->created_at->format('d/m/Y');
                    return [
                        'id_document' => $document->id_document,
                        'file_name' => $document->file_name,
                        'type' => $document->type,
                        'creat' => $creat,
                        'worker' => [
                            'id_worker' =>  $document->worker->id_user,
                            'firstName' => $document->worker->user->firstname,
                            'lastName' => $document->worker->user->lastname,
                        ]
                    ];
                });

                return response()->json($data, 200);
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

    public function generateQuote(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $document = UserDocument::findOrFail(34);
                $path = storage_path('app/public/' . $document->file_path);

                $patient = Patient::with(['user'])->find($request->id_user);
                $company = Company::find(1);
                $audioCenter = AudioCenter::find(1);
                $deviceModels = DeviceModel::where('id_device_type', 5)->get();

                foreach ($deviceModels as $deviceModel) {

                    $pdf = new Fpdi();

                    $response = $this->headerQuote($pdf, $path, $company, $patient, $request, $audioCenter);
                    $pdf = $response[0];
                    $numberDevice = $response[1];

                    if ($deviceModel->class == 1) {
                        $pdf = $this->bodyQuoteClass1($deviceModel, $request, $pdf, $numberDevice);
                        $pdf->AddPage();
                        $tplId = $pdf->importPage(2);
                        $pdf->useTemplate($tplId, 0, 0, 210);
                        $pdf = $this->footerQuote($pdf, $company);
                    } else {
                        $response = $this->firstPageBodyQuoteClass2($deviceModel, $request, $pdf, $numberDevice);
                        $pdf = $response[0];
                        $devicePrice = $response[1];
                        $pdf = $this->secondPageBodyQuoteClass2($deviceModel, $request, $pdf, $numberDevice, $path, $devicePrice, $company);
                    }

                    $directoryPath = 'documents/user_' . $request->id_user . '/quote/';
                    $fileName = 'devis_' . $deviceModel->content . '_' . time() . '.pdf';

                    // Créez le répertoire s'il n'existe pas
                    if (!file_exists($directoryPath)) {
                        Storage::makeDirectory($directoryPath);

                    }

                    // Sauvegarde du fichier PDF
                    $filePath = storage_path('app/public/' . $directoryPath . $fileName);
                    $pdf->Output($filePath, 'F');

                    $document = new UserDocument();
                    $document->id_user = $request->id_user;
                    $document->type = "devis attente";
                    $document->file_name = $fileName;
                    $document->file_path = $directoryPath . $fileName;
                    $document->file_type = "application/pdf";
                    $document->id_worker = $this->idUserService->getAuthenticatedIdUser()['id_user'];

                    $document->save();

                    if ($deviceModel->content == "evolv1200") {
                        //return response()->download($filePath, $document->file_name);
                    }
                }

                //return response()->download($filePath, $document->file_name);
                return response()->json(["message" => "Ok"]);
            }
            return response()->json(["message" => "You do not have the rights"], 401);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function formatedNumber($number){
        return number_format($number, 2, ',', '');
    }

    public function headerQuote($pdf,$path,$company,$patient,Request $request,$audioCenter)
    {
        $pdf->AddPage();
        $pdf->setSourceFile($path);
        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId, 0, 0, 210);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(14, 25);
        $pdf->Write(0, $company->name);
        $pdf->SetXY(14, 29);
        $pdf->SetFont('helvetica', '', 6);
        $pdf->Write(0, $company->address);
        $pdf->SetXY(14, 31);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Write(0, $company->postal_code . ', ' . $company->city);
        $pdf->SetXY(14, 35);
        $pdf->Write(0, $company->phone . '  ' . $company->email);
        $pdf->SetXY(14, 39);
        $pdf->Write(0, $company->manager);
        $pdf->SetXY(14, 43);
        $pdf->Write(0, 'Rpps : ' . $company->rpps);

        $pdf->SetXY(110, 25);
        $pdf->Write(0, $patient->user->firstname . ' ' . $patient->user->lastname);

        $pdf->SetXY(110, 29);
        $pdf->Write(0, $patient->social_security_number);

        $pdf->SetXY(110, 33);
        $pdf->Write(0, $patient->address . ' ' . $patient->postal_code . ', ' . $patient->city);

        $pdf->SetXY(176, 41);
        $pdf->Write(0, $request->date_prescription);

        $pdf->SetXY(14, 49);
        $pdf->Write(0, 'JE NE SAIS PAS');

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY(14, 53);
        $pdf->Write(0, "Date et lieu d'emission : " . date('d/m/Y') . ' à ' . $audioCenter->name);
        $pdf->SetFont('helvetica', '', 10);

        $date = new DateTime();
        $pdf->SetXY(85, 56.5);
        $pdf->Write(0, $date->modify('+2 months')->format('d/m/Y'));


        $numberDevice = 0;

        if ($request->right['ear'] == true) {
            $numberDevice++;
            $pdf->SetXY(42.5    , 65.5);
            $pdf->Write(0, 'X');
            if ($request->right['first'] == true) {
                $pdf->SetXY(80.5, 65.5);
                $pdf->Write(0, 'X');
            } else {
                $pdf->SetXY(108.5, 65.5);
                $pdf->Write(0, 'X');
                $pdf->SetXY(162, 65.8);
                $pdf->Write(0, $request->right['date']);
            }
        }


        if ($request->left['ear'] == true) {
            $pdf->SetXY(42.5, 69);
            $pdf->Write(0, 'X');
            $numberDevice++;
            if ($request->left['first'] == true) {

                $pdf->SetXY(80.5, 69);
                $pdf->Write(0, 'X');
            } else {
                $pdf->SetXY(108.5, 69);
                $pdf->Write(0, 'X');

                $pdf->SetXY(162, 69);
                $pdf->Write(0, $request->left['date']);
            }
        }

        return [$pdf,$numberDevice];
    }

    public function bodyQuoteClass1($deviceModel,Request $request,$pdf,$numberDevice)
    {

        $dutyFree= intval($deviceModel->all_taxes_combined) * 0.945;
        $deviceModelCharacteristics = DeviceModelCharacteristic::where('device_content',$deviceModel->content)->get();

        if ($request->left['ear'] == true) {


            $pdf->SetXY(14, 172);
            $pdf->Write(0, 'LPP: ' . $deviceModel->lpp_left);

            $pdf->SetXY(74, 170);
            $pdf->Write(0, $deviceModel->content);

            $pdf->SetXY(170, 170);
            $pdf->Write(0,$this->formatedNumber($dutyFree) );

            $pdf->SetXY(185, 170);
            $pdf->Write(0, $deviceModel->all_taxes_combined);
        }else{
            $pdf->SetXY(170, 170);
            $pdf->Write(0, '0,00');

            $pdf->SetXY(185, 170);
            $pdf->Write(0, '0,00');
        }

        if ($request->right['ear'] == true) {


            $pdf->SetXY(14, 126);
            $pdf->Write(0, 'LPP: ' . $deviceModel->lpp_right);



            $pdf->SetXY(170, 140);
            $pdf->Write(0,$this->formatedNumber($dutyFree) );


            $pdf->SetXY(185, 140);
            $pdf->Write(0, $deviceModel->all_taxes_combined);

        }else{
            $pdf->SetXY(170, 140);
            $pdf->Write(0, '0,00');

            $pdf->SetXY(185, 140);
            $pdf->Write(0, '0,00');
        }

        $pdf->SetXY(74, 122);
        $pdf->Write(0, $deviceModel->deviceManufactured->address . ' ' . $deviceModel->deviceManufactured->postal_code . ', ' . $deviceModel->deviceManufactured->city);

        $pdf->SetXY(74, 126);
        $pdf->Write(0, $deviceModel->content);

        $pdf->SetXY(74, 134);
        $pdf->Write(0, 'Caractéristiques essentielles, notamment:');

        $y = 138;

        foreach ($deviceModelCharacteristics as $deviceModelCharacteristic){
            $pdf->SetXY(74, $y);
            $pdf->Write(0, '- ' .  $deviceModelCharacteristic->deviceCharacteristic->information);
            $y = $y+4;
        }

        $pdf->SetXY(176, 178);
        $pdf->Write(0, '0,00');

        $pdf->SetXY(191 , 178);
        $pdf->Write(0, '0,00');

        $pdf->SetXY(170  , 184.5);
        $pdf->Write(0,$this->formatedNumber($dutyFree * $numberDevice));

        $pdf->SetXY(185  , 184.5);
        $pdf->Write(0,$this->formatedNumber(intval($deviceModel->all_taxes_combined) * $numberDevice));

        $pdf->SetXY(187  , 189.5);
        $pdf->Write(0,$this->formatedNumber($request->social_security_class_1));

        $pdf->SetXY(185  , 194.5);
        $pdf->Write(0,$this->formatedNumber($request->mutual_class_1));

        $pdf->SetXY(185  , 199);
        $pdf->Write(0,$this->formatedNumber(intval($deviceModel->all_taxes_combined) * $numberDevice - ($request->social_security_class_1 + $request->mutual_class_1)) );

        return $pdf;
    }

    public function firstPageBodyQuoteClass2($deviceModel,Request $request,$pdf,$numberDevice)
    {
        $dutyFree= intval($deviceModel->all_taxes_combined) * 0.945;
        $deviceModelCharacteristics = DeviceModelCharacteristic::where('device_content',$deviceModel->content)->get();
        $deviceModelClass1 = DeviceModel::where('content',$deviceModel->class_1_type)->first();
        $this->bodyQuoteClass1($deviceModelClass1,$request,$pdf,$numberDevice);
        if ($request->left['ear'] == true) {

            $pdf->SetXY(14, 260);
            $pdf->Write(0, 'LPP: ' . $deviceModel->lpp_left);

            $pdf->SetXY(50, 257 );
            $pdf->Write(0, $deviceModel->content);

            $pdf->SetXY(155, 258);
            $pdf->Write(0, $this->formatedNumber($dutyFree));

            $dutyFreeWithDiscount =  $dutyFree - $request->discount;
            $formattedValue = $this->formatedNumber($dutyFreeWithDiscount);
            $pdf->SetXY(170, 258);
            $pdf->Write(0, $formattedValue);

            $pdf->SetXY(185, 258);
            $pdf->Write(0, $deviceModel->all_taxes_combined);

        }else{
            $pdf->SetXY(155, 258);
            $pdf->Write(0, '0,00');

            $pdf->SetXY(170, 258);
            $pdf->Write(0, '0,00');

            $pdf->SetXY(185, 258);
            $pdf->Write(0, '0,00');

        }

        if ($request->right['ear'] == true) {

            $pdf->SetXY(14, 224);
            $pdf->Write(0, 'LPP: ' . $deviceModel->lpp_right);

            $pdf->SetXY(155, 220);
            $pdf->Write(0, $this->formatedNumber($dutyFree));

            $dutyFreeWithDiscount = $dutyFree - $request->discount;
            $formattedValue = $this->formatedNumber($dutyFreeWithDiscount);

            $pdf->SetXY(170, 220);
            $pdf->Write(0, $formattedValue);

            $pdf->SetXY(185, 220);
            $pdf->Write(0, $deviceModel->all_taxes_combined);

            $pdf->SetXY(185, 224);
            $pdf->Write(0, '- ' . $this->formatedNumber($request->discount));

            $pdf->SetXY(185, 225);
            $pdf->Write(0, '______');
            $pdf->SetXY(185, 230);
            $pdf->Write(0,$this->formatedNumber(intval($deviceModel->all_taxes_combined) - $request->discount));

        }else{
            $pdf->SetXY(170, 220);
            $pdf->Write(0, '0,00');

            $pdf->SetXY(185, 220);
            $pdf->Write(0, '0,00');
        }

        $devicePrice = intval($deviceModel->all_taxes_combined) - $request->discount;

        $pdf->SetXY(50, 220);
        $pdf->Write(0, $deviceModel->deviceManufactured->address . ' ' . $deviceModel->deviceManufactured->postal_code . ', ' . $deviceModel->deviceManufactured->city);

        $pdf->SetXY(50, 224);
        $pdf->Write(0, $deviceModel->content);

        $pdf->SetXY(50, 228);
        $pdf->Write(0, 'Caractéristiques essentielles, notamment :');

        $deviceModelCharacteristics = DeviceModelCharacteristic::where('device_content',$deviceModel->content)->get();

        $y = 232;

        foreach ($deviceModelCharacteristics as $deviceModelCharacteristic){
            $pdf->SetXY(74, $y);
            $pdf->Write(0, '- ' .  $deviceModelCharacteristic->deviceCharacteristic->information);
            $y = $y+4;
        }


        return [$pdf,$devicePrice];
    }

    public function secondPageBodyQuoteClass2($deviceModel,Request $request,$pdf,$numberDevice,$path,$devicePrice,$company)
    {
        $pdf->AddPage();
        $tplId = $pdf->importPage(2);
        $pdf->useTemplate($tplId, 0, 0, 210);

        if ($deviceModel->energy == "Rechargeable"){
            $charger = DeviceModel::where('content',$deviceModel->charger)->first();
            $dutyFreeCharger= intval($charger->all_taxes_combined) * 0.945;
            $pdf->SetXY(45, 11);
            $pdf->Write(0, $charger->content);

            $pdf->SetXY(150, 11);
            $pdf->Write(0, $this->formatedNumber($dutyFreeCharger));

            $pdf->SetXY(168, 11);
            $pdf->Write(0, $this->formatedNumber($dutyFreeCharger - $request->charger_discount));

            $pdf->SetXY(186, 11);
            $pdf->Write(0, $this->formatedNumber($charger->all_taxes_combined));
            $pdf->SetXY(184, 15);
            $pdf->Write(0, '- ' . $this->formatedNumber($request->charger_discount));


            $chargerPrice = $charger->all_taxes_combined - $request->charger_discount;
        }else{
            $pdf->SetXY(150, 11);
            $pdf->Write(0, '0,00');

            $pdf->SetXY(168, 11);
            $pdf->Write(0, '0,00');

            $pdf->SetXY(186, 11);
            $pdf->Write(0, '0,00');

            $chargerPrice = 0;
        }


        $pdf->SetXY(184, 21);
        $totalPrice = $chargerPrice + $devicePrice * $numberDevice;
        $pdf->Write(0,$this->formatedNumber($totalPrice));

        $dutyFreeTotal = ((($devicePrice + $request->discount)*0.945) * $numberDevice) + $dutyFreeCharger;   ////// ICI AUSSI IL Y A 0.945 !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $pdf->SetXY(150, 21);
        $pdf->Write(0,$this->formatedNumber($dutyFreeTotal));

        $dutyFreeTotalWithDiscount = (($devicePrice * 0.945) * $numberDevice) + $dutyFreeCharger - $request->charger_discount;
        $pdf->SetXY(165, 21);
        $pdf->Write(0,$this->formatedNumber($dutyFreeTotalWithDiscount));

        $pdf->SetXY(186, 25);
        $pdf->Write(0,$this->formatedNumber($request->social_security_class_2));

        $pdf->SetXY(186, 29);
        $pdf->Write(0,$this->formatedNumber($request->mutual_class_2));

        $pdf->SetXY(184, 33);
        $pdf->Write(0,$this->formatedNumber($totalPrice - ($request->social_security_class_2 + $request->mutual_class_2)) );

        $pdf = $this->footerQuote($pdf,$company);

        return $pdf;
    }

    public function footerQuote($pdf,$company)
    {
        $pdf->SetXY(66, 238.5);
        $pdf->Write(0, $company->manager);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(34, 263);
        $pdf->Write(0, $company->name);

        $pdf->SetXY(20, 266.5);
        $pdf->Write(0, substr($company->rpps,0,14));

        $pdf->SetXY(131, 266.5);
        $pdf->Write(0, $company->finess);

        $pdf->SetXY(18, 270);
        $pdf->Write(0, substr($company->rpps,0,9) . ' ' . $company->name);

        return $pdf;
    }

    public function searchQuote($query,$id_user)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $quotes = UserDocument::where("id_user",$id_user)
                ->where("type","devis attente")
                ->where('file_name', 'LIKE', '%' . $query . '%')->get();

                $formattedQuotes = $quotes->map(function ($quote) {
                    return [
                        'label' => $quote->file_name ,
                        'value' => $quote->id_document,
                    ];
                });
                return response()->json($formattedQuotes);
            }
            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (Exception $exception) {
            return response()->json($exception);

        }
    }

    public function checkQuote($id_user)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $quote = UserDocument::where("id_user",$id_user)
                    ->where("type","devis attente")->first();

                if ($quote != null){
                    return response()->json(["message" =>"exist"]);
                }else{
                    return response()->json(["message" =>"not exist"]);
                }
            }
            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (Exception $exception) {
            return response()->json($exception);

        }
    }

    public function checkQuoteAffiliate($id_user)
    {
        try {
            $permissions = $this->permissionService->getPermissions();
            if ($permissions["isWorker"] == true) {

                $quote = UserDocument::where("id_user",$id_user)
                    ->where("type","devis")->first();

                if ($quote != null){
                    return response()->json(["message" =>"exist"]);
                }else{
                    return response()->json(["message" =>"not exist"]);
                }
            }
            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (Exception $exception) {
            return response()->json($exception);

        }
    }

    public function checkPrescription($id_user)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"] == true) {

                $quote = UserDocument::where("id_user",$id_user)
                    ->where("type","ordonnance")->first();

                if ($quote != null){
                    return response()->json(["message" =>"exist"]);
                }else{
                    return response()->json(["message" =>"not exist"]);
                }
            }
            return response()->json(["message" => "You do not have the rights"], 401);

        } catch (Exception $exception) {
            return response()->json($exception);

        }
    }

    public function audiogramCut($id_user)
    {
        try {
            $permissions = $this->permissionService->getPermissions();

            if ($permissions["isWorker"]) {

                $audiogram = UserDocument::where("id_user",$id_user)
                    ->where("type","audiogramme")
                    ->first();

                if ($audiogram != null) {

                    $userDir = storage_path('app/public/documents/user_' . $audiogram->id_user);
                    $filePath = storage_path('app/public/' . $audiogram->file_path);
                    $outputPath = $userDir . '/temp_extracted_section.pdf';
                    $imageOutputPath = str_replace('.pdf', '.png', $outputPath);

                    if (file_exists($imageOutputPath) && filesize($imageOutputPath) > 0) {
                        return response()->file($imageOutputPath, [
                            'Content-Type' => 'image/png',
                            'Content-Disposition' => 'inline; filename="extracted_section.png"',
                        ]);
                    }

                    if (!file_exists($userDir)) {
                        mkdir($userDir, 0755, true);
                    }

                    $image = $this->pdfService->extractSection($filePath, $outputPath, 0, 30, 210, 300);

                    if (file_exists($image) && filesize($image) > 0) {
                        return response()->file($image, [
                            'Content-Type' => 'image/png',
                            'Content-Disposition' => 'inline; filename="extracted_section.png"',
                        ]);
                    } else {
                        return response()->json(["message" => "Failed to extract and convert PDF section"], 500);
                    }
                } else {
                    return response()->json(["message" => "Document not found"]);
                }
            }

            return response()->json(["status" => "error", "message" => "You do not have the rights"], 401);

        } catch (ModelNotFoundException $e) {
            return response()->json(["status" => "error", "message" => "Document not found"], 404);
        } catch (Exception $exception) {
            return response()->json([
                "status" => "error",
                "message" => "An error occurred",
                "error" => $exception->getMessage()
            ], 500);
        }
    }

}
