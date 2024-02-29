<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {

        //fetching the category from the datavase category with the help of Category model
        $categories = Category::where('status', 1)->orderBy('name', 'ASC')->take(8)->get();
        // Search all the category from the database
         $newCategories =  Category::where('status', 1)->orderBy('name', 'ASC')->take(8)->get();

        //fetching the featured jobs from the database
        //teke method is used  to get the first 6 records from the database
        $featuredJobs = Job::where('status', 1)->orderBy('created_at', 'DESC')->with('jobType')->where('isFeatured', 1)->take(6)->get();

        //fetching the latest job from the  database by using orderby and descending in id column.
        $latestJobs = Job::where('status', 1)->with('jobType')->orderBy('created_at', 'DESC')->take(6)->get();

        return view("front.home", [
            'categories' => $categories,
            'featuredJobs' => $featuredJobs,
            'latestJobs' => $latestJobs,
            'newCategories' => $newCategories
        ]);
    }


}
