// head {
var __nodeId__ = "std_images_ui__main_topBar";
var __nodeNs__ = "std_images_ui";
// }

(function (__nodeNs__, __nodeId__) {
    $.widget(__nodeNs__ + "." + __nodeId__, $.ewma.node, {
        options: {},

        __create: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            w.bind();
        },

        bind: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            //

            var $urlInput = $(".input > input", $w);

            $urlInput.bind("paste", function () {
                setTimeout(function () {
                    loadFromUrl();
                });
            });

            $urlInput.bind("keyup", function (e) {
                if (e.which === 13) {
                    loadFromUrl();
                }
            });

            var loadFromUrl = function () {
                w.r('loadFromUrl', {
                    url: $urlInput.val()
                });
            };

            //

            $(".click_mode_button", $w).click(function () {
                w.r('clickModeToggle');
            });
        }
    });
})(__nodeNs__, __nodeId__);
