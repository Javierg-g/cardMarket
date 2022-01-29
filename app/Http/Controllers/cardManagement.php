<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Card;
use App\Models\CardInCollection;
use App\Models\Collection;
use Illuminate\Support\Facades\Validator;
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
            print("Errores de la validación:" . $validator->errors());
            $response['msg'] = "Los campos introducidos no son correctos";
            return response()->json($response);
        } else {
            $data = json_decode($req->getContent());
            $collection = Collection::where('id','=',$data->collection)->first();

            if($collection){
                $card = new Card();
                $card->name = $data->name;
                $card->description = $data->description;

                try {
                    $card->save();
                    $selectedCollection = new CardInCollection();
                    $selectedCollection->card_id = $card->id;
                    $selectedCollection->collection_id = $collection->id;
                    $selectedCollection->save();
                    $response['msg'] = "Carta registrada con id: " . $card->id." en coleccion con id:".$selectedCollection;
                } catch (\Exception $e) {
                    $response['status'] = 0;
                    $req['msg'] = "Se ha producido un error" . $e->getMessage();
                }
            }else{
                $response['status'] = 0;
                $req['msg'] = "No existe esa coleccion";
            }

            return response()->json($response);
        }
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
            print("Errores de la validación:" . $validator->errors());
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
}
