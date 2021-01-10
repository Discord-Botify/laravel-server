<html>
<head>
    <title>@yield('title')</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

    <!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Aldrich' rel='stylesheet'>

    <!-- app.css  -->
    <link rel="stylesheet" href="{{asset('css/app.css')}}">

    <!-- typewriter JS -->
    <script>
        typeWriter = async (text, elementId) => {
            let i = 0;
            const speed = 75; /* The speed/duration of the effect in milliseconds */

            function sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }

            let currentText = '|';

            while (i < text.length) {
                currentText = currentText.substring(0, currentText.length - 1);
                currentText += text.charAt(i) + '|';
                document.getElementById(elementId).innerHTML = currentText;
                i++;
                await sleep(speed);
            }

            let hasPipe = true;

            while(true) {
                if(hasPipe === true) {
                    currentText = currentText.substring(0, currentText.length - 1);
                    document.getElementById(elementId).innerHTML = currentText;
                    hasPipe = false;
                } else {
                    currentText += '|';
                    document.getElementById(elementId).innerHTML = currentText;
                    hasPipe = true;
                }
                await sleep(500);
            }

        }
    </script>
</head>
<body>
    <div class="container-fluid h-100 w-100">
        <div class="row h-100 w-100">
            <div class="col-md-5 h-100 auth-splash d-flex align-items-center justify-content-start">
                <div class="display-1 ml-2" id="title"></div>
                <script>typeWriter('Welcome to Botify', 'title')</script>
            </div>
            <div class="col-md-7 h-100 d-flex align-items-center justify-content-center">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
