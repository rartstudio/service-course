<?php

namespace App\Http\Controllers;

use App\Chapter;
use App\Course;
use App\Mentor;
use App\MyCourse;
use App\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::query();

        $q = $request->query('q');
        $status = $request->query('status');

        $courses->when($q,function($query) use ($q){
            return $query->whereRaw("name LIKE '%".strtolower($q)."%'");
        });

        $courses->when($status,function($query) use ($status){
            return $query->where('status','=',$status);
        });

        return response()->json([
            'status' => 'success',
            'data' => $courses->with('images')->paginate(10)
        ]);
    }

    public function show($id)
    {
        $course = Course::with(['mentor','images','chapters.lessons'])->find($id);
        if(!$course){
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ],404);
        }

        $reviews = Review::where('course_id','=',$id)->get()->toArray();

        if(count($reviews) > 0){
            //find column user_id from reviews array and userIds return array
            $userIds = array_column($reviews,'user_id');

            //call user service to get users data based on userIds
            $users = getUserByIds($userIds);

            // dd($users);

            //checking if status error (user service down)
            //if yes return empty array
            //if no set user data
            if($users['status'] === 'error'){
                $reviews = [];
            } else {
                foreach($reviews as $key => $review){
                    //search index array by id user 
                    $userIndex = array_search($review['user_id'],array_column($users['data'],'id'));
                    
                    //set new key value pair for user data in review
                    $reviews[$key]['users'] = $users['data'][$userIndex];
                }                
            }
        }

        $totalStudent = MyCourse::where('course_id','=',$id)->count();

        //one lessons == one video
        //get all related lesson with chapter and count it
        //format will relation_count
        $totalVideos = Chapter::where('course_id','=',$id)->withCount('lessons')->get()->toArray();

        //find column lesson_count from totalvideos and turn it to new array then sum it
        $finalTotalVideos = array_sum(array_column($totalVideos,'lessons_count'));

        $course['reviews'] = $reviews;
        $course['total_student'] = $totalStudent;
        $course['total_videos'] = $finalTotalVideos;

        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    public function create(Request $request)
    {
        //rule schema
        $rules = [
            'name' => 'required|string',
            'certificate' => 'required|boolean',
            'thumbnail' => 'string|url',
            'type' => 'required|in:free,premium',
            'status' => 'required|in:draft,published',
            'price' => 'integer',
            'level' => 'required|in:all-level,beginner,intermediate,advance',
            'mentor_id' => 'required|integer',
            'description' => 'string'
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

        //get data mentorid from request
        $mentorId = $request->input('mentor_id');
        $mentor = Mentor::find($mentorId);

        if(!$mentor){
            return response()->json([
                'status' => 'error',
                'message' => 'mentor not found'
            ], 404);
        }

        $course = Course::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    public function update(Request $request, $id)
    {
        //rule schema
        $rules = [
            'name' => 'string',
            'certificate' => 'boolean',
            'thumbnail' => 'string|url',
            'type' => 'in:free,premium',
            'status' => 'in:draft,published',
            'price' => 'integer',
            'level' => 'in:all-level,beginner,intermediate,advance',
            'mentor_id' => 'integer',
            'description' => 'string'
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

        $course = Course::find($id);
        if(!$course){
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ],404);
        }

        $mentorId = $request->input('mentor_id');
        if($mentorId){
           $mentor = Mentor::find($mentorId);
           if(!$mentor){
               return response()->json([
                   'status' => 'error',
                   'message' => 'mentor not found'
               ]);
           } 
        }

        $course->fill($data);
        $course->save();

        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    public function destroy($id)
    {   
        $course = Course::find($id);

        if(!$course){
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ],404);
        }

        $course->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'course deleted'
        ]);
    }
}
