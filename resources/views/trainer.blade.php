<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <title>Trainer</title>
        <link href="{{ asset('css/restyle.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/test-form.css') }}" rel="stylesheet">
        <script type="text/javascript" src="{{ asset('js/test-form.js') }}"></script>
    </head>
    <body>
        <div class="container">
            <div class="grid condensed">
                <div class="row start-screen">
                    <div class="cell">
                        <button class="button rounded primary" id="button-start">Начать упражнение</button>
                    </div>
                </div>
                <div class="row main-screen" style="display: none;">
                    <div class="cell">
                        <h5 id="text-progress">Загрузка ...</h5>
                    </div>
                </div>
                <div class="row main-screen" style="display: none;">
                    <div class="cell">
                        <h3 id="text-question">Загрузка ...</h3>
                    </div>
                </div>
                <div class="row main-screen" style="display: none;">
                    <div class="cell">
                        <div class="input-control input-text full-size">
                            <input id="input-answer" type="text" disabled="disabled">
                        </div>
                    </div>
                </div>
                <div class="row main-screen" style="display: none;">
                    <div class="cell">
                        <div id="button-container">
                            <button class="button rounded primary" id="button-answer" type="button" disabled="disabled">Ответить</button>
                            <button class="button rounded" id="button-open-symbol" type="button" disabled="disabled">Символ</button>
                            <button class="button rounded" id="button-open-word" type="button" disabled="disabled">Слово</button>
                            <button class="button rounded" id="button-open-sentence" type="button" disabled="disabled">Фраза</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
