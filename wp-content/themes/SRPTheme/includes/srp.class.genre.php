<?php

class SRPGenre
{
    private $name = '';
    private $id = -1;

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }

    public function getID() { return $this->id; }
    public function setID($id) { $this->id = $id; }
}

class SRPGenreSettings
{
    private $genres = array();

    public function getAllGenres() { return $this->genres; }

    public function getGenreByID($id) { return $this->genres[$id]; }

    public function __construct($selectOnStartup = false)
    {
        if ($selectOnStartup === true)
        {
            dbSelect();
        }
    }

    public function dbSelect()
    {
        global $wpdb;
        $genres_tablename = $wpdb->prefix . 'srp_genres';
        $query = $wpdb->prepare("SELECT id, genre_name FROM $genres_tablename ORDER BY genre_name");
        
        unset($this->genres);

        $IDs = $wpdb->get_col($query, 0);
        $names = $wpdb->get_col($query, 1);
        for ($i = 0; $i < count($IDs); $i++)
        {
            $genre = new SRPGenre;
            $genre->setName($names[$i]);
            $genre->setID($IDs[$i]);
            $this->genres[] = $genre;
        }
        
        return true;
    }
}

?>
