<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JsonController;


class SegmentationController extends Controller
{

    /**
     * Exibe todas as listas de eventos
     *
     * @return void
     */
    public function index(Request $request)
    {

        $user = $request->user();
        $plan = $user->plan;
        $custom = DB::table('plans_person')->where('user_id', $user->id)->first();
        $fields = [];
        $fields['cargo'] = DB::table('contacts')
        ->select('cargo')
        ->distinct()
        ->orderBy('cargo', 'ASC')
        ->get('cargo')
        ->map(function($item, $key){
            if($item->cargo != null){
                return $item->cargo;
            }
        })->filter()->values() ?? [];

        $fields['departamento'] = DB::table('contacts')
        ->select('departamento')
        ->distinct()
        ->orderBy('departamento', 'ASC')
        ->get('departamento')
        ->map(function($item, $key){
            if($item->departamento != null)
            {
                return $item->departamento;
            }
        })->filter()->values() ?? [];

        $fields['segmento'] =  DB::table('contacts')
        ->select('segmento')
        ->distinct()
        ->orderBy('segmento', 'ASC')
        ->get('segmento')
        ->map(function($item, $key){
            if($item->segmento != null)
            {
                return $item->segmento;
            }
        })->filter()->values() ?? [];

        $fields['tamanho'] = [
            '1',
            '2 a 10',
            '11 a 20',
            '21 a 50',
            '51 a 100',
            '101 a 500',
            '501 a 1000',
            '1001 a 5000',
            '5001 a 10000',
            'mais de 10000'
        ];


        $fields['pais'] = DB::table('contacts')
        ->select('pais')
        ->distinct()
        ->orderBy('pais', 'ASC')
        ->get('pais')
        ->map(function($item, $key){
            if($item->pais != null)
            {
                return $item->pais;
            }
        })->filter()->values() ?? [];


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
                $contacts->whereIn($key, $value);
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
