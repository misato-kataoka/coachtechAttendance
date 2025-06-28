<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //public function store(){
        /*$user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password)
        ]);
        event(new Registered($user));
        Auth::login($user);*/

        //if (Auth::check()) {
         //   return redirect('/attendance');
        //}

       //return view('auth.login');
    //}

    public function index()
    {
        return view('index');
    }

    public function loginUser(LoginRequest $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/attendance');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。',
        ])->withInput();
    }

    public function logout(){
        return view('auth.login');
    }
}
