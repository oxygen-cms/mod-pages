<div id="content" class="Content-container">

    <div class="Content-toolbar">
        <button type="button" class="Content-refresh Button Button-color--white">
            <span class="Icon Icon-refresh Icon--pushRight"></span>
            Refresh
        </button>

        <button type="button" class="Content-collapseToggle Button Button-color--white">
            <span class="Toggle--ifDisabled">
                <span class="Icon Icon-expand Icon--pushRight"></span>
                Expand
            </span>
            <span class="Toggle--ifEnabled Toggle--isHidden">
                <span class="Icon Icon-times Icon--pushRight"></span>
                Exit
            </span>
        </button>
    </div>

    <iframe src="{{ URL::route($blueprint->getRouteName('getContent'), $item->getId()) }}" class="Content-preview"></iframe>

</div>

<?php Event::listen('oxygen.layout.body.after', function() { ?>

    <script>
        $(document).ready(function() {
            var body = $(document.body);
            var content = $("#content");

            $(".Content-refresh").on("click", function() {
                $(".Content-preview")[0].contentWindow.location.reload();
            });

            var toggle = new Oxygen.Toggle(
                $(".Content-collapseToggle"),
                function() {
                    body.addClass("Body--noScroll");
                    body.scrollTop(0);

                    content.addClass("Content-container--noTransition");

                    setTimeout(function() {
                        content.css({
                            position: "absolute",
                            top: content.offset().top,
                            left: content.offset().left,
                            width: content.width(),
                            height: content.height()
                        });

                        setTimeout(function() {
                            content.removeClass("Content-container--noTransition");
                            content.addClass("Content-container--fill");
                        }, 0);
                    }, 0)
                },
                function() {
                    body.removeClass("Body--noScroll");

                    content.removeClass("Content-container--fill");

                    setTimeout(function() {
                        content.addClass("Content-container--noTransition");

                        setTimeout(function() {
                            content.css({
                                position: "",
                                top: "",
                                left: "",
                                width: "",
                                height: ""
                            });

                            content.removeClass("Content-container--noTransition");
                        }, 0);
                    }, 500);
                }
            );
        });
    </script>

<?php }); ?>