$(document).ready(function() {
    /* Разбивает предложение на токены */
    function Tokenizer() {
        this.pattern = new RegExp("^[A-Za-zА-Яа-я0-9\'\"\-]$");

        this.getSymbolType = function(symbol) {
            var charCode = symbol.charCodeAt(0);

            if (charCode === 9 || charCode === 32) {
                return 0;
            }
            else if (this.pattern.exec(symbol) != null) {
                return 1;
            }
            else {
                return 2;
            }
        };

        this.tokenizeSentence = function(sentence) {
            tokSentence = "";
            sentence = sentence.trim();
            isDivider = false;

            if (sentence.length > 0) {
                tokSentence = sentence.substr(0, 1).toUpperCase();
            }

            for (var index = 1; index < sentence.length; index++) {
                var previousSymbol = sentence.substr(index - 1, 1);
                var currentSymbol = sentence.substr(index, 1);
                var previousSymbolType = this.getSymbolType(previousSymbol);
                var currentSymbolType = this.getSymbolType(currentSymbol);

                if (previousSymbolType !== currentSymbolType && !(isDivider)) {
                    tokSentence += "|";
                    isDivider = true;
                }

                if (currentSymbolType > 0) {
                    tokSentence += currentSymbol.toUpperCase();
                    isDivider = false;
                }
            }
            return tokSentence;
        };
    }

    /* Управляет процессом тестирования */
    function TestManager(tokenizer) {
        this.progressField = $("#text-progress");
        this.questionField = $("#text-question");
        this.answerField = $("#input-answer");
        this.tokenizer = tokenizer;
        this.questions = [];
        this.questionIndex = -1;

        this.start = function(data) {
            $(".start-screen").hide();
            $(".main-screen").show();

            if (data.length > 0) {
                this.answerField.removeAttr("disabled").focus();
                $("#button-answer").removeAttr("disabled");
                $("#button-help").removeAttr("disabled");
                this.questions = data;
                this.next();
            }
            else {
                this.progressField.text("");
                this.questionField.text("Error: Cannot load questions from DB !!!");
            }
        };

        this.next = function() {
            this.questionIndex++;

            if (this.questionIndex < this.questions.length) {
                this.progressField.text("Q. " + (this.questionIndex + 1).toString() + " : " + (this.questions.length).toString());
                this.questionField.text(this.questions[this.questionIndex].translation);
                this.answerField.val("");
            }
            else {
                this.endTest();
            }
        };

        this.end = function() {
            this.questionField.text("Test is passed !!!");
            this.answerField.attr("disabled", "disabled");
            this.answerField.val("");
            $("#button-answer").attr("disabled", "disabled");
            $("#button-help").attr("disabled", "disabled");
        };

        this.refreshAnswerColor = function(tokSentence, dbSentence) {
            if (tokSentence.length < dbSentence.length && dbSentence.indexOf(tokSentence) === 0) {
                this.answerField.css("color", "#436EEE");
            }
            else if (tokSentence.length === dbSentence.length && dbSentence.indexOf(tokSentence) === 0) {
                this.answerField.css("color", "#008000");
            }
            else {
                this.answerField.css("color", "#FF0000");
            }
        };

        this.onAnswer = function() {
            var tokSentence = this.tokenizer.tokenizeSentence(this.answerField.val());
            var sentence = this.questions[this.questionIndex].phrase;
            var dbSentence = this.tokenizer.tokenizeSentence(sentence);

            if (tokSentence === dbSentence) {
                $.ajax({
                    type: "PUT",
                    url: "/api/phrases/" + this.questions[this.questionIndex].id
                });
                this.next();
            }
            this.answerField.focus();
        };

        this.onHelp = function() {
            this.answerField.val(this.questions[this.questionIndex].phrase).focus();
            this.answerField.css("color", "#008000");
        };

        this.onAnswerFieldChange = function(e) {
            var tokSentence = this.tokenizer.tokenizeSentence(this.answerField.val());
            var sentence = this.questions[this.questionIndex].phrase;
            var dbSentence = this.tokenizer.tokenizeSentence(sentence);

            if (e.keyCode === 13 && tokSentence === dbSentence) {
                $.ajax({
                    type: "PUT",
                    url: "/api/phrases/" + this.questions[this.questionIndex].id
                });
                this.next();
            }
            else {
                this.refreshAnswerColor(tokSentence, dbSentence);
            }
        };

        this.startTest = function() {
            var self = this;

            $.ajax({
                type: "GET",
                url: "/api/phrases",
                data: {},
                success: function(data) {
                    self.start(data);
                },
                error: function(data) {
                    console.log(data);
                }
            });
        };

        this.endTest = function() {
            var self = this;
            self.end();
        };
    }

    var tokenizer = new Tokenizer();
    var testManager = new TestManager(tokenizer);

    /* Кнопка старта */
    $("#button-start").click(function() {
        testManager.startTest();
    });

    /* Кнопка ответа */
    $("#button-answer").click(function() {
        testManager.onAnswer();
    });

    /* Кнопка помощи */
    $("#button-help").click(function() {
        testManager.onHelp();
    });

    /* При изменении поля ввода ответа */
    $("#input-answer").keyup(function(e) {
        testManager.onAnswerFieldChange(e);
    });
});