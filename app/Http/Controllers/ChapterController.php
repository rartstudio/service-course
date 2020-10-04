<?php

namespace App\Http\Controllers;

use App\Chapter;
use App\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChapterController extends Controller
{
    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'course_id' => 'required|integer'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ]);
        }

        $courseId = $request->input('course_id');
        $course = Course::find($courseId);

        if(!$course){
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ],404);
        }

        $chapter = Chapter::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'string',
            'course_id' => 'integer'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ]);
        }

        $chapter = Chapter::find($id);
        if(!$chapter) {
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ],404);
        }

        $courseId = $request->input('course_id');
        if($courseId){
            $course = Course::find($courseId);
            if(!$course){
                return response()->json([
                    'status' => 'error',
                    'message' => 'course not found'
                ],404);
            }
        }

        $chapter->fill($data);
        $chapter->save();

        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }
}
