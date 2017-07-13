<?php

require_once 'database_connect.php';
require_once 'database_user.php';

if(isset($_POST['action']) && $_POST['action'] === "updateScore"){

    //update score u bazi korisnici, dohvati podatke i zovi metodu updateBestResult iz file database_user.php

    $categoryName = $_POST['cat_name'];
    $userId = $_POST['user_id'];
    $score = $_POST['score'];

    updateBestResult($userId, $score, $categoryName);

    echo json_encode( "Success" );
    flush();

}else if(isset($_GET['action']) && $_GET['action'] === "get"){  //dohavacamo sve korisnike

    $users = getUsers(); //dohvacamo korisnike iz baze podataka database_user.php i vrati ih

    echo json_encode( $users );
    flush();

}else if(isset($_POST['action']) && $_POST['action'] === "makeAdmin"){  //napravi usera sa poslanim id adminom

    $userId = $_POST['user_id'];

    setUserAdmin($userId); //metoda je u database_user.php
    $users = getUsers();  //dobavi usere sa novim podacima i vrati ih nazad

    echo json_encode( $users );
    flush();

}

?>