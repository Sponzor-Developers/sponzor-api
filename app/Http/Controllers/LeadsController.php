<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\JsonController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ActiveCampaningController;

class LeadsController extends Controller
{

    
    public function index(Request $request)
    {
        $result = (new ActiveCampaningController)->getFields(); 
        return JsonController::return('success', 200, '', $result);
    }

    public function list(Request $request)
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
        $lead = DB::table('leads')->where('user_id', $user)->first();
        $listleads = json_decode($lead->leads);
        $leads = DB::table('contacts')->whereIn('id', $listleads)->paginate(10);
        return JsonController::return('success', 200, '', ['leads' => $leads]);
    }



    // download
    public function downloadAll(Request $request)
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
        $lead = DB::table('leads')->where('user_id', $user)->first();
        $listleads = json_decode($lead->leads);
        $leads = DB::table('contacts')->whereIn('id', $listleads)->get()->toArray();
        $filename = 'leads.csv';
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array('Nome', 'Email', 'telefone', 'Cargo', 'Segmento', 'Empresa', 'Tamanho', 'Departamento', 'Pais'));
        foreach($leads as $lead)
        {
            fputcsv($handle, array($lead->nome, $lead->email, $lead->telefone, $lead->cargo, $lead->segmento, $lead->empresa, $lead->tamanho, $lead->departamento, $lead->pais));
        }
        fclose($handle);
        $data = file_get_contents($filename);
        $base64 = base64_encode($data);
        return JsonController::return('success', 200, '', ['filename' => $filename, 'crypto' => 'data:text/csv;base64,', 'hash' => $base64]);
    }

    //download selected
    public function downloadSelected(Request $request)
    {
        $data = $request->validate([
            'leads' => 'required|array',
            'leads.*' => 'required|integer'
        ]);
        $user = auth()->user()->id;
        $segmentation = auth()->user()->segmentation;
        if(!$segmentation)
        {
            return JsonController::return('error', 400, 'Você não possui segmentação');
        }
        $lead = DB::table('leads')->where('user_id', $user)->first();
        $listleads = json_decode($lead->leads);
        $leads = DB::table('contacts')->whereIn('id', $listleads)->whereIn('id', $data['leads'])->get()->toArray();
        $filename = 'leads.csv';
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array('Nome', 'Email', 'telefone', 'Cargo', 'Segmento', 'Empresa', 'Tamanho', 'Departamento', 'Pais'));
        foreach($leads as $lead)
        {
            fputcsv($handle, array($lead->nome, $lead->email, $lead->telefone, $lead->cargo, $lead->segmento, $lead->empresa, $lead->tamanho, $lead->departamento, $lead->pais));
        }
        fclose($handle);
        $data = file_get_contents($filename);
        $base64 = base64_encode($data);
        return JsonController::return('success', 200, '', ['filename' => $filename, 'crypto' => 'data:text/csv;base64,', 'hash' => $base64]);
    }

    public function filter(Request $request,)
    {
        $data = $request->validate([
            'cargo' => 'array',
            'departamento' => 'array',
            'segmento' => 'array',
            'tamanho' => 'array',
            'pais' => 'array',
        ]);
        $user = auth()->user()->id;
        $segmentation = auth()->user()->segmentation;
        if(!$segmentation)
        {
            return JsonController::return('error', 400, 'Você não possui segmentação');
        }
        $leads = DB::table('leads')->where('user_id', $user)->first();
        $contacts = DB::table('contacts');
        foreach($data as $key => $value)
        {
            if($value)
            {
                $contacts->whereIn($key, $value);
            }
        }
        $contacts->whereIn('id', json_decode($leads->leads));
        $contacts = $contacts->paginate(10);
        return JsonController::return('success', 200, '', ['leads' => $contacts]);
    }

}
