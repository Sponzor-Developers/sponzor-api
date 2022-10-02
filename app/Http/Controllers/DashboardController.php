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
        // db select inner join events in categories type text example [1,2,3] and events_categories
        $events = DB::table('events')->get();
        return JsonController::return('success', 200, 'Dashboard' , ['events' => $events]);
    }

    /**
     * Exibe o evento selecionado
     *
     * @param [type] $id
     * @return void
     */
    public function show($id)
    {
        
        return JsonController::return('success', 200, 'MVP', ['id' => $id]);
    }
}
