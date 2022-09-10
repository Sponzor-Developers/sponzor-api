<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReturnController extends Controller
{

    /**
     * Regra de negocio
     * status
     * code
     * message
     * data
     */
    public function __construct(string $status, int $code, string $message, array $data = [])
    {
        $this->status = self::status($status);
        $this->code = self::code($code);
        $this->message = self::message($message);
        $this->data = self::data($data);
        return response()->json([
            'status' => $this->status,
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data
        ], $this->code);
    }

    /**
     * Verificar se o status é valido
     *
     * @param string $status
     * @return string
     */
    private function status(string $status) : string
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
    private function code(int $code) : int
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
    private function message(string $message) : string
    {
        if(is_string($message)){
            return $message;
        }
        return 'Erro interno';
    }

    private function data($data)
    {
        if(is_array($data)){
            return $data;
        }
        return [];
    }
}
