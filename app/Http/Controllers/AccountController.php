<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AccountController extends Controller
{
    //This method show the user registration page
    public function registration()
    {
        return view('front.account.registration');
    }
    //This method will save a user
    public function processRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|same:confirm_password',
            'confirm_password' => 'required'
        ]);

        if ($validator->passes()) {

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'You have registered successfully');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

    }

    //This method show the user Login page
    public function login()
    {
        return view('front.account.login');
    }

    public function authencate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->passes()) {
            //Matchin the database value and user entered value
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                //if database value and user entered value is mathched the return to the profile page
                return redirect()->route('account.profile');

            } else {

                return redirect()->route('account.login')->with('error', 'Invalid email or password');
            }
        } else {
            return redirect()
                ->route('account.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
    }

    public function profile()
    {
        //echo Auth::user() -> password;
        //Finding the user id from database
        $id = Auth::user()->id;
        //dd($id);
        //Fetchin the all the details of user
        $user = User::where('id', $id)->first();

        return view('front.account.profile', [
            'user' => $user

        ]);
    }

    public function updateProfile(Request $request)
    {
        $id = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:10|max:50',
            'email' => 'required|email|unique:users,email,' . $id . ',id'

        ]);

        if ($validator->passes()) {

            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->designation = $request->designation;
            $user->save();

            session()->flash('success', 'Profile updated successfully');

            return response()->json([
                'status' => true,
                'error' => [],
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function updateProfilePic(Request $request)
    {
        // dd($request->all());
        //image validation

        $id = Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'image' => 'required|image'
        ]);

        if ($validator->passes()) {

            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = $id . '-' . time() . '.' . $ext; //generate the unique name of image
            $image->move(public_path('/profile_pic/'), $imageName);

            // Creating the smalla thumbnail
            $sourcePath = public_path('/profile_pic/' . $imageName);

            // create new image instance (800 x 600)
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($sourcePath);

            // crop the best fitting 5:3 (600x360) ratio and resize to 600x360 pixel
            $image->cover(150, 150);
            $image->toPng()->save(public_path('/profile_pic/thumb/' . $imageName));

            //Deleting the old pic
            File::delete(public_path('/profile_pic/thumb/' . Auth::user()->image));
            File::delete(public_path('/profile_pic/' . Auth::user()->image));

            User::where('id', $id)->update(['image' => $imageName]);



            session()->flash('success', "Image Uploaded Successfully");

            return response()->json([
                'status' => true,
                'errors' => [],
            ]);


        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

    }

    public function createJob()
    {

        $categories = Category::orderBy('name', 'ASC')->where('status', 1)->get();

        $jobTypes = JobType::orderBy('name', 'Asc')->where('status', 1)->get();

        return view('front.account.job.create', [
            'categories' => $categories,
            'jobTypes' => $jobTypes,
        ]);
    }

    public function saveJob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:75',
        ]);

        if ($validator->passes()) {

            $job = new Job();
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->user_id = Auth::user()->id;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsility = $request->responsibility;
            $job->qualification = $request->qualifications;
            $job->keywords = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->website;
            $job->save();

            session()->flash('success', 'Job added successfully');

            return response()->json([
                'status' => true,
                'errors' => [],
            ]);


        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function myJobs(){

        $jobs = Job::where('user_id', Auth::user()->id)->with('jobType')->orderBy('created_at', 'DESC')->paginate(10);

        //dd($jobs);

        return view('front.account.job.my-jobs', [

            'jobs' => $jobs,

            ]);
    }

    public function editJob(Request $request, $id){
       // dd($id);

       $job = Job::where([
        'user_id' => Auth::user()->id,
        'id' => $id
        ])->first(); // this first method does not allow to change the id from the url

        //if user tried to  access another users job or if there is no such job in db then redirect him back with a message
        if($job == null){
            abort(404);
        }

        $categories = Category::orderBy('name', 'ASC')->where('status', 1)->get();

        $jobTypes = JobType::orderBy('name', 'Asc')->where('status', 1)->get();

        return view('front.account.job.edit', [
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'job' => $job,
            ]);
    }

    public function updateJob(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:75',
        ]);

        if ($validator->passes()) {

            //updating the job with the help of job id
            $job = Job::find($id);

            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->user_id = Auth::user()->id;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsility = $request->responsibility;
            $job->qualification = $request->qualifications;
            $job->keywords = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->website;
            $job->save();

            session()->flash('success', 'Job updated successfully');

            return response()->json([
                'status' => true,
                'errors' => [],
            ]);


        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function  deleteJob(Request $request){

     $job =  Job::where([
            'user_id' => Auth::user()->id,
            'id' => $request->jobId,
            ])->first();

            if($job == null){
                session()->flash('error', 'Either   job deleted or not found');
                return response()->json([
                    'status' => true
                    ]);
            }

            job::where('id', $request->jobId)->delete();
            session()->flash('success', 'Job Deleted Successfully');
            return response()->json([
                'status' => true
                ]);

    }

    public function myJobApplication(){

       $jobApplications =  JobApplication::where('user_id', Auth::user()->id)
       ->with('job', 'job.JobType', 'job.applications')
       ->orderBy('created_at','DESC')
       ->paginate(10);
       //dd( $jobs);
        return view('front.account.job.my-job-applications', [
            'jobApplications' =>  $jobApplications ,
            ]);
    }

    public function removeJobs(Request $request){
        $jobApplication = JobApplication::where([
            'id' => $request->id,
             'user_id' => Auth::user()->id
             ])->first();

    if($jobApplication == null){
        session()->flash('error', 'Job application not found');
            return response()->json([
                        'status' => false,
                       ]);
    }
    JobApplication::find($request->id)->delete();
    session()->flash('success', 'Job application remove successfully');
            return response()->json([
                        'status' => true,
                       ]);
    }

    public function savedJobs(){

        $savedJobs= SavedJob::where([
                    'user_id' => Auth::user()->id,
        ])->with('job', 'job.JobType', 'job.applications')
        ->orderBy('created_at','DESC')
        ->paginate(10);

        return view('front.account.job.saved-jobs', [
            'savedJobs' =>  $savedJobs ,
            ]);
    }


    public function removeSavedJob(Request $request){
        $savedJob = SavedJob::where([
            'id' => $request->id,
             'user_id' => Auth::user()->id
             ])->first();

    if($savedJob == null){
        session()->flash('error', 'Job not found');
            return response()->json([
                        'status' => false,
                       ]);
    }
    SavedJob::find($request->id)->delete();
    session()->flash('success', 'Job remove successfully');
            return response()->json([
                        'status' => true,
                       ]);
    }

    public function updatePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
            ]);

            if($validator->fails()){

            return response() ->json([
                'status' => false,
                'errors' => $validator->errors(),
                ]);

            }

            if(Hash::check($request->old_password, Auth::user() -> password) == false){
                session()->flash("error", "Your old  password is incorrect");
                return response() ->json([
                'status' => true,
                ]);
            }

            $user = User::find(Auth::user()->id);
            $user->password = Hash::make($request->new_password);
            $user->save();

            session()->flash("success", "Password updated successfully");
                return response() ->json([
                'status' => true,
                ]);
    }


}
