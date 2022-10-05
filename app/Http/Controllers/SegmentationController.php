<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JsonController;
use App\Http\Controllers\ActiveCampaningController;
use Illuminate\Support\Facades\Http;



class SegmentationController extends Controller
{

    /**
     * Exibe todas as listas de eventos
     *
     * @return void
     */
    public function index(Request $request)
    {

        // verifica o plano do usuário
        $user = $request->user();
        $plan = $user->plan;
        // if($plan == 6)
        // {
        //     return JsonController::return('error', 400, 'Seu plano não permite segmentação', [
        //         'quota' => 0, 
        //         'min' => 0, 
        //         'max' => 0, 
        //         'fields' => []
        //     ]);
        // }


        $segmentation = $user->segmentation == 1 ? true : false;

        // if($segmentation)
        // {
        //     return JsonController::return('error', 400, 'Você já possui uma segmentação', [
        //         'quota' => 0, 
        //         'min' => 0, 
        //         'max' => 0, 
        //         'fields' => []
        //     ]);
        // }

        $custom = DB::table('plans_person')->where('user_id', $user->id)->first();


        $fields = [];
        $fields['cargo'] = DB::table('contacts')->where('user_id', $user->id)->where('cargo', '!=', '')->distinct()->pluck('cargo');
        $fields['departamento'] = DB::table('contacts')->where('user_id', $user->id)->where('departamento', '!=', '')->distinct()->pluck('departamento');
        $fields['segmento'] = DB::table('contacts')->where('user_id', $user->id)->where('segmento', '!=', '')->distinct()->pluck('segmento');
        $fields['tamanhoEmpresa'] = DB::table('contacts')->where('user_id', $user->id)->where('tamanhoEmpresa', '!=', '')->distinct()->pluck('tamanhoEmpresa');
        $fields['pais'] = DB::table('contacts')->where('user_id', $user->id)->where('pais', '!=', '')->distinct()->pluck('pais');

        // \

    



        // $fields = (new ActiveCampaningController)->getFields();



        // verifica se o plano permite segmentação

        if($plan != 5)
        {
            $plan = DB::table('plans')->where('id', $plan)->first();
            $leads = DB::table('contacts')->count();
            $range = $leads * $plan->quota / 100;
            $min = round($range * 0.90);
            $max = round($range * 1.10);
            return JsonController::return('success', 200, '', 
            [
                'quota' => round($range), 
                'min' => $min, 
                'max' => $max, 
                'fields' => $fields
            ]);
        }
        return JsonController::return('success', 200, '', 
        [
            'quota' => $custom->quota, 
            'min' => $custom->quota, 
            'max' => $custom->quota, 
            'fields' => $fields
        ]);
    }

    /**
     * Filter
     */

    public function filter(Request $request)
    {
        // cargo,departamento,segmento,tamanho,pais e interações
        $data = $request->validate([
            'cargo' => 'array',
            'departamento' => 'array',
            'segmento' => 'array',
            'tamanho' => 'array',
            'pais' => 'array',
            'interacoes' => 'array',
        ]);
        if(!$data)
        {
            return JsonController::return('error', 400, 'Dados inválidos');
        }
        $contacts = DB::table('contacts');

        // fazer um foraech de data e fazer um where in
        foreach($data as $key => $value)
        {
            if($value)
            {
                $contacts->whereNotIn($key, $value);
            }
        }

        // retorna a quantidade de contatos
        $contacts = $contacts->get('id');
        $total = count($contacts);
        $ids = [];
        foreach($contacts as $contact)
        {
            $ids[] = $contact->id;
        }
        return JsonController::return('success', 200, '', ['total' => $total, 'leads' => $ids]);
    }








    /**
     * Save
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'leads' => 'required|array',
            'leads.*' => 'required|string',
        ]);
        if(!$data)
        {
            return JsonController::return('error', 400, 'Dados inválidos');
        }
        // mudar o segmentação para 1
        $user = $request->user();
        $user->segmentation = 1;
        $user->save();
        // salvar os leads
        $leads = $data['leads'];
        DB::table('leads')->insert([
            'user_id' => $user->id,
            'event_id' => 1,
            'leads' => json_encode($leads, JSON_NUMERIC_CHECK)
        ]);
        return JsonController::return('success', 200, 'Segmentação salva com sucesso');
    }
}
