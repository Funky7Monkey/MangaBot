<?php

class Manga {
    public $id; //str
    public $title; //str
    public $cover_url; //str
    public $author; //str
    public $demographic; //str
    public $tags; //array
    public $rating;//str
    public $links; //array
    public $status; //str
    public $url; //str

    public function __construct($type, $search) {
        //echo "id: " . $id . "search: " . $search . PHP_EOL;
        switch ($type) {
            case 'id':
                $uri = "https://api.mangadex.org/manga/${search}?includes[]=cover_art";
                $out = json_decode(file_get_contents($uri), true)['data'];
                break;
            case 'chapter':
                break;
            case 'search':
                $uri = "https://api.mangadex.org/manga?title=${search}&limit=1&contentRating[]=safe&contentRating[]=suggestive&contentRating[]=erotica&contentRating[]=pornographic&includes[]=cover_art&order[relevance]=desc";
                $out = json_decode(file_get_contents($uri), true)['data'][0];
                break;
        }

        $id = $out['id'];
        $this->id = $id;
        $this->title = $out['attributes']['title']['en'];
        $this->demographic = $out['attributes']['publicationDemographic'];
        $this->rating = $out['attributes']['contentRating'];
        $this->status = $out['attributes']['status'];
        $this->links = $out['attributes']['links']; //will be array

        $cover_id = $out['relationships'][2]['attributes']['fileName'];
        $this->cover_url = "https://uploads.mangadex.org/covers/${id}/${cover_id}";
        $this->url = "https://mangadex.org/title/${id}/" . str_replace(' ', '-', $this->title);

        $author_id = $out['relationships'][0]['id'];
        $author_uri = "https://api.mangadex.org/author/${author_id}";
        $this->author = json_decode(file_get_contents($author_uri), true)['data']['attributes']['name'];

        $tags = array();
        foreach ($out['attributes']['tags'] as $tag) {
            $tags[] = $tag['attributes']['name']['en'];
        }
        $this->tags = $tags;
    }
    public function dump() {
        echo $this->id . PHP_EOL;
        echo $this->title . PHP_EOL;
        echo $this->cover_url . PHP_EOL;
        echo $this->author . PHP_EOL;
        echo $this->demographic . PHP_EOL;
        echo $this->rating . PHP_EOL;
        echo $this->status . PHP_EOL;
        print_r($this->tags);
        print_r($this->links);
    }
    /*public function static search($search) {
        $uri = "https://api.mangadex.org/manga?title=${search}&limit=5&contentRating[]=safe&contentRating[]=suggestive&contentRating[]=erotica&contentRating[]=pornographic&includes[]=cover_art&order[relevance]=desc";
        $out = json_decode(file_get_contents($uri), true);
    }*/
}

?>