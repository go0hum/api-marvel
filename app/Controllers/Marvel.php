<?php 

namespace App\Controllers;

class Marvel extends BaseController 
{
    public function index()
    {
        $json =  array(
            "detalle" => "no encontrado"
        );

        return json_encode($json, true);
    }

    public function colaborators($id)
    {
        $json =  array(
            "status" => 200,
            "message" => "The colaborators is {$id}"
        );

        return json_encode($json, true);
    }

    public function characters($id)
    {
        $json =  array(
            "status" => 200,
            "message" => "The characters is {$id}"
        );

        return json_encode($json, true);
    }
}