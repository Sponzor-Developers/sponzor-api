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
        return JsonController::return('success', 200, 'MVP', ['event' => $slug]);
    }
}
