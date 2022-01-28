<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordRecovered;
use Illuminate\Support\Facades\DB;

class UserController extends UserController
{
    public function register(Request $req)
    {
        $response = ["status" => 1, "msg" => ""];

        $validator = Validator::make(json_decode($req->getContent(), true), [

            "name" => 'required|max:50',
            "email" => 'required|email|unique:App\Models\User,email|max:40',
            "password" => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
            "work_role" => 'required|in:Dirección,RRHH,Empleado',
            "salary" => 'required', 'numeric',
            "bio" => 'required|max:150'

        ]);

        if ($validator->fails()) {
            $response['status'] = "0";
            print("Errores de la validación de la edición:" . $validator->errors());
            $response['msg'] = "Los campos introducidos no son correctos";

            return response()->json($response);
        } else {

            $data = json_decode($req->getContent());

            $user = new User();

            $user->name = $data->name;
            $user->email = $data->email;
            $user->password = Hash::make($data->password);
            $user->work_role = $data->work_role;
            $user->salary = $data->salary;
            $user->bio = $data->bio;


            try {
                $user->save();
                $response['msg'] = "Empleado registrado con id: " . $user->id;
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

        try {

            if (User::where('email', '=', $data->email)->first()) {
                $user = User::where('email', '=', $data->email)->first();
                if (Hash::check($data->password, $user->password)) {
                    do {
                        $token = Hash::make($user->id . now());
                    } while (User::where('api_token', $token)->first());

                    $user->api_token = $token;
                    $user->save();
                    $response['msg'] = "Accediendo a la cuenta...";
                } else {
                    $response['msg'] = "La contraseña es incorrecta";
                }
            } else {
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

                $newPassword = md5("newPass");
                $user->password = Hash::make($newPassword);
                $user->save();


                //Mail::to($user->email)->send(new PasswordRecovered($newPassword));
                $response['msg'] = "Correo enviado a la dirección = " . $user->email;
            } else {
                $response['msg'] = "El usuario no se ha encontrado";
            }
        } catch (\Exception $e) {
            $response['status'] = 0;
            $req['msg'] = "Se ha producido un error" . $e->getMessage();
        }
        return response()->json($response);
    }
}
