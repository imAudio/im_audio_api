<?php

namespace App\Http\Controllers;

use App\Models\AudioCenter;
use Illuminate\Http\Request;

class AudioCenterController extends Controller
{

    public function index()
    {
        $centerAudio =AudioCenter::get();
        return response()->json(['data' => $centerAudio],200);
    }


    public function create()
    {

    }



    public function show($id)
    {

    }

    public function update(Request $request, $id)
    {

    }


    public function destroy($id)
    {

    }
}
