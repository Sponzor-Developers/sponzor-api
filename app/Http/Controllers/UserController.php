<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Models\User;
use App\Http\Controllers\ReturnController;



class UserController extends Controller
{
    /**
     * Lista informações do usuário
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        return JsonController::return('success', 200, 'Profile', ['user' => $request->user()]);
    }

    /**
     * Atualiza informações do usuário
     *
     * @param Request $request
     * @return void
     */
    public function update(Request $request)
    {
        return JsonController::return('success', 200, 'Profile updated', ['user' => $request->user()->update($request->all())]);
    }

    /**
     * Deleta usuário
     *
     * @param Request $request
     * @return void
     */
    public function destroy(Request $request)
    {
        return JsonController::return('success', 200, 'Profile deleted', ['user' => $request->user()->delete()]);
    }
}
