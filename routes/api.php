<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\JsonController;


Route::get('/', function () {
    return JsonController::return('success', 200);
});
/**
 * Auth 
 */

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); // REGISTER
    Route::post('/login', [AuthController::class, 'login']); // LOGIN
    Route::post('/change-password', [AuthController::class, 'resetPassword']); // ENVIAR EMAIL PARA ALTERAR SENHA
    Route::post('/check-reset', [AuthController::class, 'checkTokenResetPassword']); // CONFIRMA SE O TOKEN DE ALTERAÇÃO DE SENHA É VÁLIDO
    Route::post('/reset', [AuthController::class, 'changePassword']);  // ALTERAR SENHA
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('/logout', [AuthController::class, 'logout']); // LOGOUT
    });
});

/**
 * DASHBOARD
 */

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'dashboard'], function () {

    /**
     * DASHBOARD / HOME
     */

    Route::get('/', [DashboardController::class, 'index']); // DASHBOARD
    Route::get('/event/{slug}', [DashboardController::class, 'show']);  // EVENTO SELECIONADO

    /**
     * DASHBOARD / ADMIN
     */
    Route::prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'index']);
        Route::post('/users', [AdminController::class, 'filter']);
        Route::post('/user', [AdminController::class, 'store']);
        Route::get('/user/{id}', [AdminController::class, 'show']);
        Route::put('/user/{id}', [AdminController::class, 'update']);
        Route::delete('/user/{id}', [AdminController::class, 'destroy']);
    });

    /**
     * DASHBOARD / USER
     */
    Route::get('/user', [UserController::class, 'index']); // INFO USER
    Route::put('/user', [UserController::class, 'update']); // UPDATE USER
    Route::delete('/user', [UserController::class, 'destroy']); // DELETE USER

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
