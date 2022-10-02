<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\JsonController;
use Illuminate\Support\Facades\DB;

class LeadsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user()->id;
        $segmentation = auth()->user()->segmentation;
        if(!$segmentation)
        {
            return JsonController::return('error', 400, 'Você não possui segmentação');
        }
        $leads_process = DB::table('leads')->where('user_id', $user)->first();
        if(!$leads_process)
        {
            return JsonController::return('error', 400, 'Nenhum lead encontrado', ['leads' => null]);
        }
        $leads = json_decode($leads_process->leads, true);
        return JsonController::return('success', 200, 'Leads listados com sucesso', ['leads' => $leads]);
    }

    // download
    public function download(Request $request)
    {
        $user = auth()->user()->id;
        $segmentation = auth()->user()->segmentation;
        if(!$segmentation)
        {
            return JsonController::return('error', 400, 'Você não possui segmentação');
        }
        $leads_process = DB::table('leads')->where('user_id', $user)->first();
        if(!$leads_process)
        {
            return JsonController::return('error', 400, 'Nenhum lead encontrado', ['leads' => null]);
        }
        $leads = json_decode($leads_process->leads, true);
        $file = fopen('leads.csv', 'w');
        fputcsv($file, array_keys($leads[0]));
        foreach($leads as $lead)
        {
            fputcsv($file, $lead);
        }
        fclose($file);
        return response()->download('leads.csv');
    }

    //download selected
    public function downloadSelected(Request $request)
    {
        $user = auth()->user()->id;
        $segmentation = auth()->user()->segmentation;
        if(!$segmentation)
        {
            return JsonController::return('error', 400, 'Você não possui segmentação');
        }
        $leads_process = DB::table('leads')->where('user_id', $user)->first();
        if(!$leads_process)
        {
            return JsonController::return('error', 400, 'Nenhum lead encontrado', ['leads' => null]);
        }
        $leads = json_decode($leads_process->leads, true);
        $file = fopen('leads.csv', 'w');
        fputcsv($file, array_keys($leads[0]));
        foreach($leads as $lead)
        {
            if($lead['selected'] == true)
            {
                fputcsv($file, $lead);
            }
        }
        fclose($file);
        return response()->download('leads.csv');
    }
}
