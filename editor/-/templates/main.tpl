<div class="{__NODE_ID__}" instance="{__INSTANCE__}">

    <div class="screen">
        <div class="cp top">
            <div class="modify_buttons">
                {FIT_BUTTON}
                {FILL_BUTTON}
                {CENTER_BUTTON}
            </div>
            {RESET_BUTTON}
        </div>

        <div class="row row1 overlay"></div>
        <div class="row row2">
            <div class="col col1 overlay"></div>
            <div class="container" style="width: {CONTAINER_WIDTH}px; height: {CONTAINER_HEIGHT}px">
                <div class="image" style="top: {TOP}px; left: {LEFT}px; width: {WIDTH}px; height: {HEIGHT}px">
                    <img src="{SRC}" width="{WIDTH}" height="{HEIGHT}">
                    <div class="border"></div>
                </div>
                <div class="info">
                    <div class="scale" title="Масштаб"></div>
                    <div class="result_dimensions" title="Размер результирующей картинки"></div>
                </div>
                <div class="grid"></div>
            </div>
            <div class="col col3 overlay"></div>
        </div>
        <div class="row row3 overlay"></div>

        <div class="cp bottom">
            <div class="left">
                <div class="dimensions">
                    <input type="text" dimension="0" value="{CONTAINER_WIDTH}">
                    <input type="text" dimension="1" value="{CONTAINER_HEIGHT}">
                </div>
                <div class="helpers">
                    <div class="button grid">
                        <div class="rows">
                            <div class="row line"></div>
                            <div class="row line"></div>
                        </div>
                        <div class="cols">
                            <div class="col line"></div>
                            <div class="col line"></div>
                        </div>
                        <div class="divider_selector hidden">

                        </div>
                    </div>
                    <div class="button border">
                        <div class="icon"></div>
                    </div>
                </div>
            </div>
            <div class="right">
                <div class="force_jpeg_button {FORCE_JPEG_BUTTON_PRESSED_CLASS}">jpeg</div>
                <div class="save_buttons">
                    <div class="button save {SAVE_BUTTON_DISABLED_CLASS}" title="Ctrl+Enter">Сохранить</div>
                    <div class="button save_copy" title="Ctrl+Shift+Enter">Сохранить копию</div>
                </div>
            </div>
        </div>
    </div>

</div>
