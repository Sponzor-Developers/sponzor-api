<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\JsonController;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return JsonController::return('success', 200, 'Listagem de usuários', ['users' => DB::table('users')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return JsonController::return('success', 200, 'User created', ['user' => DB::table('users')->insert($request->all())]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return JsonController::return('success', 200, 'User', ['user' => DB::table('users')->where('id', $id)->first()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        // name, enterprise, business, plan, quota, phone, event
        $request = $request->validate([
            'email' => 'string|email|min:5|max:155',
            'name' => 'string|min:3|max:155',
            'enterprise' => 'string|min:3|max:155',
            'business' => 'string|min:3|max:155',
            'plan' => 'string',
            'quota' => 'string',
            'phone' => 'string|min:8|max:50',
        ]);
        // se houver erro de validação, retorna o erro
        if (!$request) {
            return JsonController::return('error', 400, 'Dados inválidos');
        }
        // se houver plano 
        if(isset($request['plan']))
        {
            if($request['plan'] != '6')
            {
                $delete = DB::table('plans_person')->where('user_id', $id)->delete();
                if(!$delete)
                {
                    return JsonController::return('error', 400, 'Erro ao atualizar quota');
                }
            }
            $this->planIsValid($request['plan']);
            if($request['plan'] == '6' && !isset($request['quota']))
            {
                return JsonController::return('error', 400, 'Quota não informada');
            }
        }
        // se houver quota e se é valida
        if(isset($request['quota'] )) {
            $this->quotaIsValid($request['quota']);
            $this->quotaExists($request['quota'], $id, $request['plan']);
            unset($request['quota']);
        }
        $request['updated_at'] = date('Y-m-d H:i:s'); // atualiza a data de atualização
        return JsonController::return('success', 200, 'User updated', ['user' => DB::table('users')->where('id', $id)->update($request)]);
    }

    /**
     * Verificar se a quota existe e se pertence ao usuário que está sendo atualizado
     *
     * @param integer $quota
     * @param integer $id
     * @return void
     */
    private function quotaExists(string $quota = '0', string $user_id = '0', string $plan = '1')
    {
        $select = DB::table('plans_person')->where('user_id', $user_id)->first();
        if (!$select) {
            $insert = DB::table('plans_person')->insert([
                'quota' => $quota,
                'user_id' => $user_id,
            ]);
            if (!$insert) {
                return JsonController::return('error', 400, 'Erro ao atualizar quota');
            }
            return;
        }
        $update = DB::table('plans_person')->where('user_id', $user_id)->update(['quota' => $quota]);
        if (!$update) {
            return JsonController::return('error', 400, 'Erro ao atualizar quota');
        }
        return;
    }

    /**
     * Verificar se a quota é válida
     *
     * @param integer $quota
     * @return void
     */
    private function quotaIsValid(string $quota)
    {
        if($quota > 0 && $quota <= 100) {
            return JsonController::return('error', 400, 'Quota inválida');
        }
    }

    /**
     * verficar se o plano é válido
     */
    private function planIsValid(string $plan)
    {
        $plano = DB::table('plans')->where('id', $plan)->first();
        if (!$plano) {
            return JsonController::return('error', 400, 'Plano inválido');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return JsonController::return('success', 200, 'User deleted', ['user' => DB::table('users')->where('id', $id)->delete()]);
    }
}
