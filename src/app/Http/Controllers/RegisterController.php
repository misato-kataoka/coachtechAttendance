<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;

class RegisterController extends Controller
{

    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request){
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password)
        ]);
        if (Auth::check()) {
            return redirect('/attendance');
        }

        return view('auth.login');
    }
}
