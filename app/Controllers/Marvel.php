<?php 

namespace App\Controllers;

class Marvel extends BaseController 
{
    private $url;
    private $ts;
    private $publicKey;
    private $privateKey;
    private $hash;

    public function __construct()
    {
        $this->url = getenv('API_MARVEL_URL');
        $this->ts = time();
        $this->publicKey = getenv('API_MARVEL_PUBLIC');
        $this->privateKey = getenv('API_MARVEL_PRIVATE');
        $this->hash = md5($this->ts.$this->privateKey.$this->publicKey);
    }

    public function index()
    {
        $json =  array(
            "status" => 200,
            "data" => "Request invalid"
        );

        return json_encode($json, true);
    }

    public function colaborators($heroe)
    {
        $urls = $this->getUrls($heroe);
        $response['editors'] = [];
        $response['writers'] = [];
        $requests = $this->multiRequest($urls);
        foreach ($requests as $request) {
            $info = json_decode($request);
            foreach ($info->data->results[0]->creators->items as $item) {
                if ($item->role == 'editor' && !in_array($item->name, $response['editors'])) {
                    $response['editors'][] = $item->name;
                }
                if ($item->role == 'writer' && !in_array($item->name, $response['writers'])) {
                    $response['writers'][] = $item->name;
                }
            }
        }

        $json =  array(
            "status" => 200,
            "data" => $response
        );

        return json_encode($json, true);
    }

    public function characters($heroe)
    {
        $urls = $this->getUrls($heroe);
        $response['character'] = [];
        $requests = $this->multiRequest($urls);
        foreach ($requests as $request) {
            $info = json_decode($request);
            foreach ($info->data->results[0]->characters->items as $item) {
                if (strcasecmp(preg_replace('/[^A-Za-z0-9]/', "", $heroe), preg_replace('/[^A-Za-z0-9]/', "", $item->name)) !== 0) {
                    $response['character'][$item->name]["Comics"][] = $info->data->results[0]->title;
                }
            }
        }

        $json =  array(
            "status" => 200,
            "characters" => $response
        );

        return json_encode($json, true);
    }

    protected function getUrls($heroe)
    {
        $urls = [];
        foreach ($this->allFind($heroe) as $characters) {
            if (!empty($characters->comics->items)) {
                foreach($characters->comics->items as $comic) {
                    $urls[] = $comic->resourceURI."?ts={$this->ts}&apikey={$this->publicKey}&hash={$this->hash}";
                }
            }
        }
        return $urls;
    }

    protected function allFind($heroe, $offset=0, $limit=100)
    {
        $name = str_replace(" ", "+", htmlentities(strtolower($heroe)));
        $ini = json_decode($this->request("{$this->url}characters?ts={$this->ts}&apikey={$this->publicKey}&hash={$this->hash}&offset={$offset}&limit={$limit}&nameStartsWith={$name}"));
        return $ini->data->results;
    }

    protected function request($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        $data = curl_exec($ch); 
        curl_close($ch); 
        return $data;
    }

    protected function multiRequest($urls)
    {
        $multiCurl = array();
        $result = array();
        $mh = curl_multi_init();
        foreach ($urls as $i => $url) {
            $multiCurl[$i] = curl_init();
            curl_setopt($multiCurl[$i], CURLOPT_URL, $url);
            curl_setopt($multiCurl[$i], CURLOPT_HEADER, 0);
            curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($mh, $multiCurl[$i]);
        }

        $index = null;
        do {
            curl_multi_exec($mh, $index);
        } while($index > 0);

        foreach($multiCurl as $k => $ch) {
            $result[$k] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);

        return $result;
    }
}