<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use function PHPUnit\Framework\throwException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JsonController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Password;




class AuthController extends Controller
{
    use HasApiTokens;

    /**
     * Autenticação de usuário
     *
     * @param Request $request
     * @return string
     */
    public function login(Request $request)
    {
        $result = $request->validate([
            'email' => 'required|string|email|min:5|max:155',
            'password' => 'required|string|min:8|max:50',
        ]);
        if(!$result)
        {
            return JsonController::return('error',400, 'Dados inválidos');
        }
        try{
            if (Auth::attempt(['email' => $result['email'], 'password' => $result['password']], true)) {
                $user = Auth::user();
                $tokenResult = $user->createToken('JWT');

                return JsonController::return('success', 200, 'Login realizado com sucesso', [
                    'token' => $tokenResult->plainTextToken,
                    'user' => $user
                ]);
            }
            return JsonController::return('error', 401, 'Usuário ou senha inválidos');
        }catch (\Exception $e) {
            return JsonController::return('error', 500, 'Erro ao realizar login');
        }
    }

    /**
     * Registrar novo usuário
     *
     * @param Request $request
     * @return string
     */
    public function register(Request $request)
    {

        // name, enterprise, business, email, password, phone, is_admin
        
        $result = $request->validate([
            'name' => 'required|string|min:3|max:155',
            'enterprise' => 'required|string|min:3|max:155',
            'business' => 'required|string|min:3|max:155',
            'email' => 'required|string|email|min:5|max:155|unique:users',
            'password' => 'required|string|min:8|max:50',
            'phone' => 'required|string|min:8|max:50',
        ]);
        if(!$result)
        {
            return JsonController::return('error',400, 'Dados inválidos');
        }
        try {
            $user = User::create([
                'name' => $result['name'],
                'enterprise' => $result['enterprise'],
                'business' => $result['business'],
                'segmentation' => 0,
                'email' => $result['email'],
                'password' => Hash::make($result['password']),
                'phone' => $result['phone'],
                'plan' => 0,
                'is_admin' => 0,
            ]);
            $tokenResult = $user->createToken('JWT');
            return JsonController::return('success', 200, 'Usuário criado com sucesso', [
                'token' => $tokenResult->plainTextToken,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return JsonController::return('error', 500, 'Erro ao criar usuário');
        }
    }

    /**
     * Resetar senha
     * @param Request $request
     * @return string
     */

    public function resetPassword(Request $request)
    {
        $result = $request->validate([
            'email' => 'required|string|email|min:3|max:191',
        ]);
        try {
            $user = User::where('email', $result['email'])->first();
            if ($user) {
                $token = Str::random(60);
                DB::table('password_resets')->insert([
                    'email' => $user->email,
                    'token' => $token,
                    'created_at' => now()
                ]);
                Mail::to($user->email)->send(new SendMail($token));
                return JsonController::return('success', 200, 'Email enviado com sucesso');
            }
            return JsonController::return('error', 401, 'Email não encontrado');
        } catch (\Exception $e) {
            return JsonController::return('error', 500, 'Erro ao enviar email');
        }
    }

    /**
     * Verifica o token de recuperação de senha
     * @param Request $request
     * @return string
     */
    public function checkToken(Request $request)
    {
        $result = $request->validate([
            'token' => 'required|string|min:3|max:191',
        ]);
        try {
            $token = DB::table('password_resets')->where('token', $result['token'])->first();
            if ($token) {
                return JsonController::return('success', 200, 'Token válido');
            }
            return JsonController::return('error', 401, 'Token inválido');
        } catch (\Exception $e) {
            return JsonController::return('error', 500, 'Erro ao verificar token');
        }
    }

    
    /**
     * Altera a senha do usuário
     * @param Request $request
     * @return string
     */
    public function changePassword(Request $request)
    {
        $result = $request->validate([
            'token' => 'required|string|min:3|max:191',
            'password' => 'required|string|max:50|min:8'
        ]);
        try {
            $check = DB::table('password_resets')->where('token', $result['token'])->count();
            if ($check == 1) {
                $user = User::where('email', DB::table('password_resets')->where('token', $result['token'])->first()->email)->first();
                $user->password = bcrypt($result['password']);
                $user->save();
                DB::table('password_resets')->where('token', $result['token'])->delete();
                return JsonController::return('success', 200, 'Senha alterada com sucesso');
            }
            return JsonController::return('error', 401, 'Token inválido');
        } catch (\Exception $e) {
            return JsonController::return('error', 500, 'Erro ao alterar senha');
        }
    }

    /**
     * Deslogar usuário
     *
     * @param Request $request
     * @return string
     */

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return JsonController::return('success', 200, 'Logout realizado com sucesso');
        } catch (\Exception $e) {
            return JsonController::return('error', 500, 'Erro ao realizar logout');
        }
    }

    /**
     * Retorna o usuário logado
     *
     * @param Request $request
     * @return string
     */

    public function user(Request $request)
    {
        try {
            return JsonController::return('success', 200, 'Usuário retornado com sucesso', $request->user());
        } catch (\Exception $e) {
            return JsonController::return('error', 500, 'Erro ao retornar usuário');
        }
    }

    /**
     * Login com o Google com o Socialite
     * @param Request $request
     * @return string
     */
    public function loginGoogle(Request $request)
    {
        $result = $request->validate([
            'token' => 'required|string|min:3|max:191',
        ]);
        try {
            $user = Socialite::driver('google')->stateless()->userFromToken($result['token']);
            $user = User::firstOrCreate([
                'email' => $user->email
            ], [
                'name' => $user->name,
                'email' => $user->email,
                'password' => bcrypt(Str::random(10))
            ]);
            $token = $user->createToken('JWT')->plainTextToken;
            return JsonController::return('success', 200, 'Login realizado com sucesso', [
                'token' => $token,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return JsonController::return('error', 500, 'Erro ao realizar login');
        }
    }

}
