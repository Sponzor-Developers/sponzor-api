<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\JsonController;
use App\Http\Controllers\SegmentationController;
use App\Http\Controllers\LeadsController;

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
        Route::get('/', [AdminController::class, 'index']); // LISTAR USUÁRIOS
        Route::get('/users', [AdminController::class, 'list']);
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
     * DASHBOARD / SEGMANTATION
     */

    Route::prefix('segmentation')->group(function () {
        Route::get('/', [SegmentationController::class, 'index']); // GET SEGMENTATION
        Route::post('/filter', [SegmentationController::class, 'filter']); // GET FILTERS
        Route::post('/save', [SegmentationController::class, 'save']); // SAVE SEGMENTATION
    });


        /**
     * DASHBOARD / LEADS
     */
    Route::prefix('leads')->group(function () {
        Route::get('/', [LeadsController::class, 'index']); // GET LEADS
        Route::post('/filter', [LeadsController::class, 'filter']); // GET FILTERS
        Route::post('/download', [LeadsController::class, 'download']); // DOWNLOAD LEADS
        Route::post('/download/selected', [LeadsController::class, 'downloadSelected']); // DOWNLOAD LEADS BY IDS
    });
});
