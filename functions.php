<?php
function sendRequest($function, $table = NULL, $data = NULL)
{
    $curl = curl_init();

    if ($table == NULL) {
        $url = "https://lucabancale.netsons.org/api/product/" . $function . ".php";
    } else {
        $url = "https://lucabancale.netsons.org/api/product/" . $function . ".php?table=" . $table;
    }

    if ($data) {
        try{
            unset($data["ISO_char"]);
        }catch(Exception $e){}
        foreach ($data as $key => $value){
            if ($value != NULL){
                continue;
            }
            unset($data[$key]);
        }
        unset($data['action']);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: javascript/json"
            )
        ));
    } else {
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: javascript/json"
            )
        ));
    }
    $rows = json_decode(curl_exec($curl), true);
    curl_close($curl);
    //print_r($rows);
    return $rows;
}
