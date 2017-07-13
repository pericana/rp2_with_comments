<?php
    require_once ('classes/Question.php');
    require_once ('classes/Answer.php');

function addQuestion($question, $answers, $correctAnswer) { //dodavanje pitanja u bazu , dobijemo question object, te asnwers polje ako je question type == 2
// i correct answer nam govori na kojoj je poziciji u polju tocan odgovor

    global $connection;

    try {
        $statement = $connection->prepare("INSERT INTO pitanja
                                                (question, questionType, questionScore, categoryId, imageForQuestion, correctAnswer, questionExplanation) VALUES
                                                (:question, :questionType, :questionScore, :categoryId, :imageForQuestion, :correctAnswer, :questionExplanation)");

        $statement->bindParam(':question', $question->question, PDO::PARAM_STR);
        $statement->bindParam(':questionType', $question->questionType, PDO::PARAM_INT);
        $statement->bindParam(':questionScore', $question->questionScore, PDO::PARAM_INT);
        $statement->bindParam(':categoryId', $question->categoryId, PDO::PARAM_INT);
        $statement->bindParam(':imageForQuestion', $question->imageForQuestion, PDO::PARAM_STR);
        $statement->bindParam(':correctAnswer', $question->correctAnswer, PDO::PARAM_STR);
        $statement->bindParam(':questionExplanation', $question->questionExplanation, PDO::PARAM_STR);

        $statement->execute();

        $questionId = $connection->lastInsertId(); //spremimo question u bazu i dobavimo id od pitanja kojeg je mysql sam generirao

        for($i=0;$i < sizeof($answers); $i++){  //ako je question type == 2, postoje ponudjeni odgovori i spremi ih u bazu
          $statement = $connection->prepare("INSERT INTO ponudjeniodgovori
                                                  (questionId, textAnswer, isCorrect) VALUES
                                                  (:questionId, :textAnswer, :isCorrect)");

          $isCorrect = 0;
          if($i === $correctAnswer){ //ako smo dosli do pozicije tocnog odgovora, spremi correct == 1
            $isCorrect = 1;
          }
          $statement->bindParam(':questionId', $questionId, PDO::PARAM_INT);
          $statement->bindParam(':textAnswer', $answers[$i], PDO::PARAM_STR);
          $statement->bindParam(':isCorrect', $isCorrect, PDO::PARAM_INT);

          $statement->execute();

        }

        return "";
    }
    catch(PDOException $e) {
        echo $e;
    }
}

function getQuestionByCategoryId($categoryId) { //dobavi pitanja za kategoriju

    global $connection;

    try {

        $statement = "";

        //ako je kategorija -1 onda dobavi sva pitanja (sve kategorije)
        if($categoryId == -1){
            $statement = $connection->prepare("SELECT * FROM pitanja");
        }else{
            $statement = $connection->prepare("SELECT * FROM pitanja WHERE categoryId = :categoryId");
            $statement->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        }

        $statement->execute();

        $resultArray = Array();
        while($item = $statement->fetchObject()){ //idi kroz redove dobivene iz baze, kreiraj novi question object
            // popuni ga podacima, ako je type 2 onda dobavi ponudjene odgovore sa questionId od questiona, idi
            // kroz redove i popuni object answer, te ga onda dodaj u array answers koji se nalazi u objektu question
            //i onda taj question dodaj u array koji ce metoda bvratiti
            $question = new Question();
            $question->id = $item->id;
            $question->question = $item->question;
            $question->questionType = $item->questionType;
            $question->imageForQuestion = $item->imageForQuestion;
            $question->correctAnswer = $item->correctAnswer;
            $question->questionScore = $item->questionScore;
            $question->questionExplanation = $item->questionExplanation;
            $question->categoryId = $item->categoryId;

            if($question->questionType == 2) {
                $statement2 = $connection->prepare("SELECT * FROM ponudjeniodgovori WHERE questionId = :questionId");
                $statement2->bindParam(':questionId', $question->id, PDO::PARAM_INT);
                $statement2->execute();
                $question->answers = array();
                while ($item2 = $statement2->fetchObject()) {
                    $answer = new Answer();
                    $answer->id = $item2->id;
                    $answer->questionId = $item2->questionId;
                    $answer->textAnswer = $item2->textAnswer;
                    $answer->isCorrect = $item2->isCorrect;
                    array_push($question->answers, $answer);
                }
            }

            array_push($resultArray, $question);
        }
        return $resultArray;

    }
    catch(PDOException $e) {
        echo $e;
    }
}
