<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Card;
use App\Models\CardInCollection;
use App\Models\Collection;
use App\Models\SoldCard;
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
            "description" => 'required|max:150',
            "collection" => 'required|integer',
        ]);

        if ($validator->fails()) {
            $response['status'] = "0";
            $response['msg'] = "Los campos introducidos no son correctos";
        } else {
            $data = json_decode($req->getContent());
            $collection = Collection::where('id', '=', $data->collection)->first();

            if ($collection) {
                $card = new Card();
                $card->name = $data->name;
                $card->description = $data->description;

                try {
                    $card->save();
                    $selectedCollection = new CardInCollection();
                    $selectedCollection->card_id = $card->id;
                    $selectedCollection->collection_id = $collection->id;
                    $selectedCollection->save();
                    $response['msg'] = "Carta registrada con id: " . $card->id . " en collection con id:" . $selectedCollection;
                } catch (\Exception $e) {
                    $response['status'] = 0;
                    $req['msg'] = "Se ha producido un error" . $e->getMessage();
                }
            } else {
                $response['status'] = 0;
                $req['msg'] = "No existe esa collection";
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
            "card" => 'required',

        ]);

        if ($validator->fails()) {
            $response['status'] = "0";
            $response['msg'] = "Los campos introducidos no son correctos";
            return response()->json($response);
        } else {
            $data = json_decode($req->getContent());
            $cardsGroup = [];
            foreach ($data->card as $newCard) {
                if (isset($newCard->id)) {
                    $card = Card::where('id', '=', $newCard->id)->first();
                    if ($card) {
                        array_push($cardsGroup, $card->id);
                    }
                } elseif (isset($newCard->name) && isset($newCard->description)) {
                    $createdCard = new Card();
                    $createdCard->name = $createdCard->name;
                    $createdCard->description = $createdCard->description;

                    try {
                        $createdCard->save();
                        array_push($cardsGroup, $createdCard->id);
                        $respuesta['msg'] = 'Carta creada. ID:' . $createdCard->id;
                    } catch (\Exception $e) {
                        $respuesta['status'] = 0;
                        $respuesta['msg'] = 'Se ha producido un error: ' . $e->getMessage();
                    }
                } else {
                    $respuesta['status'] = 0;
                    $respuesta['msg'] = 'Datos inválidos.';
                }
            }
            if (!empty($cardsGroup)) {
                $cardsIds = implode(", ", $cardsGroup);
                try {
                    $collection = new Collection();
                    $collection->name = $data->name;
                    $collection->symbol = $data->symbol;
                    $collection->edit_date = $data->edit_date;
                    $collection->save();
                    $respuesta["msg"] = "Coleccion guardada. ID:" . $collection->id;

                    foreach ($cardsGroup as $id) {
                        $cardsInCollection = new CardInCollection();
                        $cardsInCollection->card_id = $id;
                        $cardsInCollection->collection_id = $collection->id;
                        $cardsInCollection->save();
                    }
                    $respuesta['msg'] = 'Collecion. ID:' . $cardsInCollection->id . 'con cartas. ID:' . $cardsIds;
                } catch (\Exception $e) {
                    $respuesta['status'] = 0;
                    $respuesta['msg'] = 'Se ha producido un error: ' . $e->getMessage();
                }
            }
        }
        return response()->json($response);
    }
    public function putCardInCollection(Request $req)
    {
        $respuesta = ['status' => 1, 'msg' => ''];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'card' => ['required'],
            'collection' => ['required']
        ]);

        if ($validator->fails()) {
            $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {

            $data = json_decode($req->getContent());
            try {
                $card = Card::where('id', '=', $data->carta)->first();
                $collection = Collection::where('id', '=', $data->collection)->first();
                if ($card && $collection) {
                    $cardInCollection = new CardInCollection();
                    $cardInCollection->card_id = $data->card;
                    $cardInCollection->collection_id = $data->collection;
                    $cardInCollection->save();
                    $respuesta['msg'] = 'Collecion. ID:' . $data->collection . ' con carta. ID:' . $data->carta;
                } else {
                    $respuesta['status'] = 0;
                    $respuesta['msg'] = 'Carta o collection no encontrada';
                }
            } catch (\Exception $e) {
                $respuesta['status'] = 0;
                $respuesta['msg'] = 'Se ha producido un error: ' . $e->getMessage();
            }
        }
        return response()->json($respuesta);
    }
    public function searchCardByName(Request $req)
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
    public function searchBySale(Request $req)
    {
        $response = ["status" => 1];

        if ($req->search) {
            try {
                $cards = SoldCard::select(['card_id', 'quantity', 'price', 'user'])
                    ->join('users', 'users.id', '=', 'sold_cards.user')
                    ->join('cards', 'cards.id', '=', 'sold_cards.id_carta')
                    ->select('cards.nombre', 'sold_cards.quantity', 'sold_cards.price', 'users.name as seller')
                    ->where('cards.nombre', 'like', '%' . $req->search->input('busqueda') . '%')
                    ->orderBy('sold_cards.price', 'ASC')
                    ->get();

                $respuesta['data'] = $cards;
            } catch (\Exception $e) {
                $response['status'] = 0;
                $req['msg'] = "Se ha producido un error" . $e->getMessage();
            }
        } else {
            $response['status'] = 0;
            $response['msg'] = "No se ha introducido ningun parametro de busqueda";
        }
        return response()->json($response);
    }
}
