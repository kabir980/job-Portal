<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;

class JobsController extends Controller
{
    //This method will show thw jobs page
    public function index(Request $request)
    {

        $categories = Category::where('status', 1)->get();
        $jobTypes = JobType::where('status', 1)->get();

        $jobs = Job::where('status', 1);

        //search using keywords
        if(!empty($request->keyword)){

            $jobs = $jobs->where(function($query) use ($request){
                $query->orWhere('title','like','%'. $request->keyword .'%');
                $query->orWhere('keywords','like','%'. $request->keyword .'%');
            });
        }

        //search  using location
        if(!empty($request->location)){
            $jobs = $jobs->where('location', $request->location);
        }

        //searching using category
        if (!is_null($request->category)) {
            $jobs = $jobs->where('category_id', $request->category);
        }

        //search using JobType
        $jobTypeArray = [];

        if (!is_null($request->jobType)) {

            //converting into the array
           $jobTypeArray  = explode(',', $request->jobType);

            $jobs = $jobs->whereIn('job_type_id', $jobTypeArray );
        }

        //searching using experience
        if (!is_null($request->experience)) {
            $jobs = $jobs->where('experience', $request->experience);
        }

        $jobs = $jobs->with('jobType')->orderBy('created_at', 'DESc')->paginate(9);

        return view('front.jobs', [

            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'jobs' => $jobs,
            'jobTypeArray' => $jobTypeArray
        ]);
    }
}
