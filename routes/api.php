<?php
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', [HomeController::class, 'index']);
/**
 * Auth 
 */
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/reset', [AuthController::class, 'resetPassword']);
Route::get('/reset/{token}', [AuthController::class, 'checkToken']);
Route::post('/change-password', [AuthController::class, 'changePassword']);

/**
 * Auth Social
 */
// Google
 
Route::get('/auth/redirect', function () {
    return Socialite::driver('google')->redirect();
});
 
Route::get('/auth/callback', function () {
    $githubUser = Socialite::driver('github')->user();
    $user = User::updateOrCreate([
        'github_id' => $githubUser->id,
    ], [
        'name' => $githubUser->name,
        'email' => $githubUser->email,
        'github_token' => $githubUser->token,
        'github_refresh_token' => $githubUser->refreshToken,
    ]);
    Auth::login($user);
    return redirect('/dashboard');
});


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/check', [UserController::class, 'check']);
    Route::get('/perfil', [UserController::class, 'getPerfil']);
    Route::post('/perfil', [UserController::class, 'updatePerfil']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
