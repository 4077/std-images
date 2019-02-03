// head {
var __nodeId__ = "std_images_editor__main";
var __nodeNs__ = "std_images_editor";
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

            $(window).rebind("keydown." + __nodeId__, function (e) {
                if (in_array(e.which, [37, 38, 39, 40])) {
                    var offset;

                    if (e.which === 38) {
                        offset = [0, -1];
                    }

                    if (e.which === 39) {
                        offset = [1, 0];
                    }

                    if (e.which === 40) {
                        offset = [0, 1];
                    }

                    if (e.which === 37) {
                        offset = [-1, 0];
                    }

                    if (!e.ctrlKey) {
                        offset[0] *= 10;
                        offset[1] *= 10;
                    }

                    o.offset[0] += offset[0];
                    o.offset[1] += offset[1];

                    w.mr('update', {
                        offset: o.offset
                    });

                    w.setDirty();
                    w.render();

                    e.preventDefault();
                }

                if (in_array(e.which, [187, 189, 220])) {
                    var scale = o.scale;

                    var widthBefore = o.base.size[0] * scale;
                    var heightBefore = o.base.size[1] * scale;

                    var k = e.ctrlKey ? 1.01 : 1.1;

                    if (e.which === 187) {
                        scale *= k;
                    }

                    if (e.which === 189) {
                        scale /= k;
                    }

                    if (e.which === 220) {
                        scale = 1;
                    }

                    var widthAfter = o.base.size[0] * scale;
                    var heightAfter = o.base.size[1] * scale;

                    o.scale = scale;

                    o.offset[0] += (widthBefore - widthAfter) / 2;
                    o.offset[1] += (heightBefore - heightAfter) / 2;

                    w.setDirty();
                    w.render();

                    w.mr('update', {
                        scale:  o.scale,
                        offset: o.offset
                    });

                    e.preventDefault();
                }

                if (e.which === 13 && e.ctrlKey) {
                    w.r('apply', {
                        replace:     !e.shiftKey,
                        ratio:       o.base.ratio,
                        base_offset: o.base.offset
                    });
                }

                e.stopPropagation();
            });

            var $image = $(".image", $w);

            var offsetUpdateTimeout;

            $image.draggable({
                drag: function (e, ui) {
                    o.offset[0] = ui.position.left;
                    o.offset[1] = ui.position.top;

                    w.setDirty();

                    clearTimeout(offsetUpdateTimeout);
                    offsetUpdateTimeout = setTimeout(function () {

                        w.mr('update', {
                            offset: o.offset
                        });
                    }, 200);
                }
            });

            var scaleUpdateTimeout;

            $w.rebind("mousewheel." + __nodeId__, function (e, d) {
                e.preventDefault();

                var widthBefore = o.base.size[0] * o.scale;
                var heightBefore = o.base.size[1] * o.scale;

                var k = e.ctrlKey ? 1.01 : 1.1;

                if (d === -1) {
                    o.scale /= k;
                } else {
                    o.scale *= k;
                }

                var widthAfter = o.base.size[0] * o.scale;
                var heightAfter = o.base.size[1] * o.scale;

                o.offset[0] += (widthBefore - widthAfter) / 2;
                o.offset[1] += (heightBefore - heightAfter) / 2;

                w.setDirty();
                w.render();

                clearTimeout(scaleUpdateTimeout);
                scaleUpdateTimeout = setTimeout(function () {
                    w.mr('update', {
                        scale:  o.scale,
                        offset: o.offset
                    });
                }, 200);
            });

            $(".center.button", $w).click(function () {
                $image.position({
                    "at": "center",
                    "of": $(".container", $w)
                });

                o.offset[0] = $image.position().left;
                o.offset[1] = $image.position().top;

                w.setDirty();
                w.mr('update', {
                    offset: o.offset
                });
            });

            $(".save.button", $w).click(function () {
                if (o.dirty) {
                    w.r('apply', {
                        replace:     true,
                        ratio:       o.base.ratio,
                        base_offset: o.base.offset
                    });
                }
            });

            $(".reset.button", $w).click(function () {
                w.r('reset');
            });

            $(".save_copy.button", $w).click(function () {
                w.r('apply', {
                    replace:     false,
                    ratio:       o.base.ratio,
                    base_offset: o.base.offset
                });
            });

            $(".dimensions input", $w).bind("keyup", function (e) {
                if (e.which === 13) {
                    w.setDirty();

                    w.r('updateDimension', {
                        dimension: $(this).attr("dimension"),
                        value:     $(this).val()
                    });
                }
            });

            //
            // grid
            //

            var $gridButton = $(".grid.button", $w);

            $gridButton.click(function () {
                o.grid.enabled = !o.grid.enabled;

                w.renderGrid(true);

                var $grid = $(".container > .grid", $w);

                $grid.toggleClass("hidden", !o.grid.enabled);

                w.r('updateGrid', o.grid);
            });

            $(window).bind("click." + __nodeId__, function () {
                $gridDividerSelector.hide();
            });

            $w.bind("click.grid", function () {
                $gridDividerSelector.hide();
            });

            $w.bind("contextmenu.grid", function () {
                $gridDividerSelector.hide();
            });

            var $gridDividerSelector = $(".divider_selector", $gridButton);

            var i;

            for (i = 2; i < 10; i++) {
                $("<div>")
                    .html(i)
                    .attr("divider", i)
                    .addClass("button")
                    .toggleClass("pressed", i === o.grid.divider)
                    .appendTo($gridDividerSelector);
            }

            $(".button", $gridDividerSelector).click(function (e) {
                e.stopPropagation();

                o.grid.enabled = true;
                o.grid.divider = $(this).attr("divider");

                var $grid = $(".container > .grid", $w);

                $grid.toggleClass("hidden", !o.grid.enabled);

                $(".button", $gridDividerSelector).removeClass("pressed");

                w.renderGrid(true);

                w.r('updateGrid', o.grid);
            });

            $gridButton.bind("contextmenu", function (e) {
                e.preventDefault();
                e.stopPropagation();

                if ($gridDividerSelector.is(":hidden")) {
                    $gridDividerSelector.css({display: "flex"});
                } else {
                    $gridDividerSelector.hide();
                }
            });

            //
            // border
            //

            var $borderButton = $(".border.button", $w);

            $borderButton.click(function () {
                o.border.enabled = !o.border.enabled;

                w.renderBorder();

                w.r('updateBorder', {
                    enabled: o.border.enabled
                });
            });

            //
            // force jpeg
            //

            var $forceJpegButton = $(".force_jpeg_button", $w);

            $forceJpegButton.click(function () {
                o.forceJpeg = !o.forceJpeg;

                $forceJpegButton.toggleClass("pressed", o.forceJpeg);

                w.r('updateForceJpeg', {
                    force_jpeg: o.forceJpeg
                });
            });

            //
            //
            //

            if (o.grid.enabled) {
                w.renderGrid();
            }

            w.renderBorder();

            w.render();
        },

        gridRendered: false,

        renderGrid: function (force) {
            var w = this;
            var o = w.options;
            var $w = w.element;

            if (!w.gridRendered || force) {
                var $grid = $(".container > .grid", $w);

                $grid.find("canvas").remove();

                var $canvas = $("<canvas>");

                var width = o.containerSize[0];
                var height = o.containerSize[1];

                $canvas.appendTo($grid).attr("width", width).attr("height", height);

                var divider = o.grid.divider;

                var ctx = $canvas.get(0).getContext("2d");

                var linesXInterval = Math.round(width / divider);
                var linesYInterval = Math.round(height / divider);

                ctx.strokeStyle = "#575757";
                ctx.lineWidth = 1;

                for (var i = 1; i < divider; i++) {
                    ctx.moveTo(linesXInterval * i + 0.5, 0);
                    ctx.lineTo(linesXInterval * i + 0.5, height);
                    ctx.moveTo(0, linesYInterval * i + 0.5);
                    ctx.lineTo(width, linesYInterval * i + 0.5);
                }

                ctx.stroke();

                var $gridButton = $(".grid.button", $w);

                $gridButton.toggleClass("pressed", o.grid.enabled);

                w.gridRendered = true;
            }
        },

        renderBorder: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            var $borderButton = $(".border.button", $w);
            var $border = $(".container > .image > .border", $w);

            $border.toggleClass("hidden", o.border.enabled);

            $borderButton.toggleClass("pressed", !o.border.enabled);
        },

        setDirty: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            if (!o.dirty) {
                $(".reset.button", $w).css({display: 'flex'});
                $(".save.button", $w).removeClass("disabled");

                w.r('dirty', {
                    dirty: true
                });

                o.dirty = true;
            }
        },

        render: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            var $image = $(".image", $w);

            var width = o.base.size[0] * o.scale;
            var height = o.base.size[1] * o.scale;

            $image
                .css({
                    left:   o.offset[0],
                    top:    o.offset[1],
                    width:  width,
                    height: height
                })
                .find("img")
                .attr("width", width)
                .attr("height", height);

            var $resultDimensions = $(".result_dimensions", $w);

            $resultDimensions.html(Math.round(o.containerSize[0] / o.base.ratio / o.scale) + " x " + Math.round(o.containerSize[1] / o.base.ratio / o.scale));

            var $scale = $(".scale", $w);

            $scale.html(Math.round(o.scale * 100) / 100);
        }
    });
})(__nodeNs__, __nodeId__);
