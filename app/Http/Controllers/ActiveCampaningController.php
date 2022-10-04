<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\JsonController;
use SplDoublyLinkedList;

class ActiveCampaningController extends Controller
{

    const LIST = [
        'firstName' => FILTER_DEFAULT,
        'email' => FILTER_DEFAULT,
        'phone' => FILTER_DEFAULT
    ];

    function __construct()
    {
        $this->api_url = config('services.activecampaning.api_url');
        $this->api_token = config('services.activecampaning.api_token');
    }
 
    /**
     * Exibir um contato via id
     * 
     */
    public function list(array $ids)
    {
        $response = Http::withHeaders([
            'Api-Token' => $this->api_token
        ])->get($this->api_url . '/contacts', [
            'ids' => $ids
        ]);
        if($response->status() == 200)
        {
            $response = json_decode($response->body(), true)['contacts'];
            foreach($response as $key => $contact)
            {
                $response[$key] = array_filter($contact, function($key) {
                    return array_key_exists($key, self::LIST);
                }, ARRAY_FILTER_USE_KEY);
                $response[$key]['id'] = intval($contact['id']);
                $response[$key]['whatsapp'] = 'https://api.whatsapp.com/send?phone=55' . preg_replace('/[^0-9]/', '', $contact['phone']);
                $response[$key]['data'] = date('d/m/Y', strtotime($contact['cdate']));
                $fieldValues = json_decode(Http::withHeaders([
                    'Api-Token' => $this->api_token
                ])->get($this->api_url . '/contacts/'.$contact['id'].'/fieldValues')->body(), true)['fieldValues'];
                foreach($fieldValues as $fieldValue)
                {
                    if($fieldValue['field'] == 4)
                    {
                        $response[$key]['cargo'] = $fieldValue['value'];
                    }else{
                        $response[$key]['cargo'] = 'Não informado';
                    }
                    if($fieldValue['field'] == 7)
                    {
                        $response[$key]['empresa'] = $fieldValue['value'];
                    }else{
                        $response[$key]['empresa'] = 'Não informado';
                    }
                    if($fieldValue['field'] == 5)
                    {
                        $response[$key]['departamento'] = $fieldValue['value'];
                    }else{
                        $response[$key]['departamento'] = 'Não informado';
                    }
                }
            }
            return $response;
        }
        return null;
    }

    /**
     * Páginação
     * 
     */
    public function pagination(array $contacts)
    {
        $page = request()->page;
        $per_page = request()->per_page;
        $total = count($contacts);
        $pages = ceil($total / $per_page);
        $contacts = array_slice($contacts, ($page - 1) * $per_page, $per_page);
        return [
            'total' => $total,
            'pages' => $pages,
            'page' => $page,
            'per_page' => $per_page,
            'contacts' => $contacts
        ];
    }

    /**
     * GET FIELDS
     * 
     */
    
    public function getFields()
    {
        $response = Http::withHeaders([
            'Api-Token' => $this->api_token
        ])->get($this->api_url . '/fields?limit=2147483647');
        if($response->status() == 200)
        {
            $body = $response->json()['fieldOptions'];
            $fields = [];
            $fields['cargo'] = [];
            $fields['departamento'] = [];
            $fields['segmento'] = [];
            $fields['tamanhoEmpresa'] = [];
            $fields['pais'] = [];
            foreach($body as $field)
            {
                if($field['field'] == 4){
                    $fields['cargo'][] = $field['label'];
                }
                if($field['field'] == 5){
                    $fields['segmento'][] = $field['label'];
                }
                if($field['field'] == 8){
                    $fields['tamanhoEmpresa'][] = $field['label'];
                }
                if($field['field'] == 158){
                    $fields['pais'][] = $field['label'];
                }
                if($field['field'] == 22){
                    $fields['departamento'][] = $field['label'];
                }
            }
            return $fields;
        }
        return null;
    }



}
