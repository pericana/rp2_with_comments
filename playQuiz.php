<?php
    require_once 'user_session.php';
    if(isUserLogined() === -1){
        header("Location: login.php"); //ako korisnik nije logiran preusmjeri na login.php, pocetak do maina je isti kao i kod index.php
    }

    require_once 'classes/Question.php';

?>

<?php
    $title = "Igraj kviz";
    require_once "header.php";
?>

<div id="center">

    <?php
        require_once "user_info.php";
    ?>

    <div id="main">

        <input type="hidden" id="userId" value="<?php echo getSessionUser()->id; ?>"><!-- da u javiscript se moze uzeti id od ulogiranog korisnika-->

        <h3> Kviz </h3>

        <div style="margin-bottom: 50px">
            <p style="float: left">Bodovi: <span id="scoreSpan">0</span></p>
            <p style="float: right">trenutno pitanje <span id="currectQuestionSpan">0</span> od <span id="allQuestionSpan">0</span></p>
        </div>
        <br />


        <div id="dinamicContent"><!-- div za dinamicko kreiranje elemenata -->

        </div>

    </div>

</div>

<script>

    //globalne varijable koje ce se koristiti u igrici
    var categories; //sve kategorije
    getCategories();   //prvo dobavi sve kategorije i napravi select za biranje kategorije i button za pocetak igrice
    var selectedCategoryName;  //odabrana kategorija
    var questions = new Array();   //sva pitanja iz odabrane kategorije
    var currentQuestionStep = 1;    //trenutno pitanje
    var currentScore = 0;       //rezultat korisnika

    function getCategories(){ //dobavlajnje kategorija i ispisivanje kategorija i buttona

        $.ajax({
            url : "script_category.php",

            data :
                {
                    action : "get"
                },

            type: "GET",

            dataType : "json",

            success : function(data)
            {
                categories = data;
                showCategories();
            },
            error : function (xhr, status, errorThrown){
                alert("greska");
            }

        });

    }

    function showCategories() {  //napuni div dinamic kotent sa select od svih kategorija i buttonom kreni
        var categoryLabel = $("<label for='category'>Odaberite kategoriju</label>");
        var categorySelect = $("<select id='category' name='category' />");
        var all = $("<option></option>");
        all.val("-1");
        all.html("Sve kategorije");
        categorySelect.append(all);
        for(var i = 0; i < categories.length; i++){
            var option = $("<option></option>");
            option.val(categories[i].id);
            option.html(categories[i].category);
            categorySelect.append(option);
        }
        $("#dinamicContent").append(categoryLabel);
        $("#dinamicContent").append(categorySelect);

        var start = $("<button id='start' class='dinamic'>Kreni</button>");
        start.css("margin-left", "20px");
        start.css("width", "80px");
        $("#dinamicContent").append(start);

        start.on("click", function () { //dodaj click listener buttonu kreni
            startQuiz(); //kad se stisne kreni pocni kviz
        });
    }

    function startQuiz() { //dobavi pitanja za kategoriju
        var categoryId = $("#category option:selected").val();
        selectedCategoryName = $("#category option:selected").html(); //spremi selektiranu kategoriju - trebat ce kod spremanja najboljeg rezultata korisnika

        $.ajax({ //ajax za dobavljanje pitanja
            url : "script_question.php",

            data :
                {
                    action : "get",
                    cat_id: categoryId
                },

            type: "GET",

            dataType : "json",

            success : function(data)
            {
                for(var i = 0; i < data.length; i++){
                    console.log("QUESTION: " + data[i]['question']);
                }
                var max = 10;
                if(data.length < 10){
                    max = data.length;
                }
//Ako postoji vise od 10 pitanja, onda u globalnu varijablu spremi 10 random pitanja, a ako je manje od 10 onda spremi sva pitanja
                questions = new Array();
                for(var i = 0; i < max; i++){
                    var random = Math.floor(Math.random() * data.length);
                    console.log("RANDOM (0 i " + data.length + "): " + random);
                    questions.push(data[random]);
                    data.splice(random, 1);
                }

                for(var i = 0; i < questions.length; i++){
                    console.log("QUESTION: " + questions[i]['question']);
                }

                showQuestion(); //nako toga pokazi formu za prvo pitanje

            },
            error : function (xhr, status, errorThrown){
                alert("greska");
            }

        });

    }

    function showQuestion(){ //pokazi pitanje koje je na redu
        $("#dinamicContent").empty();
        var currentQuestion = questions[currentQuestionStep - 1];
        $("#currectQuestionSpan").html(currentQuestionStep);   //update trenutno pitanje i broj ukupnih pitanja
        $("#allQuestionSpan").html(questions.length);

        var selectedButtonForType2; // varijabla za kliknuti odgovor za type pitanja 2 i 4

        var questionParagraph = $("<p id='questionParagraph'></p>");  //dodaj paragraf sa pitanje u div
        questionParagraph.html(currentQuestion['question']);

        $("#dinamicContent").append(questionParagraph);

        if(currentQuestion['questionType'] == 3){ //ako je type 3, dodaj u sliku u div
            var img = $("<img />");
            img.attr("src", currentQuestion['imageForQuestion']);
            img.css("width", "50%");
            $("#dinamicContent").append(img);
        }

        //show answer form
        if(currentQuestion['questionType'] == 1 || currentQuestion['questionType'] == 3){  // ako su type 1 ili 3 dodaj input tipa text za upisat odgovor
            var answer = $("<input />");
            answer.attr("type", "text");
            answer.attr("id", "answer");
            answer.attr("placeholder", "Unesite odgovor");
            $("#dinamicContent").append("<br>");
            $("#dinamicContent").append(answer);
        }else if(currentQuestion['questionType'] == 2){// ako je type 2, onda dodaj 4 buttona sa tekstom iz ponudjenih odgovora
            var answers = currentQuestion['answers'];
            //var button1 = $("<button></button>");
            //button1.html(answers[i]['textAnswer'])
            for(var i = 0; i < answers.length; i++){

                var button = $("<button></button>");
                button.html(answers[i]['textAnswer']);
                button.val(answers[i]['isCorrect']);
                if(i % 2 == 0){
                    button.css("margin-left", "0px");
                }else{
                    button.css("margin-right", "0px");
                }
                button.attr("class", "buttonSelect");
                $("#dinamicContent").append(button);

                button.on("click", function () {  //dodaj click listener butonima
                    $(".buttonSelect").css("background-color", "");
                    $(this).css("background-color", "gray");
                    selectedButtonForType2 = $(this); //promjeni boju pozadine cliknutom button i spremi ga u varijablu selectedButtonForType2,
                    //da kasnije mozemo provjeriti jel tocan odgovor
                });
            }
        }else if(currentQuestion['questionType'] == 4){// ako je type 2, onda dodaj 2 buttona tocno i ne tocno

            var buttonTocno = $("<button></button>");
            buttonTocno.html("Točno");
            buttonTocno.val("true");
            buttonTocno.css("margin-left", "0px");
            buttonTocno.attr("class", "buttonSelect");
            $("#dinamicContent").append(buttonTocno);

            buttonTocno.on("click", function () {  // dodaj click na oba buttona, te mu promjeni boju i spremi ga u varijablu selectedButtonForType2,
                //da kasnije mozemo provjeriti jel tocan odgovor
                $(".buttonSelect").css("background-color", "");
                $(this).css("background-color", "gray");
                selectedButtonForType2 = $(this);
            });

            var buttonNetocno = $("<button></button>");
            buttonNetocno.html("Netočno");
            buttonNetocno.val("false");
            buttonNetocno.css("margin-right", "0px");
            buttonNetocno.attr("class", "buttonSelect");
            $("#dinamicContent").append(buttonNetocno);

            buttonNetocno.on("click", function () {
                $(".buttonSelect").css("background-color", "");
                $(this).css("background-color", "gray");
                selectedButtonForType2 = $(this);
            });

        }

        //answer question button
        var answerQuestion = $("<button></button>");  // dodaj odgovoi button u div
        answerQuestion.html("Odgovori");
        answerQuestion.attr("id", "answerButton");
        $("#dinamicContent").append("<br>");
        $("#dinamicContent").append(answerQuestion);

        answerQuestion.on("click", function () { //dodaj click listener za odgovri button, i na klik pozovi checkAnswer metodu koja provjerava da li je odgovor tocan
            checkAnswer(currentQuestion, selectedButtonForType2);
        });

    }

    function checkAnswer(currentQuestion, selectedButtonForType2) { //provjera da li je odgovor tocan
        //next question
        $("#answerButton").remove();
        $("#answer").attr("disabled", true);
        $(".answer").attr("disabled", true);
        $(".buttonSelect").attr("disabled", true);

        if(currentQuestion['questionType'] == 1 || currentQuestion['questionType'] == 3) { //ako je type 1 ili 3, usporedi kolonu iz baze correctAnswer sa tekstom iz
            //gore definiranog inputa za odgovor pitanja
            var answerText = $("#answer").val();

            if(answerText.toLowerCase() === currentQuestion['correctAnswer'].toLowerCase()){  // ako je odgovor tocan promjeni pozadinu inputa u zeleno i povecaj globalnu varijablu score
                $("#answer").css("background-color", "green");
                currentScore += parseInt(currentQuestion['questionScore']);
            }else{  //ako nije tocan, pozadina crvena i pokazi tocan odgovor
                $("#answer").css("background-color", "red");
                var correctAnswerIs = $("<p></p>");
                correctAnswerIs.html("Točan odgovor je: " + currentQuestion['correctAnswer']);
                $("#dinamicContent").append(correctAnswerIs);
            }
        }else if(currentQuestion['questionType'] == 2) { // ako je type 2, provjeri da li selectedButtonForType2 ima val() == 1 (pogledaj dodavanje buttona, ako je ponudjeni odgovor
            //is correct 1 onda se tom buttonu spremio val(1) )
            var isCorrect;
            if(selectedButtonForType2 == null){
                isCorrect = 0;
            }else{
                isCorrect = selectedButtonForType2.val();
            }
            if(isCorrect == 1){//ako je odgovor tocan promjeni pozadinu buttona u zeleno i povecaj globalnu varijablu score
                currentScore += parseInt(currentQuestion['questionScore']);
                selectedButtonForType2.css("background-color", "green");
            }else{//ako nije tocan, pozadina crvena i pozeleni tocan odgovor
                if(selectedButtonForType2 != null) selectedButtonForType2.css("background-color", "red");
                var buttons = $("button.buttonSelect");
                for(var i = 0; i < buttons.length; i++){
                    if(buttons.eq(i).val() == 1){
                        buttons.eq(i).css("background-color", "green");
                    }
                }
            }
        }else if(currentQuestion['questionType'] == 4) { // ako je type == 4, provjeri da li selectedButtonForType2 ima val() == correctanswer od trenutnog pitanja (pogledaj dodavanje buttona, ako je
            // tocno button onda ima val(true), a ako je netocno button onda ima val(false), a type 4 pitanja u bazi ima pod correctAnswer true ili false )
            var answer;
            if(selectedButtonForType2 == null){
                answer = "";
            }else{
                answer = selectedButtonForType2.val();
            }
            if(answer == currentQuestion['correctAnswer']){//ako je odgovor tocan promjeni pozadinu buttona u zeleno i povecaj globalnu varijablu score
                currentScore += parseInt(currentQuestion['questionScore']);
                selectedButtonForType2.css("background-color", "green");
            }else{//ako nije tocan, pozadina crvena i pozeleni tocan odgovor
                if(selectedButtonForType2 != null) selectedButtonForType2.css("background-color", "red");
                var buttons = $("button.buttonSelect");
                for(var i = 0; i < buttons.length; i++){
                    if(buttons.eq(i).val() == currentQuestion['correctAnswer']){
                        buttons.eq(i).css("background-color", "green");
                    }
                }
            }
        }

        $("#scoreSpan").html(currentScore); ///promjeni score

        var explanationH3 = $("<h3 style='margin-top: 20px'>Objašnjenje:</h3>");  // dodaj objasnjenje
        var explanationParagraph = $("<p id='explanation'></p>");
        explanationParagraph.html(currentQuestion['questionExplanation']);
        $("#dinamicContent").append(explanationH3);
        $("#dinamicContent").append(explanationParagraph);

        var nextQuestion = $("<button></button>");  //dodaj next button, a ako je zadnje pitanje onda button rezultat
        nextQuestion.attr("id", "nextQuestion");
        if((currentQuestionStep) === questions.length){
            nextQuestion.html("Rezultat");
        }else{
            nextQuestion.html("Sljedeće pitanje");
        }
        $("#dinamicContent").append(nextQuestion);

        nextQuestion.on("click", function () {  //click na button, ako je zadnje pitanje onda set result, a ako nije onda povecaj step i ponovo pozovi showQuestion
            if((currentQuestionStep) === questions.length){
                setResults();
            }else{
                currentQuestionStep++;
                showQuestion();
            }
        });
    }

    function setResults() { //prikazi u rezultat korisnika i dodaj link za vracanje na index.php
        $("#dinamicContent").empty();

        var yourScore = $("<p></p>");
        yourScore.html("Vaš rezultat je " + currentScore + " bodova");
        $("#dinamicContent").append(yourScore);

        var aHrefBack = $("<a>Gotovo</a>");
        aHrefBack.attr("href", "index.php");
        $("#dinamicContent").append(aHrefBack);

        console.log("userID: " +$("#userId").val());
        console.log("cat_name: " +selectedCategoryName);
        console.log("score: " +currentScore);

//preko ajaxa posalji novi score u bazu

        $.ajax({
            url : "script_user.php",

            data :
                {
                    action : "updateScore",
                    user_id: $("#userId").val(),
                    cat_name: selectedCategoryName,
                    score: currentScore
                },

            type: "POST",

            dataType : "json"

        });
    }

</script>
