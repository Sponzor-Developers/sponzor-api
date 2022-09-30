<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', [HomeController::class, 'index']);
/**
 * Auth 
 */

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); // REGISTER
    Route::post('/login', [AuthController::class, 'login']); // LOGIN
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/reset/{token}', [AuthController::class, 'checkToken']);
    Route::post('/reset', [AuthController::class, 'resetPassword']); //
    Route::get('/logout', [AuthController::class, 'logout']); // LOGOUT'
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('/logout', [AuthController::class, 'logout']); // LOGOUT
    });
});

/**
 * DASHBOARD
 */

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'dashboard'], function () {

    /**
     * DASHBOARD / MVP
     */

    Route::get('/', [DashboardController::class, 'mvp']);

    Route::get('/reports', [DashboardController::class, 'mvp']);

    /**
     * DASHBOARD / ADMIN
     */
    Route::prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/users/{id}', [AdminController::class, 'getUser']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    });

    /**
     * DASHBOARD / USER
     */

    Route::get('/user', [UserController::class, 'getPerfil']); // INFO USER
    Route::put('/user', [UserController::class, 'updatePerfil']); // UPDATE USER
    Route::delete('/user', [UserController::class, 'deletePerfil']); // DELETE USER

    /**
     * DASHBOARD / LEADS
     */

    Route::prefix('leads')->group(function () {
        Route::get('/leads', [UserController::class, 'getLeads']); // GET LEADS
        Route::get('/leads/{id}', [UserController::class, 'getLead']); // GET LEAD
        /**
         * DASHBOARD / LEADS / DOWNLOAD
         */
        // get all
        Route::get('/leads/download', [UserController::class, 'downloadLeads']); // DOWNLOAD LEADS
        // download all
        Route::post('/leads/download/all', [UserController::class, 'downloadLeadsByIds']); // DOWNLOAD ALL LEADS
        // download by ids
        Route::post('/leads/download/selected', [UserController::class, 'downloadLeadsByIds']); // DOWNLOAD LEADS BY IDS
    });

    /**
     * DASHBOARD / SEGMANTATION
     */

    Route::prefix('segmentation')->group(function () {
        //QUOTES
        Route::get('/', [UserController::class, 'getSegmentation']); // GET SEGMENTATION

        //filters
        Route::post('/filters', [UserController::class, 'getFilters']); // GET FILTERS

        // save segmentation
        Route::post('/save', [UserController::class, 'saveSegmentation']); // SAVE SEGMENTATION
    });
});
