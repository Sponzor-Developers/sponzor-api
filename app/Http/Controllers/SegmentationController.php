<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JsonController;
use App\Http\Controllers\ActiveCampaningController;


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
        $segmentation = $user->segmentation == 1 ? true : false;
        if($segmentation)
        {
            return JsonController::return('error', 400, 'Você já possui uma segmentação');
        }

        $custom = DB::table('plans_person')->where('user_id', $user->id)->first();

        $fields = (new ActiveCampaningController)->getFields();
        


        // verifica se o plano permite segmentação
        if($plan == 6)
        {
            return JsonController::return('error', 400, 'Seu plano não permite segmentação');
        }

        if($plan != 5)
        {
            $plan = DB::table('plans')->where('id', $plan)->first();
            $leads = 10000;
            $range = $leads * $plan->quota / 100;
            $min = round($range * 0.90);
            $max = round($range * 1.10);
            return JsonController::return('success', 200, '', 
            [
                'quota' => $range, 
                'min' => $min, 
                'max' => $max, 
                'fields' => $fields
            ]);
        }
        return JsonController::return('success', 200, '', ['quota' => $custom->quota, 'segmentation' => $segmentation, 'plan' => 'Personalizado', 'fields' => $fields]);
    }

    /**
     * Filter
     */

    public function filter(Request $request)
    {
          
    }

    /**
     * Save
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'leads' => 'required|array',
            'leads.*' => 'required|integer',
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
