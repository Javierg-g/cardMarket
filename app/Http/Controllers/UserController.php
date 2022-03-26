<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BoughtCard;
use App\Models\SoldCard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function register(Request $req)
    {
        $response = ["status" => 1, "msg" => ""];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            "name" => 'required|unique:App\Models\User,name|max:60',
            "email" => 'required|email|unique:App\Models\User,email|max:40',
            "password" => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
            "role" => 'required|in:Particular,Profesional,Admin',

        ]);

        if ($validator->fails()) {
            $response['status'] = "0";
            $response['msg'] = "Los campos introducidos no son correctos";
            return response()->json($response);
        } else {

            $data = json_decode($req->getContent());

            $user = new User();
            $user->name = $data->name;
            $user->email = $data->email;
            $user->password = Hash::make($data->password);
            $user->role = $data->role;


            try {
                $user->save();
                $response['msg'] = "Usuario registrado con id: " . $user->id;
            } catch (\Exception $e) {
                $response['status'] = 0;
                $req['msg'] = "Se ha producido un error" . $e->getMessage();
            }

            return response()->json($response);
        }
    }

    public function login(Request $req)
    {
        $response = ["status" => 1, "msg" => ""];

        $data = json_decode($req->getContent());

        $user = User::where('name', '=', $data->name)->first();

        try {

            if ($user) {
                if (Hash::check($data->password, $user->password)) {
                    do {
                        $token = Hash::make($user->id . now());
                    } while (User::where('api_token', $token)->first());
                    $user->api_token = $token;
                    $user->save();
                    $response['msg'] = "Accediendo a la cuenta...";
                } else {
                    $response['status'] = 0;
                    $response['msg'] = "La contraseña es incorrecta";
                }
            } else {
                $response['status'] = 0;
                $response['msg'] = "El usuario no se ha encontrado";
            }
        } catch (\Exception $e) {
            $response['status'] = 0;
            $req['msg'] = "Se ha producido un error" . $e->getMessage();
        }
        return response()->json($response);
    }

    public function passwordRecovery(Request $req)
    {

        $data = json_decode($req->getContent());

        try {

            if (User::where('email', '=', $data->email)->first()) {
                $user = User::where('email', '=', $data->email)->first();

                $user->api_token = null;

                $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
                $newPassword = array();
                $alphaLength = strlen($alphabet) - 1;
                for ($i = 0; $i < 10; $i++) {
                    $n = rand(0, $alphaLength);
                    $newPassword[] = $alphabet[$n];
                }
                $user->password = Hash::make($newPassword);
                $user->save();

                $response['msg'] = "Su nueva contraseña es:" . $newPassword;
            } else {
                $response['status'] = 0;
                $response['msg'] = "El usuario no se ha encontrado";
            }
        } catch (\Exception $e) {
            $response['status'] = 0;
            $req['msg'] = "Se ha producido un error" . $e->getMessage();
        }
        return response()->json($response);
    }

    public function sellCard(Request $req)
    {
        $respuesta = ['status' => 1, 'msg' => ''];

        $validator = Validator::make(
            json_decode($req->getContent(), true),
            [
                'id_carta' => ['required', 'integer'],
                'quantity' => ['required', 'integer'],
                'price' => ['required', 'numeric', 'min:0', 'not_in:0'],

            ]
        );
        if ($validator->fails()) {
            $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {
            $data = json_decode($req->getContent());
            $cartas = Card::select('id')
                ->where('id', '=', $data->id_carta)
                ->get();
            if ($cartas) {
                $soldCard = new SoldCard();
                $soldCard->id_carta = $data->id_carta;
                $soldCard->cantidad = $data->cantidad;
                $soldCard->precio = $data->precio;
                $soldCard->usuario = $data->id;

                try {
                    $soldCard->save();
                    $respuesta['msg'] = "Carta vendida. ID:" . $soldCard->id;
                } catch (\Exception $e) {
                    $respuesta['status'] = 0;
                    $respuesta['msg'] = 'Se ha producido un error: ' . $e->getMessage();
                }
            } else {
                $respuesta['msg'] = 'Carta no encontrada para poder vender.';
            }
        }



        return response()->json($respuesta);
    }
    public function buyCard(Request $req)
    {
        $response = ['status' => 1, 'msg' => ''];

        $data = json_decode($req->getContent());

        $user = User::where('api_token', '=', $req->api_token)->first();

        $card = Card::where('id', '=', $data->carta)->first();

        if ($user) {
            try {
                $boughtCard = new BoughtCard();
                $boughtCard->id_carta = $card->id;
                $boughtCard->id_user = $card->id;
                $boughtCard->save();
                $response['msg'] = 'Compra de carta. ID:' . $card->id;
            } catch (\Exception $e) {
                $response['status'] = 0;
                $response['msg'] = 'Se ha producido un error: ' . $e->getMessage();
            }
        } else {
            $response['status'] = 0;
            $response['msg'] = 'Carta no existe';
        }
        return response()->json($response);
    }
}
