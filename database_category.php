<?php
    require_once ('classes/Category.php');

function addCategory($name) {

    global $connection;

    try {
        $statement = $connection->prepare("INSERT INTO kategorije
                                                (categoryName) VALUES
                                                (:name)");   //spremi novu kategoriju u bazu podataka

        $statement->bindParam(':name', $name, PDO::PARAM_STR);
        $statement->execute();

        $statement = $connection->prepare("SELECT * FROM kategorije");   //dobavi sve kategoriju (opis u metodi getCategories)
        $statement->execute();
        $resultArray = Array();
        while($item = $statement->fetchObject()){
            $category = new Category();
            $category->id = $item->id;
            $category->category = $item->categoryName;
            array_push($resultArray, $category);
        }
        return $resultArray;
    }
    catch(PDOException $e) {
        echo $e;
    }
}

function deleteCategory($id) {

    global $connection;

    try {
        $statement = $connection->prepare("DELETE FROM kategorije WHERE id = :id");  //brisi kategoriju i dobavi novi popis kategorija iz baze (slicno kao i getcategories)

        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $statement = $connection->prepare("SELECT * FROM kategorije");
        $statement->execute();
        $resultArray = Array();
        while($item = $statement->fetchObject()){
            $category = new Category();
            $category->id = $item->id;
            $category->category = $item->categoryName;
            array_push($resultArray, $category);
        }
        return $resultArray;
    }
    catch(PDOException $e) {
        echo $e;
    }
}

function getCategories() {

    global $connection;

    try {

        $statement = $connection->prepare("SELECT * FROM kategorije ORDER BY id");  // dohvati kategorije iz baze
        $statement->execute();
        $resultArray = Array();
        while($item = $statement->fetchObject()){   // idi po redovima iz baze i spremaj podatake u category object, i onda taj object dodaj u array sa kategorijama
            $category = new Category();
            $category->id = $item->id;
            $category->category = $item->categoryName;
            array_push($resultArray, $category);
        }
        return $resultArray;
    }
    catch(PDOException $e) {
        echo $e;
    }
}
