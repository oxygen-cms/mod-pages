<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Website</title>
        <style>
            .cf:before,
            .cf:after {
                content: " ";
                display: table;
            }

            .cf:after {
                clear: both;
            }

            .cf {
                *zoom: 1;
            }

            html {
                font-family: sans-serif;
            }

            body {
                margin: 0;
            }

            p {
                line-height: 1.5;
            }

            .Header {
                padding: 2em;
                background-color: #fafafa;
                border-bottom: 1px solid #ccc;
            }

            .Header-title {
                font-weight: 200;
                float: left;
            }

            .Content {
                padding: 2em;
            }

            .Footer {
                background-color: #333;
                color: #fff;
                padding: 2em;
            }
        </style>
    </head>
    <body>
        <header class="Header cf">
            <h1 class="Header-title">Oxygen Example Theme</h1>
        </header>
        <div class="Content">
            {!! $content !!}
        </div>
        <footer class="Footer">
            <p>Created by Oxygen</p>
        </footer>
    </body>
</html>