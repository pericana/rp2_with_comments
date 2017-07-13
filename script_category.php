<?php

require_once 'classes/Category.php';
require_once 'database_connect.php';
require_once 'database_category.php';

if(isset($_POST['action']) && $_POST['action'] === "add"){

    $categoryName = $_POST['name'];   //dobavimo poslane podatke (category name)
    $categories = addCategory($categoryName);   //spremamo kategoriju u bazu podataka (database_category.php), funkcija nam vraca novi popis kategorija

    echo json_encode( $categories );
    flush();

}else if(isset($_POST['action']) && $_POST['action'] === "delete"){

    $id = $_POST['id'];  //dobavimo poslane podatke (category id)
    $categories = deleteCategory($id);  //brisemo kategoriju iz baze podataka (database_category.php), funkcija nam vraca novi popis kategorija

    echo json_encode( $categories );
    flush();

}else if(isset($_GET['action']) && $_GET['action'] === "get"){

    $categories = getCategories();  //dobavimo sve kategorije iz baze (database_category.php)

    echo json_encode( $categories );   //vrati kategorije preko json-a
    flush();

}

?>