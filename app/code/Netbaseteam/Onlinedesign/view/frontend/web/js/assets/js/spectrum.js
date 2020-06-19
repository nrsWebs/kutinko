/* Spectrum Colorpicker v1.8.0 */
(function (factory) {
    if (typeof define === "function" && define.amd) define(["jquery"], factory); else if (typeof exports == "object" && typeof module == "object") module.exports = factory(require("jquery")); else factory(jQuery)
})(function ($, undefined) {
    var defaultOpts = {
            beforeShow: noop,
            move: noop,
            change: noop,
            show: noop,
            hide: noop,
            color: false,
            flat: false,
            showInput: false,
            allowEmpty: false,
            showButtons: true,
            clickoutFiresChange: true,
            showInitial: false,
            showPalette: false,
            showPaletteOnly: false,
            hideAfterPaletteSelect: false,
            togglePaletteOnly: false,
            showSelectionPalette: true,
            localStorageKey: false,
            appendTo: "body",
            maxSelectionSize: 7,
            cancelText: "cancel",
            chooseText: "choose",
            togglePaletteMoreText: "more",
            togglePaletteLessText: "less",
            clearText: "Clear Color Selection",
            noColorSelectedText: "No Color Selected",
            preferredFormat: false,
            className: "",
            containerClassName: "",
            replacerClassName: "",
            showAlpha: false,
            theme: "sp-light",
            palette: [["#ffffff", "#000000", "#ff0000", "#ff8000", "#ffff00", "#008000", "#0000ff", "#4b0082", "#9400d3"]],
            selectionPalette: [],
            disabled: false,
            offset: null
        }, spectrums = [], IE = !!/msie/i.exec(window.navigator.userAgent), rgbaSupport = function () {
            function contains(str, substr) {
                return !!~("" + str).indexOf(substr)
            }

            var elem = document.createElement("div");
            var style = elem.style;
            style.cssText = "background-color:rgba(0,0,0,.5)";
            return contains(style.backgroundColor, "rgba") || contains(style.backgroundColor, "hsla")
        }(),
        replaceInput = ["<div class='sp-replacer'>", "<div class='sp-preview'><div class='sp-preview-inner'></div></div>", "<div class='sp-dd'>&#9660;</div>",
            "</div>"].join(""), markup = function () {
            var gradientFix = "";
            if (IE) for (var i = 1; i <= 6; i++) gradientFix += "<div class='sp-" + i + "'></div>";
            return ["<div class='sp-container sp-hidden'>", "<div class='sp-palette-container'>", "<div class='sp-palette sp-thumb sp-cf'></div>", "<div class='sp-palette-button-container sp-cf'>", "<button type='button' class='sp-palette-toggle'></button>", "</div>", "</div>", "<div class='sp-picker-container'>", "<div class='sp-top sp-cf'>", "<div class='sp-fill'></div>", "<div class='sp-top-inner'>",
                "<div class='sp-color'>", "<div class='sp-sat'>", "<div class='sp-val'>", "<div class='sp-dragger'></div>", "</div>", "</div>", "</div>", "<div class='sp-clear sp-clear-display'>", "</div>", "<div class='sp-hue'>", "<div class='sp-slider'></div>", gradientFix, "</div>", "</div>", "<div class='sp-alpha'><div class='sp-alpha-inner'><div class='sp-alpha-handle'></div></div></div>", "</div>", "<div class='sp-input-container sp-cf'>", "<input class='sp-input' type='text' spellcheck='false'  />", "</div>", "<div class='sp-initial sp-thumb sp-cf'></div>",
                "<div class='sp-button-container sp-cf'>", "<a class='sp-cancel' href='#'></a>", "<button type='button' class='sp-choose'></button>", "</div>", "</div>", "</div>"].join("")
        }();

    function paletteTemplate(p, color, className, opts) {
        var html = [];
        for (var i = 0; i < p.length; i++) {
            var current = p[i];
            if (current) {
                var tiny = tinycolor(current);
                var c = tiny.toHsl().l < .5 ? "sp-thumb-el sp-thumb-dark" : "sp-thumb-el sp-thumb-light";
                c += tinycolor.equals(color, current) ? " sp-thumb-active" : "";
                var formattedString = tiny.toString(opts.preferredFormat ||
                    "rgb");
                var swatchStyle = rgbaSupport ? "background-color:" + tiny.toRgbString() : "filter:" + tiny.toFilter();
                html.push('<span title="' + formattedString + '" data-color="' + tiny.toRgbString() + '" class="' + c + '"><span class="sp-thumb-inner" style="' + swatchStyle + ';" /></span>')
            } else {
                var cls = "sp-clear-display";
                html.push($("<div />").append($('<span data-color="" style="background-color:transparent;" class="' + cls + '"></span>').attr("title", opts.noColorSelectedText)).html())
            }
        }
        return "<div class='sp-cf " + className + "'>" +
            html.join("") + "</div>"
    }

    function hideAll() {
        for (var i = 0; i < spectrums.length; i++) if (spectrums[i]) spectrums[i].hide()
    }

    function instanceOptions(o, callbackContext) {
        var opts = $.extend({}, defaultOpts, o);
        opts.callbacks = {
            "move": bind(opts.move, callbackContext),
            "change": bind(opts.change, callbackContext),
            "show": bind(opts.show, callbackContext),
            "hide": bind(opts.hide, callbackContext),
            "beforeShow": bind(opts.beforeShow, callbackContext)
        };
        return opts
    }

    function spectrum(element, o) {
        var opts = instanceOptions(o, element), flat =
                opts.flat, showSelectionPalette = opts.showSelectionPalette, localStorageKey = opts.localStorageKey,
            theme = opts.theme, callbacks = opts.callbacks, resize = throttle(reflow, 10), visible = false,
            isDragging = false, dragWidth = 0, dragHeight = 0, dragHelperHeight = 0, slideHeight = 0, slideWidth = 0,
            alphaWidth = 0, alphaSlideHelperWidth = 0, slideHelperHeight = 0, currentHue = 0, currentSaturation = 0,
            currentValue = 0, currentAlpha = 1, palette = [], paletteArray = [], paletteLookup = {},
            selectionPalette = opts.selectionPalette.slice(0), maxSelectionSize = opts.maxSelectionSize,
            draggingClass = "sp-dragging", shiftMovementDirection = null;
        var doc = element.ownerDocument, body = doc.body, boundElement = $(element), disabled = false,
            container = $(markup, doc).addClass(theme), pickerContainer = container.find(".sp-picker-container"),
            dragger = container.find(".sp-color"), dragHelper = container.find(".sp-dragger"),
            slider = container.find(".sp-hue"), slideHelper = container.find(".sp-slider"),
            alphaSliderInner = container.find(".sp-alpha-inner"), alphaSlider = container.find(".sp-alpha"),
            alphaSlideHelper = container.find(".sp-alpha-handle"),
            textInput = container.find(".sp-input"), paletteContainer = container.find(".sp-palette"),
            initialColorContainer = container.find(".sp-initial"), cancelButton = container.find(".sp-cancel"),
            clearButton = container.find(".sp-clear"), chooseButton = container.find(".sp-choose"),
            toggleButton = container.find(".sp-palette-toggle"), isInput = boundElement.is("input"),
            isInputTypeColor = isInput && boundElement.attr("type") === "color" && inputTypeColorSupport(),
            shouldReplace = isInput && !flat,
            replacer = shouldReplace ? $(replaceInput).addClass(theme).addClass(opts.className).addClass(opts.replacerClassName) :
                $([]), offsetElement = shouldReplace ? replacer : boundElement,
            previewElement = replacer.find(".sp-preview-inner"),
            initialColor = opts.color || isInput && boundElement.val(), colorOnShow = false,
            currentPreferredFormat = opts.preferredFormat,
            clickoutFiresChange = !opts.showButtons || opts.clickoutFiresChange, isEmpty = !initialColor,
            allowEmpty = opts.allowEmpty && !isInputTypeColor;

        function applyOptions() {
            if (opts.showPaletteOnly) opts.showPalette = true;
            toggleButton.text(opts.showPaletteOnly ? opts.togglePaletteMoreText : opts.togglePaletteLessText);
            if (opts.palette) {
                palette = opts.palette.slice(0);
                paletteArray = $.isArray(palette[0]) ? palette : [palette];
                paletteLookup = {};
                for (var i = 0; i < paletteArray.length; i++) for (var j = 0; j < paletteArray[i].length; j++) {
                    var rgb = tinycolor(paletteArray[i][j]).toRgbString();
                    paletteLookup[rgb] = true
                }
            }
            container.toggleClass("sp-flat", flat);
            container.toggleClass("sp-input-disabled", !opts.showInput);
            container.toggleClass("sp-alpha-enabled", opts.showAlpha);
            container.toggleClass("sp-clear-enabled", allowEmpty);
            container.toggleClass("sp-buttons-disabled",
                !opts.showButtons);
            container.toggleClass("sp-palette-buttons-disabled", !opts.togglePaletteOnly);
            container.toggleClass("sp-palette-disabled", !opts.showPalette);
            container.toggleClass("sp-palette-only", opts.showPaletteOnly);
            container.toggleClass("sp-initial-disabled", !opts.showInitial);
            container.addClass(opts.className).addClass(opts.containerClassName);
            reflow()
        }

        function initialize() {
            if (IE) container.find("*:not(input)").attr("unselectable", "on");
            applyOptions();
            if (shouldReplace) boundElement.after(replacer).hide();
            if (!allowEmpty) clearButton.hide();
            if (flat) boundElement.after(container).hide(); else {
                var appendTo = opts.appendTo === "parent" ? boundElement.parent() : $(opts.appendTo);
                if (appendTo.length !== 1) appendTo = $("body");
                appendTo.append(container)
            }
            updateSelectionPaletteFromStorage();
            offsetElement.bind("click.spectrum touchstart.spectrum", function (e) {
                if (!disabled) toggle();
                e.stopPropagation();
                if (!$(e.target).is("input")) e.preventDefault()
            });
            if (boundElement.is(":disabled") || opts.disabled === true) disable();
            container.click(stopPropagation);
            textInput.change(setFromTextInput);
            textInput.bind("paste", function () {
                setTimeout(setFromTextInput, 1)
            });
            textInput.keydown(function (e) {
                if (e.keyCode == 13) setFromTextInput()
            });
            cancelButton.text(opts.cancelText);
            cancelButton.bind("click.spectrum", function (e) {
                e.stopPropagation();
                e.preventDefault();
                revert();
                hide()
            });
            clearButton.attr("title", opts.clearText);
            clearButton.bind("click.spectrum", function (e) {
                e.stopPropagation();
                e.preventDefault();
                isEmpty = true;
                move();
                if (flat) updateOriginalInput(true)
            });
            chooseButton.text(opts.chooseText);
            chooseButton.bind("click.spectrum", function (e) {
                e.stopPropagation();
                e.preventDefault();
                if (IE && textInput.is(":focus")) textInput.trigger("change");
                if (isValid()) {
                    updateOriginalInput(true);
                    hide()
                }
            });
            toggleButton.text(opts.showPaletteOnly ? opts.togglePaletteMoreText : opts.togglePaletteLessText);
            toggleButton.bind("click.spectrum", function (e) {
                e.stopPropagation();
                e.preventDefault();
                opts.showPaletteOnly = !opts.showPaletteOnly;
                if (!opts.showPaletteOnly && !flat) container.css("left", "-=" + (pickerContainer.outerWidth(true) +
                    5));
                applyOptions()
            });
            draggable(alphaSlider, function (dragX, dragY, e) {
                currentAlpha = dragX / alphaWidth;
                isEmpty = false;
                if (e.shiftKey) currentAlpha = Math.round(currentAlpha * 10) / 10;
                move()
            }, dragStart, dragStop);
            draggable(slider, function (dragX, dragY) {
                currentHue = parseFloat(dragY / slideHeight);
                isEmpty = false;
                if (!opts.showAlpha) currentAlpha = 1;
                move()
            }, dragStart, dragStop);
            draggable(dragger, function (dragX, dragY, e) {
                    if (!e.shiftKey) shiftMovementDirection = null; else if (!shiftMovementDirection) {
                        var oldDragX = currentSaturation *
                            dragWidth;
                        var oldDragY = dragHeight - currentValue * dragHeight;
                        var furtherFromX = Math.abs(dragX - oldDragX) > Math.abs(dragY - oldDragY);
                        shiftMovementDirection = furtherFromX ? "x" : "y"
                    }
                    var setSaturation = !shiftMovementDirection || shiftMovementDirection === "x";
                    var setValue = !shiftMovementDirection || shiftMovementDirection === "y";
                    if (setSaturation) currentSaturation = parseFloat(dragX / dragWidth);
                    if (setValue) currentValue = parseFloat((dragHeight - dragY) / dragHeight);
                    isEmpty = false;
                    if (!opts.showAlpha) currentAlpha = 1;
                    move()
                }, dragStart,
                dragStop);
            if (!!initialColor) {
                set(initialColor);
                updateUI();
                currentPreferredFormat = opts.preferredFormat || tinycolor(initialColor).format;
                addColorToSelectionPalette(initialColor)
            } else updateUI();
            if (flat) show();

            function paletteElementClick(e) {
                if (e.data && e.data.ignore) {
                    set($(e.target).closest(".sp-thumb-el").data("color"));
                    move()
                } else {
                    set($(e.target).closest(".sp-thumb-el").data("color"));
                    move();
                    updateOriginalInput(true);
                    if (opts.hideAfterPaletteSelect) hide()
                }
                return false
            }

            var paletteEvent = IE ? "mousedown.spectrum" :
                "click.spectrum touchstart.spectrum";
            paletteContainer.delegate(".sp-thumb-el", paletteEvent, paletteElementClick);
            initialColorContainer.delegate(".sp-thumb-el:nth-child(1)", paletteEvent, {ignore: true}, paletteElementClick)
        }

        function updateSelectionPaletteFromStorage() {
            if (localStorageKey && window.localStorage) {
                try {
                    var oldPalette = window.localStorage[localStorageKey].split(",#");
                    if (oldPalette.length > 1) {
                        delete window.localStorage[localStorageKey];
                        $.each(oldPalette, function (i, c) {
                            addColorToSelectionPalette(c)
                        })
                    }
                } catch (e) {
                }
                try {
                    selectionPalette =
                        window.localStorage[localStorageKey].split(";")
                } catch (e$0) {
                }
            }
        }

        function addColorToSelectionPalette(color) {
            if (showSelectionPalette) {
                var rgb = tinycolor(color).toRgbString();
                if (!paletteLookup[rgb] && $.inArray(rgb, selectionPalette) === -1) {
                    selectionPalette.push(rgb);
                    while (selectionPalette.length > maxSelectionSize) selectionPalette.shift()
                }
                if (localStorageKey && window.localStorage) try {
                    window.localStorage[localStorageKey] = selectionPalette.join(";")
                } catch (e) {
                }
            }
        }

        function getUniqueSelectionPalette() {
            var unique = [];
            if (opts.showPalette) for (var i = 0; i < selectionPalette.length; i++) {
                var rgb = tinycolor(selectionPalette[i]).toRgbString();
                if (!paletteLookup[rgb]) unique.push(selectionPalette[i])
            }
            return unique.reverse().slice(0, opts.maxSelectionSize)
        }

        function drawPalette() {
            var currentColor = get();
            var html = $.map(paletteArray, function (palette, i) {
                return paletteTemplate(palette, currentColor, "sp-palette-row sp-palette-row-" + i, opts)
            });
            updateSelectionPaletteFromStorage();
            if (selectionPalette) html.push(paletteTemplate(getUniqueSelectionPalette(),
                currentColor, "sp-palette-row sp-palette-row-selection", opts));
            paletteContainer.html(html.join(""))
        }

        function drawInitial() {
            if (opts.showInitial) {
                var initial = colorOnShow;
                var current = get();
                initialColorContainer.html(paletteTemplate([initial, current], current, "sp-palette-row-initial", opts))
            }
        }

        function dragStart() {
            if (dragHeight <= 0 || dragWidth <= 0 || slideHeight <= 0) reflow();
            isDragging = true;
            container.addClass(draggingClass);
            shiftMovementDirection = null;
            boundElement.trigger("dragstart.spectrum", [get()])
        }

        function dragStop() {
            isDragging =
                false;
            container.removeClass(draggingClass);
            boundElement.trigger("dragstop.spectrum", [get()])
        }

        function setFromTextInput() {
            var value = textInput.val();
            if ((value === null || value === "") && allowEmpty) {
                set(null);
                updateOriginalInput(true)
            } else {
                var tiny = tinycolor(value);
                if (tiny.isValid()) {
                    set(tiny);
                    updateOriginalInput(true)
                } else textInput.addClass("sp-validation-error")
            }
        }

        function toggle() {
            if (visible) hide(); else show()
        }

        function show() {
            var event = $.Event("beforeShow.spectrum");
            if (visible) {
                reflow();
                return
            }
            boundElement.trigger(event,
                [get()]);
            if (callbacks.beforeShow(get()) === false || event.isDefaultPrevented()) return;
            hideAll();
            visible = true;
            $(doc).bind("keydown.spectrum", onkeydown);
            $(doc).bind("click.spectrum", clickout);
            $(window).bind("resize.spectrum", resize);
            replacer.addClass("sp-active");
            container.removeClass("sp-hidden");
            reflow();
            updateUI();
            colorOnShow = get();
            drawInitial();
            callbacks.show(colorOnShow);
            boundElement.trigger("show.spectrum", [colorOnShow])
        }

        function onkeydown(e) {
            if (e.keyCode === 27) hide()
        }

        function clickout(e) {
            if (e.button ==
                2) return;
            if (isDragging) return;
            if (clickoutFiresChange) updateOriginalInput(true); else revert();
            hide()
        }

        function hide() {
            if (!visible || flat) return;
            visible = false;
            $(doc).unbind("keydown.spectrum", onkeydown);
            $(doc).unbind("click.spectrum", clickout);
            $(window).unbind("resize.spectrum", resize);
            replacer.removeClass("sp-active");
            container.addClass("sp-hidden");
            callbacks.hide(get());
            boundElement.trigger("hide.spectrum", [get()])
        }

        function revert() {
            set(colorOnShow, true)
        }

        function set(color, ignoreFormatChange) {
            if (tinycolor.equals(color,
                get())) {
                updateUI();
                return
            }
            var newColor, newHsv;
            if (!color && allowEmpty) isEmpty = true; else {
                isEmpty = false;
                newColor = tinycolor(color);
                newHsv = newColor.toHsv();
                currentHue = newHsv.h % 360 / 360;
                currentSaturation = newHsv.s;
                currentValue = newHsv.v;
                currentAlpha = newHsv.a
            }
            updateUI();
            if (newColor && newColor.isValid() && !ignoreFormatChange) currentPreferredFormat = opts.preferredFormat || newColor.getFormat()
        }

        function get(opts) {
            opts = opts || {};
            if (allowEmpty && isEmpty) return null;
            return tinycolor.fromRatio({
                h: currentHue, s: currentSaturation,
                v: currentValue, a: Math.round(currentAlpha * 100) / 100
            }, {format: opts.format || currentPreferredFormat})
        }

        function isValid() {
            return !textInput.hasClass("sp-validation-error")
        }

        function move() {
            updateUI();
            callbacks.move(get());
            boundElement.trigger("move.spectrum", [get()])
        }

        function updateUI() {
            textInput.removeClass("sp-validation-error");
            updateHelperLocations();
            var flatColor = tinycolor.fromRatio({h: currentHue, s: 1, v: 1});
            dragger.css("background-color", flatColor.toHexString());
            var format = currentPreferredFormat;
            if (currentAlpha <
                1 && !(currentAlpha === 0 && format === "name")) if (format === "hex" || format === "hex3" || format === "hex6" || format === "name") format = "rgb";
            var realColor = get({format: format}), displayColor = "";
            previewElement.removeClass("sp-clear-display");
            previewElement.css("background-color", "transparent");
            if (!realColor && allowEmpty) previewElement.addClass("sp-clear-display"); else {
                var realHex = realColor.toHexString(), realRgb = realColor.toRgbString();
                if (rgbaSupport || realColor.alpha === 1) previewElement.css("background-color", realRgb); else {
                    previewElement.css("background-color",
                        "transparent");
                    previewElement.css("filter", realColor.toFilter())
                }
                if (opts.showAlpha) {
                    var rgb = realColor.toRgb();
                    rgb.a = 0;
                    var realAlpha = tinycolor(rgb).toRgbString();
                    var gradient = "linear-gradient(left, " + realAlpha + ", " + realHex + ")";
                    if (IE) alphaSliderInner.css("filter", tinycolor(realAlpha).toFilter({gradientType: 1}, realHex)); else {
                        alphaSliderInner.css("background", "-webkit-" + gradient);
                        alphaSliderInner.css("background", "-moz-" + gradient);
                        alphaSliderInner.css("background", "-ms-" + gradient);
                        alphaSliderInner.css("background",
                            "linear-gradient(to right, " + realAlpha + ", " + realHex + ")")
                    }
                }
                displayColor = realColor.toString(format)
            }
            if (opts.showInput) textInput.val(displayColor);
            if (opts.showPalette) drawPalette();
            drawInitial()
        }

        function updateHelperLocations() {
            var s = currentSaturation;
            var v = currentValue;
            if (allowEmpty && isEmpty) {
                alphaSlideHelper.hide();
                slideHelper.hide();
                dragHelper.hide()
            } else {
                alphaSlideHelper.show();
                slideHelper.show();
                dragHelper.show();
                var dragX = s * dragWidth;
                var dragY = dragHeight - v * dragHeight;
                dragX = Math.max(-dragHelperHeight,
                    Math.min(dragWidth - dragHelperHeight, dragX - dragHelperHeight));
                dragY = Math.max(-dragHelperHeight, Math.min(dragHeight - dragHelperHeight, dragY - dragHelperHeight));
                dragHelper.css({"top": dragY + "px", "left": dragX + "px"});
                var alphaX = currentAlpha * alphaWidth;
                alphaSlideHelper.css({"left": alphaX - alphaSlideHelperWidth / 2 + "px"});
                var slideY = currentHue * slideHeight;
                slideHelper.css({"top": slideY - slideHelperHeight + "px"})
            }
        }

        function updateOriginalInput(fireCallback) {
            var color = get(), displayColor = "", hasChanged = !tinycolor.equals(color,
                colorOnShow);
            if (color) {
                displayColor = color.toString(currentPreferredFormat);
                addColorToSelectionPalette(color)
            }
            if (isInput) boundElement.val(displayColor);
            if (fireCallback && hasChanged) {
                callbacks.change(color);
                boundElement.trigger("change", [color])
            }
        }

        function reflow() {
            if (!visible) return;
            dragWidth = dragger.width();
            dragHeight = dragger.height();
            dragHelperHeight = dragHelper.height();
            slideWidth = slider.width();
            slideHeight = slider.height();
            slideHelperHeight = slideHelper.height();
            alphaWidth = alphaSlider.width();
            alphaSlideHelperWidth =
                alphaSlideHelper.width();
            if (!flat) {
                container.css("position", "absolute");
                if (opts.offset) container.offset(opts.offset); else container.offset(getOffset(container, offsetElement))
            }
            updateHelperLocations();
            if (opts.showPalette) drawPalette();
            boundElement.trigger("reflow.spectrum")
        }

        function destroy() {
            boundElement.show();
            offsetElement.unbind("click.spectrum touchstart.spectrum");
            container.remove();
            replacer.remove();
            spectrums[spect.id] = null
        }

        function option(optionName, optionValue) {
            if (optionName === undefined) return $.extend({},
                opts);
            if (optionValue === undefined) return opts[optionName];
            opts[optionName] = optionValue;
            if (optionName === "preferredFormat") currentPreferredFormat = opts.preferredFormat;
            applyOptions()
        }

        function enable() {
            disabled = false;
            boundElement.attr("disabled", false);
            offsetElement.removeClass("sp-disabled")
        }

        function disable() {
            hide();
            disabled = true;
            boundElement.attr("disabled", true);
            offsetElement.addClass("sp-disabled")
        }

        function setOffset(coord) {
            opts.offset = coord;
            reflow()
        }

        initialize();
        var spect = {
            show: show, hide: hide, toggle: toggle,
            reflow: reflow, option: option, enable: enable, disable: disable, offset: setOffset, set: function (c) {
                set(c);
                updateOriginalInput()
            }, get: get, destroy: destroy, container: container
        };
        spect.id = spectrums.push(spect) - 1;
        return spect
    }

    function getOffset(picker, input) {
        var extraY = 0;
        var dpWidth = picker.outerWidth();
        var dpHeight = picker.outerHeight();
        var inputHeight = input.outerHeight();
        var doc = picker[0].ownerDocument;
        var docElem = doc.documentElement;
        var viewWidth = docElem.clientWidth + $(doc).scrollLeft();
        var viewHeight = docElem.clientHeight +
            $(doc).scrollTop();
        var offset = input.offset();
        offset.top += inputHeight;
        offset.left -= Math.min(offset.left, offset.left + dpWidth > viewWidth && viewWidth > dpWidth ? Math.abs(offset.left + dpWidth - viewWidth) : 0);
        offset.top -= Math.min(offset.top, offset.top + dpHeight > viewHeight && viewHeight > dpHeight ? Math.abs(dpHeight + inputHeight - extraY) : extraY);
        return offset
    }

    function noop() {
    }

    function stopPropagation(e) {
        e.stopPropagation()
    }

    function bind(func, obj) {
        var slice = Array.prototype.slice;
        var args = slice.call(arguments, 2);
        return function () {
            return func.apply(obj,
                args.concat(slice.call(arguments)))
        }
    }

    function draggable(element, onmove, onstart, onstop) {
        onmove = onmove || function () {
        };
        onstart = onstart || function () {
        };
        onstop = onstop || function () {
        };
        var doc = document;
        var dragging = false;
        var offset = {};
        var maxHeight = 0;
        var maxWidth = 0;
        var hasTouch = "ontouchstart" in window;
        var duringDragEvents = {};
        duringDragEvents["selectstart"] = prevent;
        duringDragEvents["dragstart"] = prevent;
        duringDragEvents["touchmove mousemove"] = move;
        duringDragEvents["touchend mouseup"] = stop;

        function prevent(e) {
            if (e.stopPropagation) e.stopPropagation();
            if (e.preventDefault) e.preventDefault();
            e.returnValue = false
        }

        function move(e) {
            if (dragging) {
                if (IE && doc.documentMode < 9 && !e.button) return stop();
                var t0 = e.originalEvent && e.originalEvent.touches && e.originalEvent.touches[0];
                var pageX = t0 && t0.pageX || e.pageX;
                var pageY = t0 && t0.pageY || e.pageY;
                var dragX = Math.max(0, Math.min(pageX - offset.left, maxWidth));
                var dragY = Math.max(0, Math.min(pageY - offset.top, maxHeight));
                if (hasTouch) prevent(e);
                onmove.apply(element, [dragX, dragY, e])
            }
        }

        function start(e) {
            var rightclick = e.which ?
                e.which == 3 : e.button == 2;
            if (!rightclick && !dragging) if (onstart.apply(element, arguments) !== false) {
                dragging = true;
                maxHeight = $(element).height();
                maxWidth = $(element).width();
                offset = $(element).offset();
                $(doc).bind(duringDragEvents);
                $(doc.body).addClass("sp-dragging");
                move(e);
                prevent(e)
            }
        }

        function stop() {
            if (dragging) {
                $(doc).unbind(duringDragEvents);
                $(doc.body).removeClass("sp-dragging");
                setTimeout(function () {
                    onstop.apply(element, arguments)
                }, 0)
            }
            dragging = false
        }

        $(element).bind("touchstart mousedown", start)
    }

    function throttle(func,
                      wait, debounce) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            var throttler = function () {
                timeout = null;
                func.apply(context, args)
            };
            if (debounce) clearTimeout(timeout);
            if (debounce || !timeout) timeout = setTimeout(throttler, wait)
        }
    }

    function inputTypeColorSupport() {
        return $.fn.spectrum.inputTypeColorSupport()
    }

    var dataID = "spectrum.id";
    $.fn.spectrum = function (opts, extra) {
        if (typeof opts == "string") {
            var returnValue = this;
            var args = Array.prototype.slice.call(arguments, 1);
            this.each(function () {
                var spect = spectrums[$(this).data(dataID)];
                if (spect) {
                    var method = spect[opts];
                    if (!method) throw new Error("Spectrum: no such method: '" + opts + "'");
                    if (opts == "get") returnValue = spect.get(); else if (opts == "container") returnValue = spect.container; else if (opts == "option") returnValue = spect.option.apply(spect, args); else if (opts == "destroy") {
                        spect.destroy();
                        $(this).removeData(dataID)
                    } else method.apply(spect, args)
                }
            });
            return returnValue
        }
        return this.spectrum("destroy").each(function () {
            var options = $.extend({}, opts, $(this).data());
            var spect = spectrum(this, options);
            $(this).data(dataID, spect.id)
        })
    };
    $.fn.spectrum.load = true;
    $.fn.spectrum.loadOpts = {};
    $.fn.spectrum.draggable = draggable;
    $.fn.spectrum.defaults = defaultOpts;
    $.fn.spectrum.inputTypeColorSupport = function inputTypeColorSupport() {
        if (typeof inputTypeColorSupport._cachedResult === "undefined") {
            var colorInput = $("<input type='color'/>")[0];
            inputTypeColorSupport._cachedResult = colorInput.type === "color" && colorInput.value !== ""
        }
        return inputTypeColorSupport._cachedResult
    };
    $.spectrum = {};
    $.spectrum.localization = {};
    $.spectrum.palettes =
        {};
    $.fn.spectrum.processNativeColorInputs = function () {
        var colorInputs = $("input[type=color]");
        if (colorInputs.length && !inputTypeColorSupport()) colorInputs.spectrum({preferredFormat: "hex6"})
    };
    (function () {
        var trimLeft = /^[\s,#]+/, trimRight = /\s+$/, tinyCounter = 0, math = Math, mathRound = math.round,
            mathMin = math.min, mathMax = math.max, mathRandom = math.random;
        var tinycolor = function (color, opts) {
            color = color ? color : "";
            opts = opts || {};
            if (color instanceof tinycolor) return color;
            if (!(this instanceof tinycolor)) return new tinycolor(color,
                opts);
            var rgb = inputToRGB(color);
            this._originalInput = color, this._r = rgb.r, this._g = rgb.g, this._b = rgb.b, this._a = rgb.a, this._roundA = mathRound(100 * this._a) / 100, this._format = opts.format || rgb.format;
            this._gradientType = opts.gradientType;
            if (this._r < 1) this._r = mathRound(this._r);
            if (this._g < 1) this._g = mathRound(this._g);
            if (this._b < 1) this._b = mathRound(this._b);
            this._ok = rgb.ok;
            this._tc_id = tinyCounter++
        };
        tinycolor.prototype = {
            isDark: function () {
                return this.getBrightness() < 128
            }, isLight: function () {
                return !this.isDark()
            },
            isValid: function () {
                return this._ok
            }, getOriginalInput: function () {
                return this._originalInput
            }, getFormat: function () {
                return this._format
            }, getAlpha: function () {
                return this._a
            }, getBrightness: function () {
                var rgb = this.toRgb();
                return (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1E3
            }, setAlpha: function (value) {
                this._a = boundAlpha(value);
                this._roundA = mathRound(100 * this._a) / 100;
                return this
            }, toHsv: function () {
                var hsv = rgbToHsv(this._r, this._g, this._b);
                return {h: hsv.h * 360, s: hsv.s, v: hsv.v, a: this._a}
            }, toHsvString: function () {
                var hsv = rgbToHsv(this._r,
                    this._g, this._b);
                var h = mathRound(hsv.h * 360), s = mathRound(hsv.s * 100), v = mathRound(hsv.v * 100);
                return this._a == 1 ? "hsv(" + h + ", " + s + "%, " + v + "%)" : "hsva(" + h + ", " + s + "%, " + v + "%, " + this._roundA + ")"
            }, toHsl: function () {
                var hsl = rgbToHsl(this._r, this._g, this._b);
                return {h: hsl.h * 360, s: hsl.s, l: hsl.l, a: this._a}
            }, toHslString: function () {
                var hsl = rgbToHsl(this._r, this._g, this._b);
                var h = mathRound(hsl.h * 360), s = mathRound(hsl.s * 100), l = mathRound(hsl.l * 100);
                return this._a == 1 ? "hsl(" + h + ", " + s + "%, " + l + "%)" : "hsla(" + h + ", " + s + "%, " + l +
                    "%, " + this._roundA + ")"
            }, toHex: function (allow3Char) {
                return rgbToHex(this._r, this._g, this._b, allow3Char)
            }, toHexString: function (allow3Char) {
                return "#" + this.toHex(allow3Char)
            }, toHex8: function () {
                return rgbaToHex(this._r, this._g, this._b, this._a)
            }, toHex8String: function () {
                return "#" + this.toHex8()
            }, toRgb: function () {
                return {r: mathRound(this._r), g: mathRound(this._g), b: mathRound(this._b), a: this._a}
            }, toRgbString: function () {
                return this._a == 1 ? "rgb(" + mathRound(this._r) + ", " + mathRound(this._g) + ", " + mathRound(this._b) +
                    ")" : "rgba(" + mathRound(this._r) + ", " + mathRound(this._g) + ", " + mathRound(this._b) + ", " + this._roundA + ")"
            }, toPercentageRgb: function () {
                return {
                    r: mathRound(bound01(this._r, 255) * 100) + "%",
                    g: mathRound(bound01(this._g, 255) * 100) + "%",
                    b: mathRound(bound01(this._b, 255) * 100) + "%",
                    a: this._a
                }
            }, toPercentageRgbString: function () {
                return this._a == 1 ? "rgb(" + mathRound(bound01(this._r, 255) * 100) + "%, " + mathRound(bound01(this._g, 255) * 100) + "%, " + mathRound(bound01(this._b, 255) * 100) + "%)" : "rgba(" + mathRound(bound01(this._r, 255) * 100) +
                    "%, " + mathRound(bound01(this._g, 255) * 100) + "%, " + mathRound(bound01(this._b, 255) * 100) + "%, " + this._roundA + ")"
            }, toName: function () {
                if (this._a === 0) return "transparent";
                if (this._a < 1) return false;
                return hexNames[rgbToHex(this._r, this._g, this._b, true)] || false
            }, toFilter: function (secondColor) {
                var hex8String = "#" + rgbaToHex(this._r, this._g, this._b, this._a);
                var secondHex8String = hex8String;
                var gradientType = this._gradientType ? "GradientType = 1, " : "";
                if (secondColor) {
                    var s = tinycolor(secondColor);
                    secondHex8String = s.toHex8String()
                }
                return "progid:DXImageTransform.Microsoft.gradient(" +
                    gradientType + "startColorstr=" + hex8String + ",endColorstr=" + secondHex8String + ")"
            }, toString: function (format) {
                var formatSet = !!format;
                format = format || this._format;
                var formattedString = false;
                var hasAlpha = this._a < 1 && this._a >= 0;
                var needsAlphaFormat = !formatSet && hasAlpha && (format === "hex" || format === "hex6" || format === "hex3" || format === "name");
                if (needsAlphaFormat) {
                    if (format === "name" && this._a === 0) return this.toName();
                    return this.toRgbString()
                }
                if (format === "rgb") formattedString = this.toRgbString();
                if (format === "prgb") formattedString =
                    this.toPercentageRgbString();
                if (format === "hex" || format === "hex6") formattedString = this.toHexString();
                if (format === "hex3") formattedString = this.toHexString(true);
                if (format === "hex8") formattedString = this.toHex8String();
                if (format === "name") formattedString = this.toName();
                if (format === "hsl") formattedString = this.toHslString();
                if (format === "hsv") formattedString = this.toHsvString();
                return formattedString || this.toHexString()
            }, _applyModification: function (fn, args) {
                var color = fn.apply(null, [this].concat([].slice.call(args)));
                this._r = color._r;
                this._g = color._g;
                this._b = color._b;
                this.setAlpha(color._a);
                return this
            }, lighten: function () {
                return this._applyModification(lighten, arguments)
            }, brighten: function () {
                return this._applyModification(brighten, arguments)
            }, darken: function () {
                return this._applyModification(darken, arguments)
            }, desaturate: function () {
                return this._applyModification(desaturate, arguments)
            }, saturate: function () {
                return this._applyModification(saturate, arguments)
            }, greyscale: function () {
                return this._applyModification(greyscale,
                    arguments)
            }, spin: function () {
                return this._applyModification(spin, arguments)
            }, _applyCombination: function (fn, args) {
                return fn.apply(null, [this].concat([].slice.call(args)))
            }, analogous: function () {
                return this._applyCombination(analogous, arguments)
            }, complement: function () {
                return this._applyCombination(complement, arguments)
            }, monochromatic: function () {
                return this._applyCombination(monochromatic, arguments)
            }, splitcomplement: function () {
                return this._applyCombination(splitcomplement, arguments)
            }, triad: function () {
                return this._applyCombination(triad,
                    arguments)
            }, tetrad: function () {
                return this._applyCombination(tetrad, arguments)
            }
        };
        tinycolor.fromRatio = function (color, opts) {
            if (typeof color == "object") {
                var newColor = {};
                for (var i in color) if (color.hasOwnProperty(i)) if (i === "a") newColor[i] = color[i]; else newColor[i] = convertToPercentage(color[i]);
                color = newColor
            }
            return tinycolor(color, opts)
        };

        function inputToRGB(color) {
            var rgb = {r: 0, g: 0, b: 0};
            var a = 1;
            var ok = false;
            var format = false;
            if (typeof color == "string") color = stringInputToObject(color);
            if (typeof color == "object") {
                if (color.hasOwnProperty("r") &&
                    color.hasOwnProperty("g") && color.hasOwnProperty("b")) {
                    rgb = rgbToRgb(color.r, color.g, color.b);
                    ok = true;
                    format = String(color.r).substr(-1) === "%" ? "prgb" : "rgb"
                } else if (color.hasOwnProperty("h") && color.hasOwnProperty("s") && color.hasOwnProperty("v")) {
                    color.s = convertToPercentage(color.s);
                    color.v = convertToPercentage(color.v);
                    rgb = hsvToRgb(color.h, color.s, color.v);
                    ok = true;
                    format = "hsv"
                } else if (color.hasOwnProperty("h") && color.hasOwnProperty("s") && color.hasOwnProperty("l")) {
                    color.s = convertToPercentage(color.s);
                    color.l = convertToPercentage(color.l);
                    rgb = hslToRgb(color.h, color.s, color.l);
                    ok = true;
                    format = "hsl"
                }
                if (color.hasOwnProperty("a")) a = color.a
            }
            a = boundAlpha(a);
            return {
                ok: ok,
                format: color.format || format,
                r: mathMin(255, mathMax(rgb.r, 0)),
                g: mathMin(255, mathMax(rgb.g, 0)),
                b: mathMin(255, mathMax(rgb.b, 0)),
                a: a
            }
        }

        function rgbToRgb(r, g, b) {
            return {r: bound01(r, 255) * 255, g: bound01(g, 255) * 255, b: bound01(b, 255) * 255}
        }

        function rgbToHsl(r, g, b) {
            r = bound01(r, 255);
            g = bound01(g, 255);
            b = bound01(b, 255);
            var max = mathMax(r, g, b), min = mathMin(r,
                g, b);
            var h, s, l = (max + min) / 2;
            if (max == min) h = s = 0; else {
                var d = max - min;
                s = l > .5 ? d / (2 - max - min) : d / (max + min);
                switch (max) {
                    case r:
                        h = (g - b) / d + (g < b ? 6 : 0);
                        break;
                    case g:
                        h = (b - r) / d + 2;
                        break;
                    case b:
                        h = (r - g) / d + 4;
                        break
                }
                h /= 6
            }
            return {h: h, s: s, l: l}
        }

        function hslToRgb(h, s, l) {
            var r, g, b;
            h = bound01(h, 360);
            s = bound01(s, 100);
            l = bound01(l, 100);

            function hue2rgb(p, q, t) {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1 / 6) return p + (q - p) * 6 * t;
                if (t < 1 / 2) return q;
                if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
                return p
            }

            if (s === 0) r = g = b = l; else {
                var q = l < .5 ? l * (1 + s) : l + s - l * s;
                var p = 2 * l - q;
                r = hue2rgb(p, q, h + 1 / 3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1 / 3)
            }
            return {r: r * 255, g: g * 255, b: b * 255}
        }

        function rgbToHsv(r, g, b) {
            r = bound01(r, 255);
            g = bound01(g, 255);
            b = bound01(b, 255);
            var max = mathMax(r, g, b), min = mathMin(r, g, b);
            var h, s, v = max;
            var d = max - min;
            s = max === 0 ? 0 : d / max;
            if (max == min) h = 0; else {
                switch (max) {
                    case r:
                        h = (g - b) / d + (g < b ? 6 : 0);
                        break;
                    case g:
                        h = (b - r) / d + 2;
                        break;
                    case b:
                        h = (r - g) / d + 4;
                        break
                }
                h /= 6
            }
            return {h: h, s: s, v: v}
        }

        function hsvToRgb(h, s, v) {
            h = bound01(h, 360) * 6;
            s = bound01(s, 100);
            v = bound01(v, 100);
            var i = math.floor(h), f = h - i, p =
                v * (1 - s), q = v * (1 - f * s), t = v * (1 - (1 - f) * s), mod = i % 6, r = [v, q, p, p, t, v][mod],
                g = [t, v, v, q, p, p][mod], b = [p, p, t, v, v, q][mod];
            return {r: r * 255, g: g * 255, b: b * 255}
        }

        function rgbToHex(r, g, b, allow3Char) {
            var hex = [pad2(mathRound(r).toString(16)), pad2(mathRound(g).toString(16)), pad2(mathRound(b).toString(16))];
            if (allow3Char && hex[0].charAt(0) == hex[0].charAt(1) && hex[1].charAt(0) == hex[1].charAt(1) && hex[2].charAt(0) == hex[2].charAt(1)) return hex[0].charAt(0) + hex[1].charAt(0) + hex[2].charAt(0);
            return hex.join("")
        }

        function rgbaToHex(r,
                           g, b, a) {
            var hex = [pad2(convertDecimalToHex(a)), pad2(mathRound(r).toString(16)), pad2(mathRound(g).toString(16)), pad2(mathRound(b).toString(16))];
            return hex.join("")
        }

        tinycolor.equals = function (color1, color2) {
            if (!color1 || !color2) return false;
            return tinycolor(color1).toRgbString() == tinycolor(color2).toRgbString()
        };
        tinycolor.random = function () {
            return tinycolor.fromRatio({r: mathRandom(), g: mathRandom(), b: mathRandom()})
        };

        function desaturate(color, amount) {
            amount = amount === 0 ? 0 : amount || 10;
            var hsl = tinycolor(color).toHsl();
            hsl.s -= amount / 100;
            hsl.s = clamp01(hsl.s);
            return tinycolor(hsl)
        }

        function saturate(color, amount) {
            amount = amount === 0 ? 0 : amount || 10;
            var hsl = tinycolor(color).toHsl();
            hsl.s += amount / 100;
            hsl.s = clamp01(hsl.s);
            return tinycolor(hsl)
        }

        function greyscale(color) {
            return tinycolor(color).desaturate(100)
        }

        function lighten(color, amount) {
            amount = amount === 0 ? 0 : amount || 10;
            var hsl = tinycolor(color).toHsl();
            hsl.l += amount / 100;
            hsl.l = clamp01(hsl.l);
            return tinycolor(hsl)
        }

        function brighten(color, amount) {
            amount = amount === 0 ? 0 : amount || 10;
            var rgb = tinycolor(color).toRgb();
            rgb.r = mathMax(0, mathMin(255, rgb.r - mathRound(255 * -(amount / 100))));
            rgb.g = mathMax(0, mathMin(255, rgb.g - mathRound(255 * -(amount / 100))));
            rgb.b = mathMax(0, mathMin(255, rgb.b - mathRound(255 * -(amount / 100))));
            return tinycolor(rgb)
        }

        function darken(color, amount) {
            amount = amount === 0 ? 0 : amount || 10;
            var hsl = tinycolor(color).toHsl();
            hsl.l -= amount / 100;
            hsl.l = clamp01(hsl.l);
            return tinycolor(hsl)
        }

        function spin(color, amount) {
            var hsl = tinycolor(color).toHsl();
            var hue = (mathRound(hsl.h) + amount) % 360;
            hsl.h = hue < 0 ? 360 + hue : hue;
            return tinycolor(hsl)
        }

        function complement(color) {
            var hsl = tinycolor(color).toHsl();
            hsl.h = (hsl.h + 180) % 360;
            return tinycolor(hsl)
        }

        function triad(color) {
            var hsl = tinycolor(color).toHsl();
            var h = hsl.h;
            return [tinycolor(color), tinycolor({
                h: (h + 120) % 360,
                s: hsl.s,
                l: hsl.l
            }), tinycolor({h: (h + 240) % 360, s: hsl.s, l: hsl.l})]
        }

        function tetrad(color) {
            var hsl = tinycolor(color).toHsl();
            var h = hsl.h;
            return [tinycolor(color), tinycolor({h: (h + 90) % 360, s: hsl.s, l: hsl.l}), tinycolor({
                h: (h + 180) % 360,
                s: hsl.s,
                l: hsl.l
            }),
                tinycolor({h: (h + 270) % 360, s: hsl.s, l: hsl.l})]
        }

        function splitcomplement(color) {
            var hsl = tinycolor(color).toHsl();
            var h = hsl.h;
            return [tinycolor(color), tinycolor({h: (h + 72) % 360, s: hsl.s, l: hsl.l}), tinycolor({
                h: (h + 216) % 360,
                s: hsl.s,
                l: hsl.l
            })]
        }

        function analogous(color, results, slices) {
            results = results || 6;
            slices = slices || 30;
            var hsl = tinycolor(color).toHsl();
            var part = 360 / slices;
            var ret = [tinycolor(color)];
            for (hsl.h = (hsl.h - (part * results >> 1) + 720) % 360; --results;) {
                hsl.h = (hsl.h + part) % 360;
                ret.push(tinycolor(hsl))
            }
            return ret
        }

        function monochromatic(color, results) {
            results = results || 6;
            var hsv = tinycolor(color).toHsv();
            var h = hsv.h, s = hsv.s, v = hsv.v;
            var ret = [];
            var modification = 1 / results;
            while (results--) {
                ret.push(tinycolor({h: h, s: s, v: v}));
                v = (v + modification) % 1
            }
            return ret
        }

        tinycolor.mix = function (color1, color2, amount) {
            amount = amount === 0 ? 0 : amount || 50;
            var rgb1 = tinycolor(color1).toRgb();
            var rgb2 = tinycolor(color2).toRgb();
            var p = amount / 100;
            var w = p * 2 - 1;
            var a = rgb2.a - rgb1.a;
            var w1;
            if (w * a == -1) w1 = w; else w1 = (w + a) / (1 + w * a);
            w1 = (w1 + 1) / 2;
            var w2 = 1 -
                w1;
            var rgba = {
                r: rgb2.r * w1 + rgb1.r * w2,
                g: rgb2.g * w1 + rgb1.g * w2,
                b: rgb2.b * w1 + rgb1.b * w2,
                a: rgb2.a * p + rgb1.a * (1 - p)
            };
            return tinycolor(rgba)
        };
        tinycolor.readability = function (color1, color2) {
            var c1 = tinycolor(color1);
            var c2 = tinycolor(color2);
            var rgb1 = c1.toRgb();
            var rgb2 = c2.toRgb();
            var brightnessA = c1.getBrightness();
            var brightnessB = c2.getBrightness();
            var colorDiff = Math.max(rgb1.r, rgb2.r) - Math.min(rgb1.r, rgb2.r) + Math.max(rgb1.g, rgb2.g) - Math.min(rgb1.g, rgb2.g) + Math.max(rgb1.b, rgb2.b) - Math.min(rgb1.b, rgb2.b);
            return {
                brightness: Math.abs(brightnessA -
                    brightnessB), color: colorDiff
            }
        };
        tinycolor.isReadable = function (color1, color2) {
            var readability = tinycolor.readability(color1, color2);
            return readability.brightness > 125 && readability.color > 500
        };
        tinycolor.mostReadable = function (baseColor, colorList) {
            var bestColor = null;
            var bestScore = 0;
            var bestIsReadable = false;
            for (var i = 0; i < colorList.length; i++) {
                var readability = tinycolor.readability(baseColor, colorList[i]);
                var readable = readability.brightness > 125 && readability.color > 500;
                var score = 3 * (readability.brightness / 125) +
                    readability.color / 500;
                if (readable && !bestIsReadable || readable && bestIsReadable && score > bestScore || !readable && !bestIsReadable && score > bestScore) {
                    bestIsReadable = readable;
                    bestScore = score;
                    bestColor = tinycolor(colorList[i])
                }
            }
            return bestColor
        };
        var names = tinycolor.names = {
            aliceblue: "f0f8ff",
            antiquewhite: "faebd7",
            aqua: "0ff",
            aquamarine: "7fffd4",
            azure: "f0ffff",
            beige: "f5f5dc",
            bisque: "ffe4c4",
            black: "000",
            blanchedalmond: "ffebcd",
            blue: "00f",
            blueviolet: "8a2be2",
            brown: "a52a2a",
            burlywood: "deb887",
            burntsienna: "ea7e5d",
            cadetblue: "5f9ea0",
            chartreuse: "7fff00",
            chocolate: "d2691e",
            coral: "ff7f50",
            cornflowerblue: "6495ed",
            cornsilk: "fff8dc",
            crimson: "dc143c",
            cyan: "0ff",
            darkblue: "00008b",
            darkcyan: "008b8b",
            darkgoldenrod: "b8860b",
            darkgray: "a9a9a9",
            darkgreen: "006400",
            darkgrey: "a9a9a9",
            darkkhaki: "bdb76b",
            darkmagenta: "8b008b",
            darkolivegreen: "556b2f",
            darkorange: "ff8c00",
            darkorchid: "9932cc",
            darkred: "8b0000",
            darksalmon: "e9967a",
            darkseagreen: "8fbc8f",
            darkslateblue: "483d8b",
            darkslategray: "2f4f4f",
            darkslategrey: "2f4f4f",
            darkturquoise: "00ced1",
            darkviolet: "9400d3",
            deeppink: "ff1493",
            deepskyblue: "00bfff",
            dimgray: "696969",
            dimgrey: "696969",
            dodgerblue: "1e90ff",
            firebrick: "b22222",
            floralwhite: "fffaf0",
            forestgreen: "228b22",
            fuchsia: "f0f",
            gainsboro: "dcdcdc",
            ghostwhite: "f8f8ff",
            gold: "ffd700",
            goldenrod: "daa520",
            gray: "808080",
            green: "008000",
            greenyellow: "adff2f",
            grey: "808080",
            honeydew: "f0fff0",
            hotpink: "ff69b4",
            indianred: "cd5c5c",
            indigo: "4b0082",
            ivory: "fffff0",
            khaki: "f0e68c",
            lavender: "e6e6fa",
            lavenderblush: "fff0f5",
            lawngreen: "7cfc00",
            lemonchiffon: "fffacd",
            lightblue: "add8e6",
            lightcoral: "f08080",
            lightcyan: "e0ffff",
            lightgoldenrodyellow: "fafad2",
            lightgray: "d3d3d3",
            lightgreen: "90ee90",
            lightgrey: "d3d3d3",
            lightpink: "ffb6c1",
            lightsalmon: "ffa07a",
            lightseagreen: "20b2aa",
            lightskyblue: "87cefa",
            lightslategray: "789",
            lightslategrey: "789",
            lightsteelblue: "b0c4de",
            lightyellow: "ffffe0",
            lime: "0f0",
            limegreen: "32cd32",
            linen: "faf0e6",
            magenta: "f0f",
            maroon: "800000",
            mediumaquamarine: "66cdaa",
            mediumblue: "0000cd",
            mediumorchid: "ba55d3",
            mediumpurple: "9370db",
            mediumseagreen: "3cb371",
            mediumslateblue: "7b68ee",
            mediumspringgreen: "00fa9a",
            mediumturquoise: "48d1cc",
            mediumvioletred: "c71585",
            midnightblue: "191970",
            mintcream: "f5fffa",
            mistyrose: "ffe4e1",
            moccasin: "ffe4b5",
            navajowhite: "ffdead",
            navy: "000080",
            oldlace: "fdf5e6",
            olive: "808000",
            olivedrab: "6b8e23",
            orange: "ffa500",
            orangered: "ff4500",
            orchid: "da70d6",
            palegoldenrod: "eee8aa",
            palegreen: "98fb98",
            paleturquoise: "afeeee",
            palevioletred: "db7093",
            papayawhip: "ffefd5",
            peachpuff: "ffdab9",
            peru: "cd853f",
            pink: "ffc0cb",
            plum: "dda0dd",
            powderblue: "b0e0e6",
            purple: "800080",
            rebeccapurple: "663399",
            red: "f00",
            rosybrown: "bc8f8f",
            royalblue: "4169e1",
            saddlebrown: "8b4513",
            salmon: "fa8072",
            sandybrown: "f4a460",
            seagreen: "2e8b57",
            seashell: "fff5ee",
            sienna: "a0522d",
            silver: "c0c0c0",
            skyblue: "87ceeb",
            slateblue: "6a5acd",
            slategray: "708090",
            slategrey: "708090",
            snow: "fffafa",
            springgreen: "00ff7f",
            steelblue: "4682b4",
            tan: "d2b48c",
            teal: "008080",
            thistle: "d8bfd8",
            tomato: "ff6347",
            turquoise: "40e0d0",
            violet: "ee82ee",
            wheat: "f5deb3",
            white: "fff",
            whitesmoke: "f5f5f5",
            yellow: "ff0",
            yellowgreen: "9acd32"
        };
        var hexNames = tinycolor.hexNames =
            flip(names);

        function flip(o) {
            var flipped = {};
            for (var i in o) if (o.hasOwnProperty(i)) flipped[o[i]] = i;
            return flipped
        }

        function boundAlpha(a) {
            a = parseFloat(a);
            if (isNaN(a) || a < 0 || a > 1) a = 1;
            return a
        }

        function bound01(n, max) {
            if (isOnePointZero(n)) n = "100%";
            var processPercent = isPercentage(n);
            n = mathMin(max, mathMax(0, parseFloat(n)));
            if (processPercent) n = parseInt(n * max, 10) / 100;
            if (math.abs(n - max) < 1E-6) return 1;
            return n % max / parseFloat(max)
        }

        function clamp01(val) {
            return mathMin(1, mathMax(0, val))
        }

        function parseIntFromHex(val) {
            return parseInt(val,
                16)
        }

        function isOnePointZero(n) {
            return typeof n == "string" && n.indexOf(".") != -1 && parseFloat(n) === 1
        }

        function isPercentage(n) {
            return typeof n === "string" && n.indexOf("%") != -1
        }

        function pad2(c) {
            return c.length == 1 ? "0" + c : "" + c
        }

        function convertToPercentage(n) {
            if (n <= 1) n = n * 100 + "%";
            return n
        }

        function convertDecimalToHex(d) {
            return Math.round(parseFloat(d) * 255).toString(16)
        }

        function convertHexToDecimal(h) {
            return parseIntFromHex(h) / 255
        }

        var matchers = function () {
            var CSS_INTEGER = "[-\\+]?\\d+%?";
            var CSS_NUMBER = "[-\\+]?\\d*\\.\\d+%?";
            var CSS_UNIT = "(?:" + CSS_NUMBER + ")|(?:" + CSS_INTEGER + ")";
            var PERMISSIVE_MATCH3 = "[\\s|\\(]+(" + CSS_UNIT + ")[,|\\s]+(" + CSS_UNIT + ")[,|\\s]+(" + CSS_UNIT + ")\\s*\\)?";
            var PERMISSIVE_MATCH4 = "[\\s|\\(]+(" + CSS_UNIT + ")[,|\\s]+(" + CSS_UNIT + ")[,|\\s]+(" + CSS_UNIT + ")[,|\\s]+(" + CSS_UNIT + ")\\s*\\)?";
            return {
                rgb: new RegExp("rgb" + PERMISSIVE_MATCH3),
                rgba: new RegExp("rgba" + PERMISSIVE_MATCH4),
                hsl: new RegExp("hsl" + PERMISSIVE_MATCH3),
                hsla: new RegExp("hsla" + PERMISSIVE_MATCH4),
                hsv: new RegExp("hsv" + PERMISSIVE_MATCH3),
                hsva: new RegExp("hsva" +
                    PERMISSIVE_MATCH4),
                hex3: /^([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/,
                hex6: /^([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/,
                hex8: /^([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/
            }
        }();

        function stringInputToObject(color) {
            color = color.replace(trimLeft, "").replace(trimRight, "").toLowerCase();
            var named = false;
            if (names[color]) {
                color = names[color];
                named = true
            } else if (color == "transparent") return {r: 0, g: 0, b: 0, a: 0, format: "name"};
            var match;
            if (match = matchers.rgb.exec(color)) return {
                r: match[1],
                g: match[2], b: match[3]
            };
            if (match = matchers.rgba.exec(color)) return {r: match[1], g: match[2], b: match[3], a: match[4]};
            if (match = matchers.hsl.exec(color)) return {h: match[1], s: match[2], l: match[3]};
            if (match = matchers.hsla.exec(color)) return {h: match[1], s: match[2], l: match[3], a: match[4]};
            if (match = matchers.hsv.exec(color)) return {h: match[1], s: match[2], v: match[3]};
            if (match = matchers.hsva.exec(color)) return {h: match[1], s: match[2], v: match[3], a: match[4]};
            if (match = matchers.hex8.exec(color)) return {
                a: convertHexToDecimal(match[1]),
                r: parseIntFromHex(match[2]),
                g: parseIntFromHex(match[3]),
                b: parseIntFromHex(match[4]),
                format: named ? "name" : "hex8"
            };
            if (match = matchers.hex6.exec(color)) return {
                r: parseIntFromHex(match[1]),
                g: parseIntFromHex(match[2]),
                b: parseIntFromHex(match[3]),
                format: named ? "name" : "hex"
            };
            if (match = matchers.hex3.exec(color)) return {
                r: parseIntFromHex(match[1] + "" + match[1]),
                g: parseIntFromHex(match[2] + "" + match[2]),
                b: parseIntFromHex(match[3] + "" + match[3]),
                format: named ? "name" : "hex"
            };
            return false
        }

        window.tinycolor = tinycolor
    })();
    $(function () {
        if ($.fn.spectrum.load) $.fn.spectrum.processNativeColorInputs()
    })
});
/*! JsBarcode v3.6.0 | (c) Johan Lindell | MIT license */
!function (t) {
    function e(r) {
        if (n[r]) return n[r].exports;
        var o = n[r] = {i: r, l: !1, exports: {}};
        return t[r].call(o.exports, o, o.exports, e), o.l = !0, o.exports
    }

    var n = {};
    return e.m = t, e.c = n, e.i = function (t) {
        return t
    }, e.d = function (t, e, n) {
        Object.defineProperty(t, e, {configurable: !1, enumerable: !0, get: n})
    }, e.n = function (t) {
        var n = t && t.__esModule ? function () {
            return t["default"]
        } : function () {
            return t
        };
        return e.d(n, "a", n), n
    }, e.o = function (t, e) {
        return Object.prototype.hasOwnProperty.call(t, e)
    }, e.p = "", e(e.s = 42)
}([function (t, e) {
    "use strict";

    function n(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var r = function o(t, e) {
        n(this, o), this.data = t, this.text = e.text || t, this.options = e
    };
    e["default"] = r
}, function (t, e) {
    "use strict";

    function n(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var r = function () {
        function t() {
            n(this, t), this.startBin = "101", this.endBin = "101", this.middleBin = "01010", this.Lbinary = ["0001101", "0011001", "0010011", "0111101", "0100011", "0110001", "0101111", "0111011", "0110111", "0001011"], this.Gbinary = ["0100111", "0110011", "0011011", "0100001", "0011101", "0111001", "0000101", "0010001", "0001001", "0010111"], this.Rbinary = ["1110010", "1100110", "1101100", "1000010", "1011100", "1001110", "1010000", "1000100", "1001000", "1110100"]
        }

        return t.prototype.encode = function (t, e, n) {
            var r = "";
            n = n || "";
            for (var o = 0; o < t.length; o++) "L" == e[o] ? r += this.Lbinary[t[o]] : "G" == e[o] ? r += this.Gbinary[t[o]] : "R" == e[o] && (r += this.Rbinary[t[o]]), o < t.length - 1 && (r += n);
            return r
        }, t
    }();
    e["default"] = r
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    function u(t, e) {
        for (var n = 0; n < e; n++) t = "0" + t;
        return t
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var s = n(0), c = r(s), f = function (t) {
        function e(n, r) {
            return o(this, e), i(this, t.call(this, n, r))
        }

        return a(e, t), e.prototype.encode = function () {
            for (var t = "110", e = 0; e < this.data.length; e++) {
                var n = parseInt(this.data[e]), r = n.toString(2);
                r = u(r, 4 - r.length);
                for (var o = 0; o < r.length; o++) t += "0" == r[o] ? "100" : "110"
            }
            return t += "1001", {data: t, text: this.text}
        }, e.prototype.valid = function () {
            return this.data.search(/^[0-9]+$/) !== -1
        }, e
    }(c["default"]);
    e["default"] = f
}, function (t, e) {
    "use strict";

    function n(t, e) {
        var n, r = {};
        for (n in t) t.hasOwnProperty(n) && (r[n] = t[n]);
        for (n in e) e.hasOwnProperty(n) && "undefined" != typeof e[n] && (r[n] = e[n]);
        return r
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e["default"] = n
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = n(0), s = r(u), c = function (t) {
        function e(n, r) {
            o(this, e);
            var a = i(this, t.call(this, n.substring(1), r));
            a.bytes = [];
            for (var u = 0; u < n.length; ++u) a.bytes.push(n.charCodeAt(u));
            return a.encodings = [740, 644, 638, 176, 164, 100, 224, 220, 124, 608, 604, 572, 436, 244, 230, 484, 260, 254, 650, 628, 614, 764, 652, 902, 868, 836, 830, 892, 844, 842, 752, 734, 590, 304, 112, 94, 416, 128, 122, 672, 576, 570, 464, 422, 134, 496, 478, 142, 910, 678, 582, 768, 762, 774, 880, 862, 814, 896, 890, 818, 914, 602, 930, 328, 292, 200, 158, 68, 62, 424, 412, 232, 218, 76, 74, 554, 616, 978, 556, 146, 340, 212, 182, 508, 268, 266, 956, 940, 938, 758, 782, 974, 400, 310, 118, 512, 506, 960, 954, 502, 518, 886, 966, 668, 680, 692, 5379], a
        }

        return a(e, t), e.prototype.encode = function () {
            var t, e = this.bytes, n = e.shift() - 105;
            if (103 === n) t = this.nextA(e, 1); else if (104 === n) t = this.nextB(e, 1); else {
                if (105 !== n) throw new f;
                t = this.nextC(e, 1)
            }
            return {
                text: this.text == this.data ? this.text.replace(/[^\x20-\x7E]/g, "") : this.text,
                data: this.getEncoding(n) + t.result + this.getEncoding((t.checksum + n) % 103) + this.getEncoding(106)
            }
        }, e.prototype.getEncoding = function (t) {
            return this.encodings[t] ? (this.encodings[t] + 1e3).toString(2) : ""
        }, e.prototype.valid = function () {
            return this.data.search(/^[\x00-\x7F\xC8-\xD3]+$/) !== -1
        }, e.prototype.nextA = function (t, e) {
            if (t.length <= 0) return {result: "", checksum: 0};
            var n, r;
            if (t[0] >= 200) r = t[0] - 105, t.shift(), 99 === r ? n = this.nextC(t, e + 1) : 100 === r ? n = this.nextB(t, e + 1) : 98 === r ? (t[0] = t[0] > 95 ? t[0] - 96 : t[0], n = this.nextA(t, e + 1)) : n = this.nextA(t, e + 1); else {
                var o = t[0];
                r = o < 32 ? o + 64 : o - 32, t.shift(), n = this.nextA(t, e + 1)
            }
            var i = this.getEncoding(r), a = r * e;
            return {result: i + n.result, checksum: a + n.checksum}
        }, e.prototype.nextB = function (t, e) {
            if (t.length <= 0) return {result: "", checksum: 0};
            var n, r;
            t[0] >= 200 ? (r = t[0] - 105, t.shift(), 99 === r ? n = this.nextC(t, e + 1) : 101 === r ? n = this.nextA(t, e + 1) : 98 === r ? (t[0] = t[0] < 32 ? t[0] + 96 : t[0], n = this.nextB(t, e + 1)) : n = this.nextB(t, e + 1)) : (r = t[0] - 32, t.shift(), n = this.nextB(t, e + 1));
            var o = this.getEncoding(r), i = r * e;
            return {result: o + n.result, checksum: i + n.checksum}
        }, e.prototype.nextC = function (t, e) {
            if (t.length <= 0) return {result: "", checksum: 0};
            var n, r;
            t[0] >= 200 ? (r = t[0] - 105, t.shift(), n = 100 === r ? this.nextB(t, e + 1) : 101 === r ? this.nextA(t, e + 1) : this.nextC(t, e + 1)) : (r = 10 * (t[0] - 48) + t[1] - 48, t.shift(), t.shift(), n = this.nextC(t, e + 1));
            var o = this.getEncoding(r), i = r * e;
            return {result: o + n.result, checksum: i + n.checksum}
        }, e
    }(s["default"]), f = function (t) {
        function e() {
            o(this, e);
            var n = i(this, t.call(this));
            return n.name = "InvalidStartCharacterException", n.message = "The encoding does not start with a start character.", n
        }

        return a(e, t), e
    }(Error);
    e["default"] = c
}, function (t, e) {
    "use strict";

    function n(t) {
        for (var e = 0, n = 0; n < t.length; n++) {
            var r = parseInt(t[n]);
            e += (n + t.length) % 2 === 0 ? r : 2 * r % 10 + Math.floor(2 * r / 10)
        }
        return (10 - e % 10) % 10
    }

    function r(t) {
        for (var e = 0, n = [2, 3, 4, 5, 6, 7], r = 0; r < t.length; r++) {
            var o = parseInt(t[t.length - 1 - r]);
            e += n[r % n.length] * o
        }
        return (11 - e % 11) % 11
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.mod10 = n, e.mod11 = r
}, function (t, e) {
    "use strict";

    function n(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function r(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function o(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var i = function (t) {
        function e(o, i) {
            n(this, e);
            var a = r(this, t.call(this));
            return a.name = "InvalidInputException", a.symbology = o, a.input = i, a.message = '"' + a.input + '" is not a valid input for ' + a.symbology, a
        }

        return o(e, t), e
    }(Error), a = function (t) {
        function e() {
            n(this, e);
            var o = r(this, t.call(this));
            return o.name = "InvalidElementException", o.message = "Not supported type to render on", o
        }

        return o(e, t), e
    }(Error), u = function (t) {
        function e() {
            n(this, e);
            var o = r(this, t.call(this));
            return o.name = "NoElementException", o.message = "No element to render on.", o
        }

        return o(e, t), e
    }(Error);
    e.InvalidInputException = i, e.InvalidElementException = a, e.NoElementException = u
}, function (t, e) {
    "use strict";

    function n(t) {
        var e = ["width", "height", "textMargin", "fontSize", "margin", "marginTop", "marginBottom", "marginLeft", "marginRight"];
        for (var n in e) e.hasOwnProperty(n) && (n = e[n], "string" == typeof t[n] && (t[n] = parseInt(t[n], 10)));
        return "string" == typeof t.displayValue && (t.displayValue = "false" != t.displayValue), t
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e["default"] = n
}, function (t, e) {
    "use strict";
    Object.defineProperty(e, "__esModule", {value: !0});
    var n = {
        width: 2,
        height: 100,
        format: "auto",
        displayValue: !0,
        fontOptions: "",
        font: "monospace",
        text: void 0,
        textAlign: "center",
        textPosition: "bottom",
        textMargin: 2,
        fontSize: 20,
        background: "#ffffff",
        lineColor: "#000000",
        margin: 10,
        marginTop: void 0,
        marginBottom: void 0,
        marginLeft: void 0,
        marginRight: void 0,
        valid: function () {
        }
    };
    e["default"] = n
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        return e.height + (e.displayValue && t.text.length > 0 ? e.fontSize + e.textMargin : 0) + e.marginTop + e.marginBottom
    }

    function i(t, e, n) {
        if (n.displayValue && e < t) {
            if ("center" == n.textAlign) return Math.floor((t - e) / 2);
            if ("left" == n.textAlign) return 0;
            if ("right" == n.textAlign) return Math.floor(t - e)
        }
        return 0
    }

    function a(t, e, n) {
        for (var r = 0; r < t.length; r++) {
            var a, u = t[r], s = (0, l["default"])(e, u.options);
            a = s.displayValue ? c(u.text, s, n) : 0;
            var f = u.data.length * s.width;
            u.width = Math.ceil(Math.max(a, f)), u.height = o(u, s), u.barcodePadding = i(a, f, s)
        }
    }

    function u(t) {
        for (var e = 0, n = 0; n < t.length; n++) e += t[n].width;
        return e
    }

    function s(t) {
        for (var e = 0, n = 0; n < t.length; n++) t[n].height > e && (e = t[n].height);
        return e
    }

    function c(t, e, n) {
        var r;
        r = "undefined" == typeof n ? document.createElement("canvas").getContext("2d") : n, r.font = e.fontOptions + " " + e.fontSize + "px " + e.font;
        var o = r.measureText(t).width;
        return o
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.getTotalWidthOfEncodings = e.calculateEncodingAttributes = e.getBarcodePadding = e.getEncodingHeight = e.getMaximumHeightOfEncodings = void 0;
    var f = n(3), l = r(f);
    e.getMaximumHeightOfEncodings = s, e.getEncodingHeight = o, e.getBarcodePadding = i, e.calculateEncodingAttributes = a, e.getTotalWidthOfEncodings = u
}, function (t, e, n) {
    "use strict";
    Object.defineProperty(e, "__esModule", {value: !0});
    var r = n(20), o = n(19), i = n(26), a = n(29), u = n(28), s = n(34), c = n(36), f = n(35), l = n(27);
    e["default"] = {
        CODE39: r.CODE39,
        CODE128: o.CODE128,
        CODE128A: o.CODE128A,
        CODE128B: o.CODE128B,
        CODE128C: o.CODE128C,
        EAN13: i.EAN13,
        EAN8: i.EAN8,
        EAN5: i.EAN5,
        EAN2: i.EAN2,
        UPC: i.UPC,
        ITF14: a.ITF14,
        ITF: u.ITF,
        MSI: s.MSI,
        MSI10: s.MSI10,
        MSI11: s.MSI11,
        MSI1010: s.MSI1010,
        MSI1110: s.MSI1110,
        pharmacode: c.pharmacode,
        codabar: f.codabar,
        GenericBarcode: l.GenericBarcode
    }
}, function (t, e) {
    "use strict";

    function n(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var r = function () {
        function t(e) {
            n(this, t), this.api = e
        }

        return t.prototype.handleCatch = function (t) {
            if ("InvalidInputException" !== t.name) throw t;
            if (this.api._options.valid === this.api._defaults.valid) throw t.message;
            this.api._options.valid(!1), this.api.render = function () {
            }
        }, t.prototype.wrapBarcodeCall = function (t) {
            try {
                var e = t.apply(void 0, arguments);
                return this.api._options.valid(!0), e
            } catch (n) {
                return this.handleCatch(n), this.api
            }
        }, t
    }();
    e["default"] = r
}, function (t, e) {
    "use strict";

    function n(t) {
        return t.marginTop = t.marginTop || t.margin, t.marginBottom = t.marginBottom || t.margin, t.marginRight = t.marginRight || t.margin, t.marginLeft = t.marginLeft || t.margin, t
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e["default"] = n
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t) {
        if ("string" == typeof t) return i(t);
        if (Array.isArray(t)) {
            for (var e = [], n = 0; n < t.length; n++) e.push(o(t[n]));
            return e
        }
        if ("undefined" != typeof HTMLCanvasElement && t instanceof HTMLImageElement) return a(t);
        if ("undefined" != typeof SVGElement && t instanceof SVGElement) return {
            element: t,
            options: (0, c["default"])(t),
            renderer: l["default"].SVGRenderer
        };
        if ("undefined" != typeof HTMLCanvasElement && t instanceof HTMLCanvasElement) return {
            element: t,
            options: (0, c["default"])(t),
            renderer: l["default"].CanvasRenderer
        };
        if (t && t.getContext) return {element: t, renderer: l["default"].CanvasRenderer};
        if (t && "object" === ("undefined" == typeof t ? "undefined" : u(t)) && !t.nodeName) return {
            element: t,
            renderer: l["default"].ObjectRenderer
        };
        throw new p.InvalidElementException
    }

    function i(t) {
        var e = document.querySelectorAll(t);
        if (0 !== e.length) {
            for (var n = [], r = 0; r < e.length; r++) n.push(o(e[r]));
            return n
        }
    }

    function a(t) {
        var e = document.createElement("canvas");
        return {
            element: e,
            options: (0, c["default"])(t),
            renderer: l["default"].CanvasRenderer,
            afterRender: function () {
                t.setAttribute("src", e.toDataURL())
            }
        }
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (t) {
        return typeof t
    } : function (t) {
        return t && "function" == typeof Symbol && t.constructor === Symbol ? "symbol" : typeof t
    }, s = n(37), c = r(s), f = n(39), l = r(f), p = n(6);
    e["default"] = o
}, function (t, e) {
    "use strict";

    function n(t) {
        function e(t) {
            if (Array.isArray(t)) for (var r = 0; r < t.length; r++) e(t[r]); else t.text = t.text || "", t.data = t.data || "", n.push(t)
        }

        var n = [];
        return e(t), n
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e["default"] = n
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = n(4), s = r(u), c = function (t) {
        function e(n, r) {
            return o(this, e), i(this, t.call(this, String.fromCharCode(208) + n, r))
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^[\x00-\x5F\xC8-\xCF]+$/) !== -1
        }, e
    }(s["default"]);
    e["default"] = c
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = n(4), s = r(u), c = function (t) {
        function e(n, r) {
            return o(this, e), i(this, t.call(this, String.fromCharCode(209) + n, r))
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^[\x20-\x7F\xC8-\xCF]+$/) !== -1
        }, e
    }(s["default"]);
    e["default"] = c
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = n(4), s = r(u), c = function (t) {
        function e(n, r) {
            return o(this, e), i(this, t.call(this, String.fromCharCode(210) + n, r))
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^(\xCF*[0-9]{2}\xCF*)+$/) !== -1
        }, e
    }(s["default"]);
    e["default"] = c
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    function u(t) {
        var e, n = t.match(/^[\x00-\x5F\xC8-\xCF]*/)[0].length, r = t.match(/^[\x20-\x7F\xC8-\xCF]*/)[0].length,
            o = t.match(/^(\xCF*[0-9]{2}\xCF*)*/)[0].length;
        return e = o >= 2 ? String.fromCharCode(210) + f(t) : n > r ? String.fromCharCode(208) + s(t) : String.fromCharCode(209) + c(t), e = e.replace(/[\xCD\xCE]([^])[\xCD\xCE]/, function (t, e) {
            return String.fromCharCode(203) + e
        })
    }

    function s(t) {
        var e = t.match(/^([\x00-\x5F\xC8-\xCF]+?)(([0-9]{2}){2,})([^0-9]|$)/);
        if (e) return e[1] + String.fromCharCode(204) + f(t.substring(e[1].length));
        var n = t.match(/^[\x00-\x5F\xC8-\xCF]+/);
        return n[0].length === t.length ? t : n[0] + String.fromCharCode(205) + c(t.substring(n[0].length))
    }

    function c(t) {
        var e = t.match(/^([\x20-\x7F\xC8-\xCF]+?)(([0-9]{2}){2,})([^0-9]|$)/);
        if (e) return e[1] + String.fromCharCode(204) + f(t.substring(e[1].length));
        var n = t.match(/^[\x20-\x7F\xC8-\xCF]+/);
        return n[0].length === t.length ? t : n[0] + String.fromCharCode(206) + s(t.substring(n[0].length))
    }

    function f(t) {
        var e = t.match(/^(\xCF*[0-9]{2}\xCF*)+/)[0], n = e.length;
        if (n === t.length) return t;
        t = t.substring(n);
        var r = t.match(/^[\x00-\x5F\xC8-\xCF]*/)[0].length, o = t.match(/^[\x20-\x7F\xC8-\xCF]*/)[0].length;
        return r >= o ? e + String.fromCharCode(206) + s(t) : e + String.fromCharCode(205) + c(t)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var l = n(4), p = r(l), h = function (t) {
        function e(n, r) {
            if (o(this, e), n.search(/^[\x00-\x7F\xC8-\xD3]+$/) !== -1) var a = i(this, t.call(this, u(n), r)); else var a = i(this, t.call(this, n, r));
            return i(a)
        }

        return a(e, t), e
    }(p["default"]);
    e["default"] = h
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.CODE128C = e.CODE128B = e.CODE128A = e.CODE128 = void 0;
    var o = n(18), i = r(o), a = n(15), u = r(a), s = n(16), c = r(s), f = n(17), l = r(f);
    e.CODE128 = i["default"], e.CODE128A = u["default"], e.CODE128B = c["default"], e.CODE128C = l["default"]
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    function u(t) {
        return s(f(t))
    }

    function s(t) {
        return b[t].toString(2)
    }

    function c(t) {
        return y[t]
    }

    function f(t) {
        return y.indexOf(t)
    }

    function l(t) {
        for (var e = 0, n = 0; n < t.length; n++) e += f(t[n]);
        return e %= 43
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.CODE39 = void 0;
    var p = n(0), h = r(p), d = function (t) {
            function e(n, r) {
                return o(this, e), n = n.toUpperCase(), r.mod43 && (n += c(l(n))), i(this, t.call(this, n, r))
            }

            return a(e, t), e.prototype.encode = function () {
                for (var t = u("*"), e = 0; e < this.data.length; e++) t += u(this.data[e]) + "0";
                return t += u("*"), {data: t, text: this.text}
            }, e.prototype.valid = function () {
                return this.data.search(/^[0-9A-Z\-\.\ \$\/\+\%]+$/) !== -1
            }, e
        }(h["default"]),
        y = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "-", ".", " ", "$", "/", "+", "%", "*"],
        b = [20957, 29783, 23639, 30485, 20951, 29813, 23669, 20855, 29789, 23645, 29975, 23831, 30533, 22295, 30149, 24005, 21623, 29981, 23837, 22301, 30023, 23879, 30545, 22343, 30161, 24017, 21959, 30065, 23921, 22385, 29015, 18263, 29141, 17879, 29045, 18293, 17783, 29021, 18269, 17477, 17489, 17681, 20753, 35770];
    e.CODE39 = d
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    function u(t) {
        var e, n = 0;
        for (e = 0; e < 12; e += 2) n += parseInt(t[e]);
        for (e = 1; e < 12; e += 2) n += 3 * parseInt(t[e]);
        return (10 - n % 10) % 10
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var s = n(1), c = r(s), f = n(0), l = r(f), p = function (t) {
        function e(n, r) {
            o(this, e), n.search(/^[0-9]{12}$/) !== -1 && (n += u(n));
            var a = i(this, t.call(this, n, r));
            return !r.flat && r.fontSize > 10 * r.width ? a.fontSize = 10 * r.width : a.fontSize = r.fontSize, a.guardHeight = r.height + a.fontSize / 2 + r.textMargin, a.lastChar = r.lastChar, a
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^[0-9]{13}$/) !== -1 && this.data[12] == u(this.data)
        }, e.prototype.encode = function () {
            return this.options.flat ? this.flatEncoding() : this.guardedEncoding()
        }, e.prototype.getStructure = function () {
            return ["LLLLLL", "LLGLGG", "LLGGLG", "LLGGGL", "LGLLGG", "LGGLLG", "LGGGLL", "LGLGLG", "LGLGGL", "LGGLGL"]
        }, e.prototype.guardedEncoding = function () {
            var t = new c["default"], e = [], n = this.getStructure()[this.data[0]], r = this.data.substr(1, 6),
                o = this.data.substr(7, 6);
            return this.options.displayValue && e.push({
                data: "000000000000",
                text: this.text.substr(0, 1),
                options: {textAlign: "left", fontSize: this.fontSize}
            }), e.push({data: "101", options: {height: this.guardHeight}}), e.push({
                data: t.encode(r, n),
                text: this.text.substr(1, 6),
                options: {fontSize: this.fontSize}
            }), e.push({data: "01010", options: {height: this.guardHeight}}), e.push({
                data: t.encode(o, "RRRRRR"),
                text: this.text.substr(7, 6),
                options: {fontSize: this.fontSize}
            }), e.push({
                data: "101",
                options: {height: this.guardHeight}
            }), this.options.lastChar && this.options.displayValue && (e.push({data: "00"}), e.push({
                data: "00000",
                text: this.options.lastChar,
                options: {fontSize: this.fontSize}
            })), e
        }, e.prototype.flatEncoding = function () {
            var t = new c["default"], e = "", n = this.getStructure()[this.data[0]];
            return e += "101", e += t.encode(this.data.substr(1, 6), n), e += "01010", e += t.encode(this.data.substr(7, 6), "RRRRRR"), e += "101", {
                data: e,
                text: this.text
            }
        }, e
    }(l["default"]);
    e["default"] = p
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = n(1), s = r(u), c = n(0), f = r(c), l = function (t) {
        function e(n, r) {
            o(this, e);
            var a = i(this, t.call(this, n, r));
            return a.structure = ["LL", "LG", "GL", "GG"], a
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^[0-9]{2}$/) !== -1
        }, e.prototype.encode = function () {
            var t = new s["default"], e = this.structure[parseInt(this.data) % 4], n = "1011";
            return n += t.encode(this.data, e, "01"), {data: n, text: this.text}
        }, e
    }(f["default"]);
    e["default"] = l
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = n(1), s = r(u), c = n(0), f = r(c), l = function (t) {
        function e(n, r) {
            o(this, e);
            var a = i(this, t.call(this, n, r));
            return a.structure = ["GGLLL", "GLGLL", "GLLGL", "GLLLG", "LGGLL", "LLGGL", "LLLGG", "LGLGL", "LGLLG", "LLGLG"], a
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^[0-9]{5}$/) !== -1
        }, e.prototype.encode = function () {
            var t = new s["default"], e = this.checksum(), n = "1011";
            return n += t.encode(this.data, this.structure[e], "01"), {data: n, text: this.text}
        }, e.prototype.checksum = function () {
            var t = 0;
            return t += 3 * parseInt(this.data[0]), t += 9 * parseInt(this.data[1]), t += 3 * parseInt(this.data[2]), t += 9 * parseInt(this.data[3]), t += 3 * parseInt(this.data[4]), t % 10
        }, e
    }(f["default"]);
    e["default"] = l
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    function u(t) {
        var e, n = 0;
        for (e = 0; e < 7; e += 2) n += 3 * parseInt(t[e]);
        for (e = 1; e < 7; e += 2) n += parseInt(t[e]);
        return (10 - n % 10) % 10
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var s = n(1), c = r(s), f = n(0), l = r(f), p = function (t) {
        function e(n, r) {
            return o(this, e), n.search(/^[0-9]{7}$/) !== -1 && (n += u(n)), i(this, t.call(this, n, r))
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^[0-9]{8}$/) !== -1 && this.data[7] == u(this.data)
        }, e.prototype.encode = function () {
            var t = new c["default"], e = "", n = this.data.substr(0, 4), r = this.data.substr(4, 4);
            return e += t.startBin, e += t.encode(n, "LLLL"), e += t.middleBin, e += t.encode(r, "RRRR"), e += t.endBin, {
                data: e,
                text: this.text
            }
        }, e
    }(l["default"]);
    e["default"] = p
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    function u(t) {
        var e, n = 0;
        for (e = 1; e < 11; e += 2) n += parseInt(t[e]);
        for (e = 0; e < 11; e += 2) n += 3 * parseInt(t[e]);
        return (10 - n % 10) % 10
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var s = n(1), c = r(s), f = n(0), l = r(f), p = function (t) {
        function e(n, r) {
            o(this, e), n.search(/^[0-9]{11}$/) !== -1 && (n += u(n));
            var a = i(this, t.call(this, n, r));
            return a.displayValue = r.displayValue, r.fontSize > 10 * r.width ? a.fontSize = 10 * r.width : a.fontSize = r.fontSize, a.guardHeight = r.height + a.fontSize / 2 + r.textMargin, a
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^[0-9]{12}$/) !== -1 && this.data[11] == u(this.data)
        }, e.prototype.encode = function () {
            return this.options.flat ? this.flatEncoding() : this.guardedEncoding()
        }, e.prototype.flatEncoding = function () {
            var t = new c["default"], e = "";
            return e += "101", e += t.encode(this.data.substr(0, 6), "LLLLLL"), e += "01010", e += t.encode(this.data.substr(6, 6), "RRRRRR"), e += "101", {
                data: e,
                text: this.text
            }
        }, e.prototype.guardedEncoding = function () {
            var t = new c["default"], e = [];
            return this.displayValue && e.push({
                data: "00000000",
                text: this.text.substr(0, 1),
                options: {textAlign: "left", fontSize: this.fontSize}
            }), e.push({
                data: "101" + t.encode(this.data[0], "L"),
                options: {height: this.guardHeight}
            }), e.push({
                data: t.encode(this.data.substr(1, 5), "LLLLL"),
                text: this.text.substr(1, 5),
                options: {fontSize: this.fontSize}
            }), e.push({
                data: "01010",
                options: {height: this.guardHeight}
            }), e.push({
                data: t.encode(this.data.substr(6, 5), "RRRRR"),
                text: this.text.substr(6, 5),
                options: {fontSize: this.fontSize}
            }), e.push({
                data: t.encode(this.data[11], "R") + "101",
                options: {height: this.guardHeight}
            }), this.displayValue && e.push({
                data: "00000000",
                text: this.text.substr(11, 1),
                options: {textAlign: "right", fontSize: this.fontSize}
            }), e
        }, e
    }(l["default"]);
    e["default"] = p
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.UPC = e.EAN2 = e.EAN5 = e.EAN8 = e.EAN13 = void 0;
    var o = n(21), i = r(o), a = n(24), u = r(a), s = n(23), c = r(s), f = n(22), l = r(f), p = n(25), h = r(p);
    e.EAN13 = i["default"], e.EAN8 = u["default"], e.EAN5 = c["default"], e.EAN2 = l["default"], e.UPC = h["default"]
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.GenericBarcode = void 0;
    var u = n(0), s = r(u), c = function (t) {
        function e(n, r) {
            return o(this, e), i(this, t.call(this, n, r))
        }

        return a(e, t), e.prototype.encode = function () {
            return {data: "10101010101010101010101010101010101010101", text: this.text}
        }, e.prototype.valid = function () {
            return !0
        }, e
    }(s["default"]);
    e.GenericBarcode = c
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.ITF = void 0;
    var u = n(0), s = r(u), c = function (t) {
        function e(n, r) {
            o(this, e);
            var a = i(this, t.call(this, n, r));
            return a.binaryRepresentation = {
                0: "00110",
                1: "10001",
                2: "01001",
                3: "11000",
                4: "00101",
                5: "10100",
                6: "01100",
                7: "00011",
                8: "10010",
                9: "01010"
            }, a
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^([0-9]{2})+$/) !== -1
        }, e.prototype.encode = function () {
            for (var t = "1010", e = 0; e < this.data.length; e += 2) t += this.calculatePair(this.data.substr(e, 2));
            return t += "11101", {data: t, text: this.text}
        }, e.prototype.calculatePair = function (t) {
            for (var e = "", n = this.binaryRepresentation[t[0]], r = this.binaryRepresentation[t[1]], o = 0; o < 5; o++) e += "1" == n[o] ? "111" : "1", e += "1" == r[o] ? "000" : "0";
            return e
        }, e
    }(s["default"]);
    e.ITF = c
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    function u(t) {
        for (var e = 0, n = 0; n < 13; n++) e += parseInt(t[n]) * (3 - n % 2 * 2);
        return 10 * Math.ceil(e / 10) - e
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.ITF14 = void 0;
    var s = n(0), c = r(s), f = function (t) {
        function e(n, r) {
            o(this, e), n.search(/^[0-9]{13}$/) !== -1 && (n += u(n));
            var a = i(this, t.call(this, n, r));
            return a.binaryRepresentation = {
                0: "00110",
                1: "10001",
                2: "01001",
                3: "11000",
                4: "00101",
                5: "10100",
                6: "01100",
                7: "00011",
                8: "10010",
                9: "01010"
            }, a
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^[0-9]{14}$/) !== -1 && this.data[13] == u(this.data)
        }, e.prototype.encode = function () {
            for (var t = "1010", e = 0; e < 14; e += 2) t += this.calculatePair(this.data.substr(e, 2));
            return t += "11101", {data: t, text: this.text}
        }, e.prototype.calculatePair = function (t) {
            for (var e = "", n = this.binaryRepresentation[t[0]], r = this.binaryRepresentation[t[1]], o = 0; o < 5; o++) e += "1" == n[o] ? "111" : "1", e += "1" == r[o] ? "000" : "0";
            return e
        }, e
    }(c["default"]);
    e.ITF14 = f
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = n(2), s = r(u), c = n(5), f = function (t) {
        function e(n, r) {
            return o(this, e), i(this, t.call(this, n + (0, c.mod10)(n), r))
        }

        return a(e, t), e
    }(s["default"]);
    e["default"] = f
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = n(2), s = r(u), c = n(5), f = function (t) {
        function e(n, r) {
            return o(this, e), n += (0, c.mod10)(n), n += (0, c.mod10)(n), i(this, t.call(this, n, r))
        }

        return a(e, t), e
    }(s["default"]);
    e["default"] = f
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = n(2), s = r(u), c = n(5), f = function (t) {
        function e(n, r) {
            return o(this, e), i(this, t.call(this, n + (0, c.mod11)(n), r))
        }

        return a(e, t), e
    }(s["default"]);
    e["default"] = f
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var u = n(2), s = r(u), c = n(5), f = function (t) {
        function e(n, r) {
            return o(this, e), n += (0, c.mod11)(n), n += (0, c.mod10)(n), i(this, t.call(this, n, r))
        }

        return a(e, t), e
    }(s["default"]);
    e["default"] = f
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.MSI1110 = e.MSI1010 = e.MSI11 = e.MSI10 = e.MSI = void 0;
    var o = n(2), i = r(o), a = n(30), u = r(a), s = n(32), c = r(s), f = n(31), l = r(f), p = n(33), h = r(p);
    e.MSI = i["default"], e.MSI10 = u["default"], e.MSI11 = c["default"], e.MSI1010 = l["default"], e.MSI1110 = h["default"]
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.codabar = void 0;
    var u = n(0), s = r(u), c = function (t) {
        function e(n, r) {
            o(this, e), 0 === n.search(/^[0-9\-\$\:\.\+\/]+$/) && (n = "A" + n + "A");
            var a = i(this, t.call(this, n.toUpperCase(), r));
            return a.text = a.options.text || a.text.replace(/[A-D]/g, ""), a
        }

        return a(e, t), e.prototype.valid = function () {
            return this.data.search(/^[A-D][0-9\-\$\:\.\+\/]+[A-D]$/) !== -1
        }, e.prototype.encode = function () {
            for (var t = [], e = this.getEncodings(), n = 0; n < this.data.length; n++) t.push(e[this.data.charAt(n)]), n !== this.data.length - 1 && t.push("0");
            return {text: this.text, data: t.join("")}
        }, e.prototype.getEncodings = function () {
            return {
                0: "101010011",
                1: "101011001",
                2: "101001011",
                3: "110010101",
                4: "101101001",
                5: "110101001",
                6: "100101011",
                7: "100101101",
                8: "100110101",
                9: "110100101",
                "-": "101001101",
                $: "101100101",
                ":": "1101011011",
                "/": "1101101011",
                ".": "1101101101",
                "+": "101100110011",
                A: "1011001001",
                B: "1010010011",
                C: "1001001011",
                D: "1010011001"
            }
        }, e
    }(s["default"]);
    e.codabar = c
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e) {
        if (!t) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return !e || "object" != typeof e && "function" != typeof e ? t : e
    }

    function a(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: !1,
                writable: !0,
                configurable: !0
            }
        }), e && (Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e)
    }

    Object.defineProperty(e, "__esModule", {value: !0}), e.pharmacode = void 0;
    var u = n(0), s = r(u), c = function (t) {
        function e(n, r) {
            o(this, e);
            var a = i(this, t.call(this, n, r));
            return a.number = parseInt(n, 10), a
        }

        return a(e, t), e.prototype.encode = function () {
            for (var t = this.number, e = ""; !isNaN(t) && 0 != t;) t % 2 === 0 ? (e = "11100" + e, t = (t - 2) / 2) : (e = "100" + e, t = (t - 1) / 2);
            return e = e.slice(0, -2), {data: e, text: this.text}
        }, e.prototype.valid = function () {
            return this.number >= 3 && this.number <= 131070
        }, e
    }(s["default"]);
    e.pharmacode = c
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t) {
        var e = {};
        for (var n in s["default"]) s["default"].hasOwnProperty(n) && (t.hasAttribute("jsbarcode-" + n.toLowerCase()) && (e[n] = t.getAttribute("jsbarcode-" + n.toLowerCase())), t.hasAttribute("data-" + n.toLowerCase()) && (e[n] = t.getAttribute("data-" + n.toLowerCase())));
        return e.value = t.getAttribute("jsbarcode-value") || t.getAttribute("data-value"), e = (0, a["default"])(e)
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var i = n(7), a = r(i), u = n(8), s = r(u);
    e["default"] = o
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var i = n(3), a = r(i), u = n(9), s = function () {
        function t(e, n, r) {
            o(this, t), this.canvas = e, this.encodings = n, this.options = r
        }

        return t.prototype.render = function () {
            if (!this.canvas.getContext) throw new Error("The browser does not support canvas.");
            this.prepareCanvas();
            for (var t = 0; t < this.encodings.length; t++) {
                var e = (0, a["default"])(this.options, this.encodings[t].options);
                this.drawCanvasBarcode(e, this.encodings[t]), this.drawCanvasText(e, this.encodings[t]), this.moveCanvasDrawing(this.encodings[t])
            }
            this.restoreCanvas()
        }, t.prototype.prepareCanvas = function () {
            var t = this.canvas.getContext("2d");
            t.save(), (0, u.calculateEncodingAttributes)(this.encodings, this.options, t);
            var e = (0, u.getTotalWidthOfEncodings)(this.encodings),
                n = (0, u.getMaximumHeightOfEncodings)(this.encodings);
            this.canvas.width = e + this.options.marginLeft + this.options.marginRight, this.canvas.height = n, t.clearRect(0, 0, this.canvas.width, this.canvas.height), this.options.background && (t.fillStyle = this.options.background, t.fillRect(0, 0, this.canvas.width, this.canvas.height)), t.translate(this.options.marginLeft, 0)
        }, t.prototype.drawCanvasBarcode = function (t, e) {
            var n, r = this.canvas.getContext("2d"), o = e.data;
            n = "top" == t.textPosition ? t.marginTop + t.fontSize + t.textMargin : t.marginTop, r.fillStyle = t.lineColor;
            for (var i = 0; i < o.length; i++) {
                var a = i * t.width + e.barcodePadding;
                "1" === o[i] ? r.fillRect(a, n, t.width, t.height) : o[i] && r.fillRect(a, n, t.width, t.height * o[i])
            }
        }, t.prototype.drawCanvasText = function (t, e) {
            var n = this.canvas.getContext("2d"), r = t.fontOptions + " " + t.fontSize + "px " + t.font;
            if (t.displayValue) {
                var o, i;
                i = "top" == t.textPosition ? t.marginTop + t.fontSize - t.textMargin : t.height + t.textMargin + t.marginTop + t.fontSize, n.font = r, "left" == t.textAlign || e.barcodePadding > 0 ? (o = 0, n.textAlign = "left") : "right" == t.textAlign ? (o = e.width - 1, n.textAlign = "right") : (o = e.width / 2, n.textAlign = "center"), n.fillText(e.text, o, i)
            }
        }, t.prototype.moveCanvasDrawing = function (t) {
            var e = this.canvas.getContext("2d");
            e.translate(t.width, 0)
        }, t.prototype.restoreCanvas = function () {
            var t = this.canvas.getContext("2d");
            t.restore()
        }, t
    }();
    e["default"] = s
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var o = n(38), i = r(o), a = n(41), u = r(a), s = n(40), c = r(s);
    e["default"] = {CanvasRenderer: i["default"], SVGRenderer: u["default"], ObjectRenderer: c["default"]}
}, function (t, e) {
    "use strict";

    function n(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var r = function () {
        function t(e, r, o) {
            n(this, t), this.object = e, this.encodings = r, this.options = o
        }

        return t.prototype.render = function () {
            this.object.encodings = this.encodings
        }, t
    }();
    e["default"] = r
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
    }

    function i(t, e, n) {
        var r = document.createElementNS(l, "g");
        return r.setAttribute("transform", "translate(" + t + ", " + e + ")"), n.appendChild(r), r
    }

    function a(t, e) {
        t.setAttribute("style", "fill:" + e.lineColor + ";")
    }

    function u(t, e, n, r, o) {
        var i = document.createElementNS(l, "rect");
        return i.setAttribute("x", t), i.setAttribute("y", e), i.setAttribute("width", n), i.setAttribute("height", r), o.appendChild(i), i
    }

    Object.defineProperty(e, "__esModule", {value: !0});
    var s = n(3), c = r(s), f = n(9), l = "http://www.w3.org/2000/svg", p = function () {
        function t(e, n, r) {
            o(this, t), this.svg = e, this.encodings = n, this.options = r
        }

        return t.prototype.render = function () {
            var t = this.options.marginLeft;
            this.prepareSVG();
            for (var e = 0; e < this.encodings.length; e++) {
                var n = this.encodings[e], r = (0, c["default"])(this.options, n.options),
                    o = i(t, r.marginTop, this.svg);
                a(o, r), this.drawSvgBarcode(o, r, n), this.drawSVGText(o, r, n), t += n.width
            }
        }, t.prototype.prepareSVG = function () {
            for (; this.svg.firstChild;) this.svg.removeChild(this.svg.firstChild);
            (0, f.calculateEncodingAttributes)(this.encodings, this.options);
            var t = (0, f.getTotalWidthOfEncodings)(this.encodings),
                e = (0, f.getMaximumHeightOfEncodings)(this.encodings),
                n = t + this.options.marginLeft + this.options.marginRight;
            this.setSvgAttributes(n, e), this.options.background && u(0, 0, n, e, this.svg).setAttribute("style", "fill:" + this.options.background + ";")
        }, t.prototype.drawSvgBarcode = function (t, e, n) {
            var r, o = n.data;
            r = "top" == e.textPosition ? e.fontSize + e.textMargin : 0;
            for (var i = 0, a = 0, s = 0; s < o.length; s++) a = s * e.width + n.barcodePadding, "1" === o[s] ? i++ : i > 0 && (u(a - e.width * i, r, e.width * i, e.height, t), i = 0);
            i > 0 && u(a - e.width * (i - 1), r, e.width * i, e.height, t)
        }, t.prototype.drawSVGText = function (t, e, n) {
            var r = document.createElementNS(l, "text");
            if (e.displayValue) {
                var o, i;
                r.setAttribute("font-family", e.font);
                r.setAttribute("font-size", e.fontSize + "px"), i = "top" == e.textPosition ? e.fontSize - e.textMargin : e.height + e.textMargin + e.fontSize, "left" == e.textAlign || n.barcodePadding > 0 ? (o = 0, r.setAttribute("text-anchor", "start")) : "right" == e.textAlign ? (o = n.width - 1, r.setAttribute("text-anchor", "end")) : (o = n.width / 2, r.setAttribute("text-anchor", "middle")), r.setAttribute("x", o), r.setAttribute("y", i), r.appendChild(document.createTextNode(n.text)), t.appendChild(r)
            }
        }, t.prototype.setSvgAttributes = function (t, e) {
            var n = this.svg;
            n.setAttribute("width", t + "px"), n.setAttribute("height", e + "px"), n.setAttribute("x", "0px"), n.setAttribute("y", "0px"), n.setAttribute("viewBox", "0 0 " + t + " " + e), n.setAttribute("xmlns", l), n.setAttribute("version", "1.1"), n.style.transform = "translate(0,0)"
        }, t
    }();
    e["default"] = p
}, function (t, e, n) {
    "use strict";

    function r(t) {
        return t && t.__esModule ? t : {"default": t}
    }

    function o(t, e) {
        E.prototype[e] = E.prototype[e.toUpperCase()] = E.prototype[e.toLowerCase()] = function (n, r) {
            var o = this;
            return o._errorHandler.wrapBarcodeCall(function () {
                r.text = "undefined" == typeof r.text ? void 0 : "" + r.text;
                var a = (0, l["default"])(o._options, r);
                a = (0, _["default"])(a);
                var u = t[e], s = i(n, u, a);
                return o._encodings.push(s), o
            })
        }
    }

    function i(t, e, n) {
        t = "" + t;
        var r = new e(t, n);
        if (!r.valid()) throw new m.InvalidInputException(r.constructor.name, t);
        var o = r.encode();
        o = (0, h["default"])(o);
        for (var i = 0; i < o.length; i++) o[i].options = (0, l["default"])(n, o[i].options);
        return o
    }

    function a() {
        return c["default"].CODE128 ? "CODE128" : Object.keys(c["default"])[0]
    }

    function u(t, e, n) {
        e = (0, h["default"])(e);
        for (var r = 0; r < e.length; r++) e[r].options = (0, l["default"])(n, e[r].options), (0, y["default"])(e[r].options);
        (0, y["default"])(n);
        var o = t.renderer, i = new o(t.element, e, n);
        i.render(), t.afterRender && t.afterRender()
    }

    var s = n(10), c = r(s), f = n(3), l = r(f), p = n(14), h = r(p), d = n(12), y = r(d), b = n(13), g = r(b),
        v = n(7), _ = r(v), w = n(11), x = r(w), m = n(6), O = n(8), C = r(O), E = function () {
        }, j = function (t, e, n) {
            var r = new E;
            if ("undefined" == typeof t) throw Error("No element to render on was provided.");
            return r._renderProperties = (0, g["default"])(t), r._encodings = [], r._options = C["default"], r._errorHandler = new x["default"](r), "undefined" != typeof e && (n = n || {}, n.format || (n.format = a()), r.options(n)[n.format](e, n).render()), r
        };
    j.getModule = function (t) {
        return c["default"][t]
    };
    for (var P in c["default"]) c["default"].hasOwnProperty(P) && o(c["default"], P);
    E.prototype.options = function (t) {
        return this._options = (0, l["default"])(this._options, t), this
    }, E.prototype.blank = function (t) {
        var e = "0".repeat(t);
        return this._encodings.push({data: e}), this
    }, E.prototype.init = function () {
        if (this._renderProperties) {
            Array.isArray(this._renderProperties) || (this._renderProperties = [this._renderProperties]);
            var t;
            for (var e in this._renderProperties) {
                t = this._renderProperties[e];
                var n = (0, l["default"])(this._options, t.options);
                "auto" == n.format && (n.format = a()), this._errorHandler.wrapBarcodeCall(function () {
                    var e = n.value, r = c["default"][n.format.toUpperCase()], o = i(e, r, n);
                    u(t, o, n)
                })
            }
        }
    }, E.prototype.render = function () {
        if (!this._renderProperties) throw new m.NoElementException;
        if (Array.isArray(this._renderProperties)) for (var t = 0; t < this._renderProperties.length; t++) u(this._renderProperties[t], this._encodings, this._options); else u(this._renderProperties, this._encodings, this._options);
        return this
    }, E.prototype._defaults = C["default"], "undefined" != typeof window && (window.JsBarcode = j), "undefined" != typeof jQuery && (jQuery.fn.JsBarcode = function (t, e) {
        var n = [];
        return jQuery(this).each(function () {
            n.push(this)
        }), j(n, t, e)
    }), t.exports = j
}]);