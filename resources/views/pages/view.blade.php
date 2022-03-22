<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Oxygen Example Theme</title>
        <style>
            .Header {
                background-color: #fafafa;
                border-bottom: 1px solid #ccc;
            }

            .Header-title {
                font-weight: 200;
            }

            .Content {

            }

            .Footer {
                position: fixed;
                bottom: 0;
                width: 100%;
                background-color: #333;
                color: #fff;
            }

            .Footer .Inner {
                padding: 1em 4em;
            }
        </style>
    </head>
    <body>
        <header class="Header">
            <div class="Inner">
                <h1 class="Header-title">Example Theme</h1>
            </div>
        </header>
        <div class="Content">
            @include('oxygen/mod-pages::pages.content')
        </div>
        <footer class="Footer">
            <div class="Inner">
                <small>Served to you by Oxygen CMS</small>
            </div>
        </footer>
    </body>
</html>