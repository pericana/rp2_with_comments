<?php   //file za komuniciranje s bazom vezanom uz korisnika (dobavljanje korisnika, registracija itd.
    require_once ('classes/User.php');
    require_once ('user_session.php');

function registration($user, $password) { //funkciji prosljedujemo kriptirani password i popunjeni user object

    global $connection;

    try {
        $statement = $connection->prepare("SELECT * FROM korisnici WHERE user=:user OR email=:email");  //gledamo u bazi je li slobodno korisnicko ime i e-mail
        $statement->bindParam(':user', $user->name, PDO::PARAM_STR);
        $statement->bindParam(':email', $user->email, PDO::PARAM_STR);
        $statement->execute();
        $result = $statement->fetchObject();
        if ($result) {
            if($result->user == $user->name){
                return "Korisničko ime je zauzeto, probajte sa nekim drugim.";
            }else{
                return "Email je zauzet, probajte sa nekim drugim.";
            }
        }else{  //ako je slobodno spremamo podatke u bazu
            $statement = $connection->prepare("INSERT INTO korisnici
                                                (user, pass, email, userType) VALUES
                                                (:user, :pass, :email, :userType)");

            $statement->bindParam(':user', $user->name, PDO::PARAM_STR);
            $statement->bindParam(':pass', $password, PDO::PARAM_STR);
            $statement->bindParam(':email', $user->email, PDO::PARAM_STR);
            $statement->bindParam(':userType', $user->userType, PDO::PARAM_INT);
            $statement->execute();

            $user->id = $connection->lastInsertId();    //pridjelimo id novostvorenom korisniku (id je mysql sam stvorio)

            setSessionUser($user);  //dodamo usera u session (user_session.php) i redirektamo na index.php
            header("Location: index.php");
        }
    }
    catch(PDOException $e) {
        echo $e;
    }
}

function login($nameOrEmail, $password) {  //funkciji prosljedujemo kriptirani password i nameoremail

    global $connection; //iz database_connect.php; koji smo importali u login.php fileu izmad ovog filea pa mozemo koristit njegove varijable kao globalne

    try {
        $statement = $connection->prepare("SELECT * FROM korisnici WHERE user=:user OR email=:email");  //dohvacanje korisnika sa poslanim usernameom ili emailom iz baze
        $statement->bindParam(':user', $nameOrEmail, PDO::PARAM_STR);
        $statement->bindParam(':email', $nameOrEmail, PDO::PARAM_STR);
        $statement->execute();
        $result = $statement->fetchObject();    //spremamo dohvacene podatke u $result
        if (!$result) { //ako ne postoji korisnik s tim username ili emailom
            return "Korisničko ime i email ne postoje u bazi.";
        }else if ( $result->pass !==  $password) {  //ako postoji korisnik s tim username ili mailom, ali kriptirana sifra se ne poklapa s podacima iz baze
            return "Lozinka netočna";
        }else{  //puni objekt iz user.php podacima iz baze
            $user = new User();
            $user->id = $result->id;
            $user->name = $result->user;
            $user->email = $result->email;
            $user->userType = $result->userType;
            $user->bestScore = $result->bestResults;
            $user->bestScoreCategoryName = $result->bestResultsCategory;
            setSessionUser($user);  //spremi objekt u session (user_session.php)
            header("Location: index.php");  //login uspjesno obavljen i preusmjeravamo se na index.php
        }
    }
    catch(PDOException $e) {
        echo $e;
    }
}

function updateBestResult($userId, $score, $categoryName) {

    global $connection;

    try {
        $statement = $connection->prepare("SELECT * FROM korisnici WHERE id=:userId");  //prvo dohvati korisnika
        $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetchObject();

        $scoreFromDB = $result->bestResults;  //usporedi poslani score i score iz baze, ako je poslani score veci, onda update
        //baze sa tim novi scorom
        if($score > $scoreFromDB){
            $statement = $connection->prepare("UPDATE korisnici SET bestResults = :bestResults, bestResultsCategory = :category WHERE id=:userId");
            $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
            $statement->bindParam(':bestResults', $score, PDO::PARAM_INT);
            $statement->bindParam(':category', $categoryName, PDO::PARAM_STR);
            $statement->execute();

            updateScoreSession($score, $categoryName);
        }
    }
    catch(PDOException $e) {
        echo $e;
    }
}

function getUsers() {

    global $connection;

    try {
        $statement = $connection->prepare("SELECT * FROM korisnici WHERE id !=:userId");
        $statement->bindParam(':userId', getSessionUser()->id, PDO::PARAM_INT);
        $statement->execute();

        $resultArray = Array();
        while($item = $statement->fetchObject()){ //dobavi sve usere, idi po redovima i popunjava object user i dodaj ga u array
            $user = new User();
            $user->id = $item->id;
            $user->name = $item->user;
            $user->email = $item->email;
            $user->userType = $item->userType;
            $user->bestScore = $item->bestResults;
            $user->bestScoreCategoryName = $item->bestResultsCategory;

            array_push($resultArray, $user);
        }
        return $resultArray;
    }
    catch(PDOException $e) {
        echo $e;
    }
}

function setUserAdmin($userId) {

    global $connection;

    try {
        $statement = $connection->prepare("UPDATE korisnici SET userType = 1 WHERE id = :userId");
        $statement->bindParam(':userId', $userId,PDO::PARAM_INT);
        $statement->execute();
    }
    catch(PDOException $e) {
        echo $e;
    }
}