<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    public function updateProfilePic(Request $request){
           // dd($request->all());
           //image validation
        $validator = Validator::make($request->all(), [
            'image' => 'required|image'
            ]);

            if($validator->passes()){

            }else{
                return response()->json([
                    'status' => 'false',
                    'errors' => $validator->errors(),
                    ]);
            }

    }

}
