<?php

require_once 'database_connect.php';
require_once 'database_question.php';

if(isset($_GET['action']) && $_GET['action'] === "get"){ //skripta za dobaljanje pitanja, dobavi sva pitanja za proslijedjenu kategoriju
    //i vrati ta pitanja (pitanja dobavi preko file-a database_question.php)

    $categoryId = $_GET['cat_id'];

    $questions = getQuestionByCategoryId($categoryId);

    echo json_encode( $questions );
    flush();

}

?>