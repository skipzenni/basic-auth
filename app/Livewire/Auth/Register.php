<?php

namespace App\Livewire\Auth;

use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class Register extends Component
{
    public $name,$email,$password,$password_confirmation;
    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => ['required', 'confirmed'],
    ];

    public function registerUser(){
        $this->validate();
        $user = User::create([
            'name'  =>  $this->name,
            'email'  =>  $this->email,
            'password'  =>  bcrypt($this->password)
        ]);
        Auth::login($user, true);
        // Mail::to($user->email)->send(new WelcomeEmail($user));
        event(new Registered($user));
        return redirect()->to(RouteServiceProvider::HOME);
    }
    public function render()
    {
        // return view('livewire.auth.register')->layout('layouts.app');
        return view('livewire.auth.register')->extends('layouts.app')->section('content');
    }
}
