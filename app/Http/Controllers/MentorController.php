<?php

namespace App\Http\Controllers;

use App\Mentor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MentorController extends Controller
{
    public function index()
    {
        $mentors = Mentor::all();

        return response()->json([
            'status'=> 'success',
            'data' => $mentors
        ]);
    }

    public function show($id)
    {
        $mentor = Mentor::find($id);

        if(!$mentor){
            return response()->json([
                'status' => 'error',
                'message' => 'mentor not found'
            ],404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $mentor
        ],200);
    }

    public function create(Request $request)
    {
        //rule schema
        $rules = [
            'name' => 'required|string',
            'profile' => 'required|url',
            'profession' => 'required|string',
            'email' => 'required|email'
        ];

        //get all data
        $data = $request->all();

        //validasi
        $validator= Validator::make($data,$rules);

        //if any error
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ]);
        }

        //create data
        $mentor = Mentor::create($data);

        return response()->json(['status' => 'success', 'data' => $mentor]);
    }

    public function update(Request $request, $id)
    {
        //rule schema
        $rules = [
            'name' => 'string',
            'profile' => 'url',
            'profession' => 'string',
            'email' => 'email'
        ];

        //get all data
        $data = $request->all();

        //validasi
        $validator= Validator::make($data,$rules);

        //if any error
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ]);
        }

        $mentor = Mentor::find($id);

        if(!$mentor){
            return response()->json([
                'status' => 'error',
                'message' => 'mentor not found'
            ],404);
        }

        //you may use the fill method to populate it with an array of attributes:
        //using fillable mode in model
        $mentor->fill($data);

        //save data
        $mentor->save();
        return response()->json([
            'status' => 'success',
            'data' => $mentor
        ]);
    }

    public function destroy($id)
    {
        $mentor = Mentor::find($id);

        if(!$mentor){
            return response()->json([
                'status' => 'error',
                'message' => 'mentor not found'
            ],404);
        }

        $mentor->delete();
        return response()->json([
            'status' => 'success',
            'messages' => 'Mentor deleted'
        ]);
    }
}
