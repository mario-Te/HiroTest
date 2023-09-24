<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class SecretaryController extends Controller
{
      public function create()
    {
        return view('secretary.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
          
        ]);
       $user->doctor_id= Auth::id();
       $user->isDoctor=0;
       $user->save();
          
        
        return redirect()->back()->with('success', 'Secretary created successfully');
    }
}
