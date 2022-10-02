<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\JsonController;


class ActiveCampaningController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(
        string $api_key = 'a0b1c2d3e4f5g6h7i8j9k0l1m2n3o4p5q6r7s8t9u0v1w2x3y4z5',
        string $api_url = 'https://api.activecampaign.com/v3'
    ) {
        $this->api_key = $api_key;
        $this->api_url = $api_url;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function index()
    {
        $response = Http::withHeaders([
            'Api-Token' => $this->api_key,
        ])->get($this->api_url . '/contacts');

        return JsonController::return('success', 200, 'Listagem de contatos', ['contacts' => $response->json()]);
    }

    /**
     * List all contacts by lists
     * 
     */
    public function listContactsByLists(array $lists) : array
    {
        $contacts = [];

        foreach ($lists as $list) {
            $response = Http::withHeaders([
                'Api-Token' => $this->api_key,
            ])->get($this->api_url . '/contacts?list=' . $list);

            $contacts = array_merge($contacts, $response->json()['contacts']);
        }

        return $contacts;
    }

    /**
     * Filter contacts by lists and filter
     * 
     */

    public function filterContactsByLists(array $lists, array $filter) : array
    {
        $contacts = $this->listContactsByLists($lists);

        $filteredContacts = [];

        foreach ($contacts as $contact) {
            $filteredContacts[] = array_filter($contact, function ($key) use ($filter) {
                return in_array($key, $filter);
            }, ARRAY_FILTER_USE_KEY);
        }
        return $filteredContacts;
    }

}
