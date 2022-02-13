<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Card;
use App\Models\CardInCollection;
use App\Models\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class cardManagement extends Controller
{
    public function addCard(Request $req)
    {
        $response = ["status" => 1, "msg" => ""];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            "name" => 'required|max:50',
            "description" => 'required|max:150'
            //"collection" => 'required|integer',
        ]);

        if ($validator->fails()) {
            $response['status'] = "0";
            $response['msg'] = "Los campos introducidos no son correctos";
        } else {
            $data = json_decode($req->getContent());
            //$collection = Collection::where('id', '=', $data->collection)->first();

            $card = new Card();
            $card->name = $data->name;
            $card->description = $data->description;

            try {
                $card->save();
                $response['msg'] = "Carta registrada con id: " . $card->id;
            } catch (\Exception $e) {

                $response['status'] = 0;
                $req['msg'] = "Se ha producido un error" . $e->getMessage();
            }
        }
        return response()->json($response);
    }

    public function addCollection(Request $req)
    {
        $response = ["status" => 1, "msg" => ""];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            "name" => 'required|max:50',
            "symbol" => 'required|max:150',
            "edit_date" => 'required|date',
        ]);

        if ($validator->fails()) {
            $response['status'] = "0";
            $response['msg'] = "Los campos introducidos no son correctos";
            return response()->json($response);
        } else {

            $data = json_decode($req->getContent());

            $collection = new Collection();
            $collection->name = $data->name;
            $collection->symbol = $data->symbol;
            $collection->edit_date = $data->edit_date;

            try {
                $collection->save();
                $response['msg'] = "Coleccion registrada con id: " . $collection->id;
            } catch (\Exception $e) {
                $response['status'] = 0;
                $req['msg'] = "Se ha producido un error" . $e->getMessage();
            }

            return response()->json($response);
        }
    }

    public function searchCard(Request $req)
    {
        $response = ["status" => 1];

        Log::info('- Inicio de la búsqueda -');

        if ($req->search) {
            Log::debug('Parámetro de búsqueda introducido');
            try {
                Log::debug('Consulta de búsqueda hecha');

                $cards = Card::where('name', 'like', '%' . $req->search . '%')
                    ->orWhere('id', 'like', '%' . $req->search . '%')
                    ->orderBy('name')
                    ->get();

                if (count($cards) == 0) {
                    Log::debug('Sin resultados para la busqueda');
                    $response['Resultados de la busqueda'] = "No hay resultados";
                } else {
                    Log::debug('Resultados econtrados para la busqueda');
                    $response['Resultados de la busqueda'] = $cards;
                }
            } catch (\Exception $e) {
                $response['status'] = 0;
                Log::error("Error en la busqueda: " . $e);
                $req['msg'] = "Se ha producido un error" . $e->getMessage();
            }
        } else {
            $response['status'] = 0;
            Log::warning('No se ha introducido ningún parametro en la busqueda');
            $response['msg'] = "No se ha introducido ningun parametro de busqueda";
        }
        Log::debug('Recoger resultados de busqueda');

        return response()->json($response);
    }
}



        /*if ($collection) {
                $card = new Card();
                $card->name = $data->name;
                $card->description = $data->description;

                try {
                    $card->save();
                    $selectedCollection = new CardInCollection();
                    $selectedCollection->card_id = $card->id;
                    $selectedCollection->collection_id = $collection->id;
                    $selectedCollection->save();
                    $response['msg'] = "Carta registrada con id: " . $card->id . " en coleccion con id:" . $selectedCollection;
                } catch (\Exception $e) {
                    $response['status'] = 0;
                    $req['msg'] = "Se ha producido un error" . $e->getMessage();
                }
            } else {
                $response['status'] = 0;
                $req['msg'] = "No existe esa coleccion";
            }*/
