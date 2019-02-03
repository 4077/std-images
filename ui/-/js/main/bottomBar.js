// head {
var __nodeId__ = "std_images_ui__main_bottomBar";
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
            w.bindEvents();
        },

        bindEvents: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            w.e('std/images/selection_update.' + o.instance, function (data) {
                if (data.instance === o.instance) {
                    var imagesCount = w.w('images').getImagesCount();
                    var selectedImagesCount = data.selection.length;

                    $(".select_all.button", $w).toggleClass("disabled", imagesCount === selectedImagesCount);
                    $(".deselect.button", $w).toggleClass("disabled", selectedImagesCount === 0);
                    $(".copy.button", $w).toggleClass("disabled", selectedImagesCount === 0);
                    $(".delete.button", $w).toggleClass("disabled", selectedImagesCount === 0);
                }
            });

            w.e('std/images/buffer_update.' + o.instance, function (data) {
                // if (data.instance === o.instance) {
                    var $pasteButton = $(".paste.button", $w);
                    var count = data.count;

                    if (count) {
                        $pasteButton.removeClass("disabled");
                    } else {
                        $pasteButton.addClass("disabled");
                    }

                    $pasteButton.find(".count").html('(' + count + ')');
                // }

});
        },

        bind: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            $(".copy.button", $w).click(function () {
                w.r('copy');
            });

            $(".paste.button", $w).click(function () {
                w.r('paste');
            });

            $(".delete.button", $w).click(function () {
                w.r('delete');
            });

            $(".select_all.button", $w).click(function () {
                w.r('selectAll');
            });

            $(".deselect.button", $w).click(function () {
                w.r('deselect');
            });
        }
    });
})(__nodeNs__, __nodeId__);
