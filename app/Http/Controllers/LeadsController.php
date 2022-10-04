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
        $leads = (new ActiveCampaningController)->list(json_decode($leads_process->leads));
        // paginate
        $item_per_page = 10;
        $page_number = $request->page ? $request->page : 1;
        $offset = ($page_number - 1) * $item_per_page;
        $total_records = count($leads);
        $total_pages = ceil($total_records / $item_per_page);
        $leads = array_slice($leads, $offset, $item_per_page);
        return JsonController::return('success', 200, '', [
            'current_page' => $page_number, 
            'leads' => $leads,
            'first_page_url' => url('/dashboard/leads/list?page=1'),
            'from' => $offset + 1,
            'last_page' => $total_pages,
            'last_page_url' => url('/dashboard/leads/list?page='.$total_pages),
            'next_page_url' => $page_number < $total_pages ? url('/dashboard/leads/list?page='.($page_number + 1)) : null,
            'path' => url('/dashboard/leads/list'),
            'per_page' => $item_per_page,
            'prev_page_url' => $page_number > 1 ? url('/dashboard/leads/list?page='.($page_number - 1)) : null,
            'to' => $offset + $item_per_page,
            'total' => $total_records
        ]);
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
        $leads = (new ActiveCampaningController)->list(json_decode($leads_process->leads));
        $filename = 'leads.csv';
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array('Nome', 'Email', 'Cargo', 'Empresa', 'Departamento'));
        foreach($leads as $lead)
        {
            fputcsv($handle, array($lead['firstName'], $lead['email'], $lead['cargo'], $lead['empresa'], $lead['departamento']));
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
        $segmentation = auth()->user()->segmentation;
        if(!$segmentation)
        {
            return JsonController::return('error', 400, 'Você não possui segmentação');
        }
        $leads = (new ActiveCampaningController)->list($data['leads']);
        $filename = 'leads.csv';
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array('Nome', 'Email', 'Cargo', 'Empresa', 'Departamento'));
        foreach($leads as $lead)
        {
            fputcsv($handle, array($lead['firstName'], $lead['email'], $lead['cargo'], $lead['empresa'], $lead['departamento']));
        }
        fclose($handle);
        $data = file_get_contents($filename);
        $base64 = base64_encode($data);
        return JsonController::return('success', 200, '', ['filename' => $filename, 'crypto' => 'data:text/csv;base64,', 'hash' => $base64]);
    }

    public function filter(Request $request,)
    {
        return JsonController::return('success', 200, 'Filtros', [
            'leads' => []
        ]);
    }

}
