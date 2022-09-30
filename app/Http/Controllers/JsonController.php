<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JsonController extends Controller
{
    /**
     * Retorno de requisições
     *
     * @param string $status
     * @param integer $code
     * @param string $message
     * @param array $data
     * @return void
     */
    public static function return(string $status = 'error', int $code = 500, string $message = "", array $data = [])
    {
        $response = [
            'status' => self::status($status),
            'code' => self::code($code)
        ];
        if ($message != "") {
            $response['message'] = $message;
        }
        if(count($data) > 0){
            $response['data'] = $data;
        }
        return response()->json($response, $code, [
            'Content-Type' => 'application/json;charset=UTF-8',
            'Accept' => 'application/json',
            'Charset' => 'utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Verificar se o status é valido
     *
     * @param string $status
     * @return string
     */
    private static function status(string $status) : string
    {
        if (in_array($status, [
            'success' => 'success',
            'error' => 'error',
            'warning' => 'warning',
            'info' => 'info',
        ])) {
            return $status;
        }
        return 'error';
    }

    /**
     * Verificar se o codigo é valido
     *
     * @param integer $code
     * @return integer
     */
    private static function code(int $code) : int
    {
        if($code >= 100 && $code <= 599){
            return $code;
        }
        return 500;
    }

    /**
     * Verificar se a mensagem é uma string
     *
     * @param string $message
     * @return string
     */
    private static function message(string $message) : string
    {
        if(is_string($message)){
            return $message;
        }
        return 'Erro interno';
    }

    /**
     * Undocumented function
     *
     * @param array $data
     * @return array
     */
    private static function data(array $data) : array
    {
        if(is_array($data)){
            return $data;
        }
        return [];
    }
}
