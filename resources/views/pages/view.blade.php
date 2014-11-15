<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Website</title>
        <style>
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
                background-color: #eee;
                border-bottom: 1px solid #ccc;
                text-align: center;
            }

            .Header-title {
                font-weight: 200;
            }

            .Content {
                padding: 2em;
                min-height: 100vh;
            }

            .Footer {
                background-color: #333;
                color: #fff;
                padding: 2em;
            }
        </style>
    </head>
    <body>
        <header class="Header">
            <h1 class="Header-title">Oxygen Example Theme</h1>
        </header>
        <div class="Content">
            {{ $content }}
        </div>
        <header class="Footer">
            <p>Created by Oxygen</p>
        </header>
    </body>
</html>