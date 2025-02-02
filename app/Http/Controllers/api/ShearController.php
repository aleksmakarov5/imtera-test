<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\shear;
use App\Models\vertex;
use App\Models\edge;
use app\Models\matrix;



class ShearController extends Controller
{
    public function save(Request $request)
    {
        $shears = shear::all();
        foreach ($shears as $shear) {
            $shear->delete();
            $vertex = vertex::where('shear_id', $shear->id)->delete();
            $edge = edge::where('shear_id', $shear->id)->delete();
        }
        $data = $request->validate([
            'M' => 'required|numeric',
            'K' => 'required|numeric',
            'z' => 'required|array',
            'y' => 'required|array',
            'i1' => 'required|array',
            'i2' => 'required|array',
        ]);
        $shear = new shear;
        $shear->M = $data['M'];
        $shear->K = $data['K'];
        $shear->save();
        $i = 0;

        foreach ($data['z'] as $key => $value) {
            $vertex = new vertex;
            $vertex->z = $data['z'][$i];
            $vertex->y = $data['y'][$i];
            $vertex->npp = $i;
            $vertex->shear_id = $shear->id;
            $vertex->save();
            $i++;
        }

        for ($i = 0; $i < count($data['i1']); $i++) {
            $edge = new edge;
            $edge->i1 = $data['i1'][$i];
            $edge->i2 = $data['i2'][$i];
            $edge->npp = $i;
            $edge->shear_id = $shear->id;
            $edge->save();
        }
        $vertex = vertex::where('shear_id', $shear->id)->get();
        $edge = edge::where('shear_id', $shear->id)->get();
        return [$shear, $vertex, $edge];
    }
    public function load()
    {
        $shear = shear::first();
        $vertex = vertex::where('shear_id', $shear->id)->get();
        $edge = edge::where('shear_id', $shear->id)->get();
        return [$shear, $vertex, $edge];
    }
}
