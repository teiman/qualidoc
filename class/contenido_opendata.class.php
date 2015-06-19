<?php


include_once(__ROOT__."/inc/magpierss/rss_fetch.inc");


class contenido_opendata extends contenido {

    function getURL(){
        $id_rss = $this->get("id_rss");

        $sql = parametros("SELECT url FROM canales_rss WHERE id_canal_rss=%d",$id_rss);
        $row = queryrow($sql);

        return $row["url"];
    }

}