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
        return JsonController::return('success', 200, 'Dados usuario', ['user' => $request->user()]);
    }

    /**
     * Atualiza informações do usuário
     *
     * @param Request $request
     * @return void
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'email' => 'string|email|min:5|max:155',
            'name' => 'string|max:255',
            'enterprise' => 'string|max:255',
            'business' => 'string|max:255',
            'phone' => 'string|min:8|max:50',
        ]);
        return JsonController::return('success', 200, 'Dados atualizados', ['user' => DB::table('users')->where('id', $request->user()->id)->update($data)]);
    }

    /**
     * Deleta usuário
     *
     * @param Request $request
     * @return void
     */
    public function destroy(Request $request)
    {
        return JsonController::return('success', 200, 'Conta Excluida', ['user' => $request->user()->delete()]);
    }
}
