<?php
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\AppointmentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::group(['middleware' => 'auth'], function () {
    // Create a new appointment
    Route::post('/appointments', 'App\Http\Controllers\AppointmentController@store')->name('events.store');

    // Update an existing appointment
    Route::put('/appointments/{id}', 'App\Http\Controllers\AppointmentController@update')->name('events.update');

    // Delete an appointment
    Route::delete('/appointments/{id}', 'App\Http\Controllers\AppointmentController@destroy');

    // Retrieve a list of appointments
  
    Route::get('/calendar', 'App\Http\Controllers\AppointmentController@index')->name('calendar');
    Route::get('/api/calendar/events', 'App\Http\Controllers\AppointmentController@getEvents');
});
Route::middleware('auth')->get('/', function () {
    return view('calendar');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        if (Auth::user()->isDoctor) {
            return view('dashboard');
        } else {
            return view('calendar');
        }
    })->name('dashboard');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/appointment', function () {
        if (Auth::user()->isDoctor) {
            return view('appointment');
        } else {
            return view('calendar');
        }
    })->name('appointment');
});

//Seceratry Role

Route::post('/store-secreaty', [SecretaryController::class, 'store']);
//Calendar


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/appointments/{eventId}/authorization', [AppointmentController::class, 'checkAuthorization']);
require __DIR__.'/auth.php';
