<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\JsonController;
use SplDoublyLinkedList;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{

    /**
     * Exibe todas as listas de eventos
     *
     * @return void
     */
    public function index()
    {        
        $events = DB::table('events')->get();
        // jsondecode $events['categories]
        foreach($events as $event)
        {
            $event->categories = json_decode($event->categories);
        }
        $events_categories = DB::table('events_categories')->get();



        return JsonController::return('success', 200, 'Listagem de eventos', ['categories' => $events_categories, 'events' => $events]);
    }

    /**
     * Exibe o evento selecionado
     *
     * @param [type] $id
     * @return void
     */
    public function show(Request $request, $slug)
    {
        $event = DB::table('events')->where('slug', $slug)->first();
        if(!$event)
        {
            return JsonController::return('error', 400, 'Evento não encontrado');
        }

        $leads = DB::table('contacts')->count();

        $sizeEnterprise = [
            '1' => DB::table('contacts')->where('tamanho', '1')->count() ?? 0,
            '2 a 10' => DB::table('contacts')->where('tamanho', '2 a 10')->count() ?? 0,
            '11 a 50' => DB::table('contacts')->where('tamanho', '11 a 50')->count() ?? 0,
            '51 a 100' =>  DB::table('contacts')->where('tamanho', '51 a 100')->count() ?? 0,
            '101 a 250' => DB::table('contacts')->where('tamanho', '101 a 250')->count() ?? 0,
            '251 a 500' => DB::table('contacts')->where('tamanho', '251 a 500')->count() ?? 0,
            '501 a 1000' => DB::table('contacts')->where('tamanho', '501 a 1000')->count() ?? 0,
            '1001 a 5000' => DB::table('contacts')->where('tamanho', '1001 a 5000')->count() ?? 0,
            '5001 a 10000' => DB::table('contacts')->where('tamanho', '5001 a 10000')->count() ?? 0,
            '+10000' => DB::table('contacts')->where('tamanho', 'Mais de 10.000')->count() ?? 0,
        ];

        $segments = DB::table('contacts')->select('segmento')->distinct()->get() ?? [];
        
        $segments = $segments->map(function($item, $key){
            if($item->segmento == null)
            {
                $item->segmento = 'Não informado';
            }
            $item->count = DB::table('contacts')->where('segmento', $item->segmento)->count();
            return $item;
        });

        $empresas = DB::table('contacts')->select('empresa')->distinct()->get('empresa') ?? [];
        $empresas = $empresas->map(function($item, $key){
            return $item->empresa;
        });
        $empresas = $empresas->toArray();
        $empresas = array_slice($empresas, 0, 10);

        $departamentos = DB::table('contacts')->select('departamento')->distinct()->get('departamento') ?? [];
        $departamentos = $departamentos->map(function($item, $key){
            return $item->departamento;
        });

        $cargos = DB::table('contacts')->select('cargo')->distinct()->get('cargo') ?? [];
        $cargos = $cargos->map(function($item, $key){
            return $item->cargo;
        });

        return JsonController::return('success', 200, 'MVP', ['slug' => $slug, 'event' => $event, 'total_leads' => $leads, 'tamanho_empresa' => $sizeEnterprise, 'segmentos' => $segments, 'empresas' => $empresas, 'departamentos' => $departamentos, 'cargos' => $cargos]);
    }
}
