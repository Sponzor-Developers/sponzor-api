<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Http\Controllers\SkinController;
use App\Models\User;


class UserController extends Controller
{

    public function index(Request $request) : json
    {
        $user = $request->user();
        return response()->json($user);
    }
    public static function check()
    {
        $user = auth()->user();
        $resposta = [
            'success' => true,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'is_admin' => $user->is_admin,
        ];
        return response()->json($resposta, 200);
    }

    public static function requestsOrder(Request $request)
    {
        $user = auth()->user();
        $result = $request->validate([
            'limite' => 'integer',
            'pagina' => 'integer',
        ]);
        $limite = $result['limite'] ?? 50;
        $pagina = $result['pagina'] ?? 1;
        $result = DB::table('orders')->where('user_id', $user->id)->where('status', 'approved')->orderBy('id', 'asc')->paginate($limite, ['*'], 'pagina', $pagina);
        if(!$result) {
            return response()->json(['success' => false, 'message' => 'Nenhum pedido encontrado'], 200);
        }
        //adicionar enumeração de ordens
        $count = 1;
        foreach($result as $order) {
            $order->count = $count;
            $count++;
        }
        return response()->json(['success' => true, 'response' => $result], 200);
    }
    
    public static function sendMailOrder(Request $request)
    {
            $order_id = $request->order;
            $user = auth()->user();
            $order = DB::table('orders')->where('id', $order_id)->where('user_id', $user->id)->first();
            if(!$order){
                return response()->json(['success' => false, 'response' => 'Pedido não encontrado'], 200);
            }
            $product_id = $order->product_id;
            $product_id = json_decode($product_id, true);
            $products = [];
            foreach($product_id as $id){
                $product = DB::table('accounts')
                    ->where('id','=', $id)
                    ->where('status','=', 2) 
                    ->first();
                $skins = [];
                foreach(json_decode($product->skins, true) as $skin){
                    $skins[] = SkinController::getSkin($skin)[0]['name'];
                }
                $skins = implode(', ', $skins);
                $product->skins = $skins;
                if(!$product){
                    return response()->json(['success' => false, 'response' => 'Produto não encontrado'], 200);
                }
                array_push($products, $product);
            }
            $data = [
                'order' => $order,
                'products' => $products,
            ];
            // send mail
            $email = Mail::send('emails.sendmail', $data, function ($message) use($order, $user) {
                $message->from('noreply@lilsmurfs.com', 'Lilsmurfs');
                $message->to($user->email, $user->name);
                $message->subject('Contas');
            });
            if(!$email){
                return response()->json(['success' => false, 'response' => 'Erro ao enviar email'], 500);
            }
            return response()->json(['success' => true, 'response' => 'Email enviado com sucesso'], 200);
    } 

    public static function getPerfil()
    {
        $user = auth()->user();
        $user = DB::table('users')->where('id', $user->id)->first();
        if(!$user){
            return response()->json(['success' => false, 'response' => 'Usuário não encontrado'], 200);
        }
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ];
        return response()->json(['success' => true, 'response' => $data], 200);
    }

    /**
     * Atualiza o perfil do usuário logado
     *
     * @param Request $request
     * @return string
     */
    public static function setPerfil(Request $request) : string
    {
        $result = $request->validate([
            'name' => 'string|min:4|string|max:150',
            'email' => 'email|min:4|string|max:150'
        ]);
        if(!$result){
            return response()->json(['success' => false, 'response' => 'Erro ao atualizar perfil'], 500);
        }
        if(empty($result)){
            return response()->json(['success' => false, 'response' => 'Nenhum dado foi enviado'], 200);
        }
        $user = auth()->user();
        $result = DB::table('users')->where('id', $user->id)->first();
        if(!$result){
            return response()->json(['success' => false], 200);
        }
        $data = [];
        if(isset($request->name)){
            $data['name'] = $request->name;
        }
        if(isset($request->email)){
            $data['email'] = $request->email;
        }
        if(empty($data)){
            return response()->json(['success' => false, 'response' => 'Nenhum dado foi enviado'], 200);
        }
        $update = USER::where('id', $user->id)->update($data);
        if(!$update){
            return response()->json(['success' => false, 'response' => 'Erro ao atualizar perfil'], 500);
        }
        return response()->json(['success' => true, 'response' => 'Dados atualizados com sucesso'], 200);
    }

    /**
     * Atualiza a senha do usuário logado
     *
     * @param Request $request
     * @return string
     */
    public static function setSenha(Request $request) : string
    {
        $result = $request->validate([
            'password' => 'string|min:4|string|max:150'
        ]);
        if(!$result){
            return response()->json(['success' => false, 'response' => 'Erro ao atualizar senha'], 500);
        }
        if(empty($result)){
            return response()->json(['success' => false, 'response' => 'Nenhum dado foi enviado'], 200);
        }
        $user = auth()->user();
        $result = DB::table('users')->where('id', $user->id)->first();
        if(!$result){
            return response()->json(['success' => false], 200);
        }
        // atualizar senha
        $data = [];
        $data['password'] = bcrypt($request->password);
        $update = USER::where('id', $user->id)->update($data);
        if(!$update){
            return response()->json(['success' => false, 'response' => 'Erro ao atualizar senha'], 500);
        }
        return response()->json(['success' => true, 'response' => 'Senha atualizada com sucesso'], 200);
    }
}
