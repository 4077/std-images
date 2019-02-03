// head {
var __nodeId__ = "std_images_ui__main_images_grid";
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

            w.e('std/images/update.' + o.instance, function (data) {
                if (data.instance === o.instance) {
                    w.r('reload');
                }
            });

            w.e('std/images/selection_update.' + o.instance, function (data) {
                if (data.instance === o.instance) {
                    $(".image", $w).removeClass("checked");

                    $.each(data.selection, function (n, imageId) {
                        $(".image[image_id='" + imageId + "']", $w).addClass("checked");
                    });
                }
            });

            w.e('std/images/update/dirty.' + o.instance, function (data) {
                if (data.instance === o.instance) {
                    $(".image[image_id='" + data.imageId + "']", $w).find(".edit_button").toggleClass("dirty", data.dirty);
                }
            });
        },

        bind: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            $(".image", $w).click(function () {
                if (o.clickMode === 'select') {
                    w.r('toggleSelection', {
                        image_id: $(this).attr("image_id")
                    });
                }
            });

            $(".edit_button", $w).click(function (e) {
                w.r('edit', {
                    image_id: $(this).closest(".image").attr("image_id")
                });

                e.stopPropagation();
            });
        },

        getImagesCount: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            return $(".image", $w).length;
        }
    });
})(__nodeNs__, __nodeId__);
