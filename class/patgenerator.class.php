<?php



class patgenerator {




    static function filadetemplate($ficherotemplate,$template,$datos){
        $page2 = new Pagina();
        $page2->setRoot('templates');
        $page2->readTemplatesFromInput($ficherotemplate);
        $page2->addRows($template,$datos);
        $html = $page2->getParsedTemplate();

        return $html;
    }


    static function filadetemplate2($ficherotemplate,$template,$datos){
        $page2 = new Pagina();
        $page2->setRoot('templates');
        $page2->readTemplatesFromInput($ficherotemplate);
        $page2->addRows($template,$datos);
        $html = $page2->getParsedTemplate($template);

        return $html;
    }

}