# Basic Auth with email confirmation and forget password

## Starting

laravel new LivewireAuth 
composer require laravel/ui 
php artisan ui bootstrap --auth 
composer require livewire/livewire 
npm install
npm run dev 
php artisan migrate  

## Configuration

Route

```php
Auth::routes(['login' => false,'register' => false, 'verify' =>true]);

route::middleware('guest')->group(function(){
    Route::get('/login',Login::class)->name('login');
    Route::get('/register',Register::class)->name('register');
});
```

app.blade

```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

        @stack('css')
    </head>
    <body class="antialiased">
        <div class="m-3 relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/home') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Home</a>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout').submit();">Logout</a>

                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="max-w-7xl mx-auto p-6 lg:p-8">

                {{-- {{$slot}} --}}
                @yield('content')

                <div class="flex justify-center">
                </div>

                <div class="mt-16">
                </div>

                <div class="flex justify-center mt-16 px-0 sm:items-center sm:justify-between">
                    <div class="text-center text-sm sm:text-left">
                        &nbsp;
                    </div>

                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 sm:text-right sm:ml-0">
                        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

        @stack('js')
    </body>
</html>
```

env

```php
DB_DATABASE=livewire_auth

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=693fdb15e153ec
MAIL_PASSWORD=2ab358b5eaaf8c
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@laravel.run"
MAIL_FROM_NAME="${APP_NAME}"
```

User

```php
 implements MustVerifyEmail
```

## Login

php artisan make:livewire Auth/Login 

Login

```php
<?php

namespace App\Livewire\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Login extends Component
{
    public $email, $password, $remember;
    public function render()
    {
        // return view('livewire.auth.login')->layout('layouts.app');
        return view('livewire.auth.login')->extends('layouts.app')->section('content');
    }

    public function rules(){
        return [
            'email' =>  ['required','email'],
            'password'  =>  ['required'],
        ];
    }

    public function loginUser(){
        $this->validate();
        $throttleKey = strtolower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey,5)) {
            $this->addError('email',__('auth.throttle',[
                'seconds' => RateLimiter::availableIn($throttleKey)
            ]));
            return null;
        }
        if (!Auth::attempt($this->only(['email','password']), $this->remember)) {
            RateLimiter::hit($throttleKey);
            $this->addError('email',__('auth.failed'));
            return null;
        }

        return redirect()->to(RouteServiceProvider::HOME);
    }
}

```

login

```php
<div>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Login</div>
                <div class="card-body">
                    <form wire:submit.prevent='loginUser'>
                        <div class="mb-3">
                            <label for="email" class="form-label">Alamat Email</label>
                            <input for="email" class="form-control" @error('email') is-invalid @enderror id="email" placeholder="name@laravel.run" wire:model.defer='email'>
                            @error('email')
                                <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Kata Sandi</label>
                            <input for="password" class="form-control"  @error('password') is-invalid @enderror id="password" placeholder="*******" wire:model.defer='password'>
                            @error('password')
                                <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" wire:model.defer='remember'>
                                <label class="form-check-label" for="remember">
                                  Remember me
                                </label>
                              </div>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Masuk</button>
                        </div>
                        <a href="{{route('password.request')}}" class="d-block text-primary">Lupa Kata Sandi?</a>
                        <a href="{{route('register')}}" class="text-primary">Belum punya akun?</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

```

## Register

php artisan livewire:make Auth/Register

Register

```php
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

```

Register

```php
<div>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Register</div>
                <div class="card-body">
                    <form wire:submit.prevent='registerUser'>
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" @error('name') is-invalid @enderror id="name" placeholder="Jhon Deo" wire:model.defer="name">
                            @error('name')
                                <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Alamat Email</label>
                            <input type="email" class="form-control" @error('email') is-invalid @enderror id="email" placeholder="name@laravel.run" wire:model.defer='email'>
                            @error('email')
                                <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Kata Sandi</label>
                            <input type="password" class="form-control"  @error('password') is-invalid @enderror id="password" placeholder="*******" wire:model.defer='password'>
                            @error('password')
                                <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Ulangi Kata Sandi</label>
                            <input type="password_confirmation" class="form-control"  @error('password_confirmation') is-invalid @enderror id="password_confirmation" placeholder="*******" wire:model.defer='password_confirmation'>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Daftar</button>
                        </div>
                        <a href="{{route('login')}}" class="text-primary">Sudah punya akun?</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

```

## Email

php artisan make:mail WelcomeEmail

WelcomeEmail

```php
<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    private User $user;
    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thanks for joining '.config('app.name',''),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'user' => $this->user,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

```

emails/welcome

```php
<h1>Thanks for joining {{$user->name}}</h1>

<p>Checking</p>

```

