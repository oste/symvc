<?php

class IndexController extends Controller {

    public function indexAction(){

        //render the IndexTemplate which extends LayoutTemplate and pass it a variable called $data
        $this->render('IndexTemplate', array('data' => 'Hello Symvc'));

    }

}

?>
