/* Curved text */
(function (global) {
    var fabric = global.fabric || (global.fabric = {}), extend = fabric.util.object.extend,
        clone = fabric.util.object.clone;
    if (fabric.CurvedText) {
        fabric.warn("fabric.CurvedText is already defined");
        return
    }
    var stateProperties = fabric.Text.prototype.stateProperties.concat();
    stateProperties.push("radius", "spacing", "reverse", "effect", "range", "largeFont", "smallFont");
    var _dimensionAffectingProps = fabric.Text.prototype._dimensionAffectingProps.concat(["radius", "spacing", "reverse", "fill", "effect", "width",
        "height", "range", "fontSize", "shadow", "largeFont", "smallFont"]);
    var letterProperties = ["backgroundColor", "textBackgroundColor", "textDecoration", "stroke", "strokeWidth", "shadow", "fontWeight", "fontStyle", "strokeWidth", "textAlign"];
    fabric.CurvedText = fabric.util.createClass(fabric.Text, fabric.Collection, {
        type: "curvedText",
        radius: 50,
        range: 5,
        smallFont: 10,
        largeFont: 30,
        effect: "curved",
        spacing: 20,
        reverse: false,
        stateProperties: stateProperties,
        _dimensionAffectingProps: _dimensionAffectingProps,
        _isRendering: 0,
        complexity: function () {
            this.callSuper("complexity")
        },
        initialize: function (text, options) {
            options || (options = {});
            this.__skipDimension = true;
            delete options.text;
            this.setOptions(options);
            this.__skipDimension = false;
            this.callSuper("initialize", text, options);
            this.letters = new fabric.Group([], {selectable: false, padding: 0});
            this.setText(text)
        },
        setText: function (text) {
            if (this.letters) {
                while (this.letters.size()) this.letters.remove(this.letters.item(0));
                for (var i = 0; i < text.length; i++) if (this.letters.item(i) === undefined) this.letters.add(new fabric.IText(text[i])); else this.letters.item(i).text =
                    text[i]
            }
            this.text = text;
            var i = this.letters.size();
            while (i--) {
                var letter = this.letters.item(i);
                letter.set({
                    objectCaching: false,
                    fill: this.fill,
                    stroke: this.stroke,
                    strokeWidth: this.strokeWidth,
                    fontFamily: this.fontFamily,
                    fontSize: this.fontSize,
                    fontStyle: this.fontStyle,
                    fontWeight: this.fontWeight,
                    underline: this.underline,
                    overline: this.overline,
                    linethrough: this.linethrough,
                    lineHeight: this.lineHeight
                })
            }
            this._updateLetters();
            this.canvas && this.canvas.renderAll()
        },
        _initDimensions: function (ctx) {
            if (this.__skipDimension) return;
            if (!ctx) {
                ctx = fabric.util.createCanvasElement().getContext("2d");
                this._setTextStyles(ctx)
            }
            this._textLines = this.text.split(this._reNewline);
            this._clearCache();
            var currentTextAlign = this.textAlign;
            this.textAlign = "left";
            this.width = this.get("width");
            this.textAlign = currentTextAlign;
            this.height = this.get("height");
            this._updateLetters()
        },
        _updateLetters: function () {
            var renderingCode = fabric.util.getRandomInt(100, 999);
            this._isRendering = renderingCode;
            if (this.letters && this.text) {
                var curAngle = 0, curAngleRotation = 0,
                    angleRadians = 0, align = 0, textWidth = 0, space = parseInt(this.spacing), fixedLetterAngle = 0;
                if (this.effect === "curved") {
                    for (var i = 0, len = this.text.length; i < len; i++) textWidth += this.letters.item(i).width + space;
                    textWidth -= space
                } else if (this.effect === "arc") {
                    fixedLetterAngle = (this.letters.item(0).fontSize + space) / this.radius / (Math.PI / 180);
                    textWidth = (this.text.length + 1) * (this.letters.item(0).fontSize + space)
                }
                curAngle = -(textWidth / 2 / this.radius / (Math.PI / 180));
                if (this.reverse) curAngle = -curAngle;
                var width = 0, multiplier =
                    this.reverse ? -1 : 1, thisLetterAngle = 0, lastLetterAngle = 0;
                for (var i = 0, len = this.text.length; i < len; i++) {
                    if (renderingCode !== this._isRendering) return;
                    for (var key in letterProperties) this.letters.item(i).set(key, this.get(key));
                    this.letters.item(i).set("left", width);
                    this.letters.item(i).set("top", 0);
                    this.letters.item(i).set("angle", 0);
                    this.letters.item(i).set("padding", 0);
                    if (this.effect === "curved") {
                        thisLetterAngle = (this.letters.item(i).width + space) / this.radius / (Math.PI / 180);
                        curAngle = multiplier * (multiplier *
                            curAngle + lastLetterAngle);
                        angleRadians = curAngle * (Math.PI / 180);
                        lastLetterAngle = thisLetterAngle;
                        this.letters.item(i).set("angle", curAngle);
                        this.letters.item(i).set("top", multiplier * -1 * (Math.cos(angleRadians) * this.radius));
                        this.letters.item(i).set("left", multiplier * (Math.sin(angleRadians) * this.radius));
                        this.letters.item(i).set("padding", 0);
                        this.letters.item(i).set("selectable", false)
                    } else if (this.effect === "arc") {
                        curAngle = multiplier * (multiplier * curAngle + fixedLetterAngle);
                        angleRadians = curAngle * (Math.PI /
                            180);
                        this.letters.item(i).set("top", multiplier * -1 * (Math.cos(angleRadians) * this.radius));
                        this.letters.item(i).set("left", multiplier * (Math.sin(angleRadians) * this.radius));
                        this.letters.item(i).set("padding", 0);
                        this.letters.item(i).set("selectable", false)
                    } else if (this.effect === "STRAIGHT") {
                        this.letters.item(i).set("left", width);
                        this.letters.item(i).set("top", 0);
                        this.letters.item(i).set("angle", 0);
                        width += this.letters.item(i).get("width");
                        this.letters.item(i).set("padding", 0);
                        this.letters.item(i).set({
                            borderColor: "red",
                            cornerColor: "green", cornerSize: 6, transparentCorners: false
                        });
                        this.letters.item(i).set("selectable", false)
                    } else if (this.effect === "smallToLarge") {
                        var small = parseInt(this.smallFont);
                        var large = parseInt(this.largeFont);
                        var difference = large - small;
                        var center = Math.ceil(this.text.length / 2);
                        var step = difference / this.text.length;
                        var newfont = small + i * step;
                        this.letters.item(i).set("fontSize", newfont);
                        this.letters.item(i).set("left", width);
                        width += this.letters.item(i).get("width");
                        this.letters.item(i).set("padding",
                            0);
                        this.letters.item(i).set("selectable", false);
                        this.letters.item(i).set("top", -1 * this.letters.item(i).get("fontSize") + i)
                    } else if (this.effect === "largeToSmallTop") {
                        var small = parseInt(this.largeFont);
                        var large = parseInt(this.smallFont);
                        var difference = large - small;
                        var center = Math.ceil(this.text.length / 2);
                        var step = difference / this.text.length;
                        var newfont = small + i * step;
                        this.letters.item(i).set("fontSize", newfont);
                        this.letters.item(i).set("left", width);
                        width += this.letters.item(i).get("width");
                        this.letters.item(i).set("padding",
                            0);
                        this.letters.item(i).set({
                            borderColor: "red",
                            cornerColor: "green",
                            cornerSize: 6,
                            transparentCorners: false
                        });
                        this.letters.item(i).set("padding", 0);
                        this.letters.item(i).set("selectable", false);
                        this.letters.item(i).top = -1 * this.letters.item(i).get("fontSize") + i / this.text.length
                    } else if (this.effect === "largeToSmallBottom") {
                        var small = parseInt(this.largeFont);
                        var large = parseInt(this.smallFont);
                        var difference = large - small;
                        var center = Math.ceil(this.text.length / 2);
                        var step = difference / this.text.length;
                        var newfont =
                            small + i * step;
                        this.letters.item(i).set("fontSize", newfont);
                        this.letters.item(i).set("left", width);
                        width += this.letters.item(i).get("width");
                        this.letters.item(i).set("padding", 0);
                        this.letters.item(i).set({
                            borderColor: "red",
                            cornerColor: "green",
                            cornerSize: 6,
                            transparentCorners: false
                        });
                        this.letters.item(i).set("padding", 0);
                        this.letters.item(i).set("selectable", false);
                        this.letters.item(i).top = -1 * this.letters.item(i).get("fontSize") - i
                    } else if (this.effect === "bulge") {
                        var small = parseInt(this.smallFont);
                        var large =
                            parseInt(this.largeFont);
                        var difference = large - small;
                        var center = Math.ceil(this.text.length / 2);
                        var step = difference / (this.text.length - center);
                        if (i < center) var newfont = small + i * step; else var newfont = large - (i - center + 1) * step;
                        this.letters.item(i).set("fontSize", newfont);
                        this.letters.item(i).set("left", width);
                        width += this.letters.item(i).get("width");
                        this.letters.item(i).set("padding", 0);
                        this.letters.item(i).set("selectable", false);
                        this.letters.item(i).set("top", -1 * this.letters.item(i).get("height") / 2)
                    }
                }
                var scaleX =
                    this.letters.get("scaleX");
                var scaleY = this.letters.get("scaleY");
                var angle = this.letters.get("angle");
                this.letters.set("scaleX", 1);
                this.letters.set("scaleY", 1);
                this.letters.set("angle", 0);
                this.letters._calcBounds();
                this.letters._updateObjectsCoords();
                this.letters.set("scaleX", scaleX);
                this.letters.set("scaleY", scaleY);
                this.letters.set("angle", angle);
                this.width = this.letters.width;
                this.height = this.letters.height;
                this.letters.left = -(this.letters.width / 2);
                this.letters.top = -(this.letters.height / 2)
            }
        },
        render: function (ctx) {
            if (!this.visible) return;
            if (!this.letters) return;
            ctx.save();
            this.transform(ctx);
            var groupScaleFactor = Math.max(this.scaleX, this.scaleY);
            this.clipTo && fabric.util.clipContext(this, ctx);
            for (var i = 0, len = this.letters.size(); i < len; i++) {
                var object = this.letters.item(i), originalScaleFactor = object.borderScaleFactor,
                    originalHasRotatingPoint = object.hasRotatingPoint;
                if (!object.visible) continue;
                object.render(ctx)
            }
            this.clipTo && ctx.restore();
            ctx.restore();
            this.setCoords()
        },
        _set: function (key, value) {
            if (key === "text") {
                this.setText(value);
                return
            }
            this.callSuper("_set",
                key, value);
            if (this.text && this.letters) {
                if (["angle", "left", "top", "scaleX", "scaleY", "width", "height"].indexOf(key) === -1) {
                    var i = this.letters.size();
                    while (i--) this.letters.item(i).set(key, value)
                }
                if (this._dimensionAffectingProps.indexOf(key) !== -1) {
                    this._updateLetters();
                    this.setCoords()
                }
            }
        },
        initDimensions: function () {
        },
        toObject: function (propertiesToInclude) {
            var object = extend(this.callSuper("toObject", propertiesToInclude), {
                radius: this.radius,
                spacing: this.spacing,
                reverse: this.reverse,
                effect: this.effect,
                range: this.range,
                smallFont: this.smallFont,
                largeFont: this.largeFont,
                rtl: this.rtl
            });
            if (!this.includeDefaultValues) this._removeDefaultValues(object);
            return object
        },
        toString: function () {
            return "#<fabric.CurvedText (" + this.complexity() + '): { "text": "' + this.text + '", "fontFamily": "' + this.fontFamily + '", "radius": "' + this.radius + '", "spacing": "' + this.spacing + '", "reverse": "' + this.reverse + '" }>'
        },
        toSVG: function (reviver) {
            var markup = ["<g ", 'transform="', this.getSvgTransform(), '">'];
            if (this.letters) for (var i = 0, len = this.letters.size(); i <
            len; i++) markup.push(this.letters.item(i).toSVG(reviver));
            markup.push("</g>");
            return reviver ? reviver(markup.join("")) : markup.join("")
        }
    });
    fabric.CurvedText.fromObject = function (object, callback) {
        return fabric.Object._fromObject("CurvedText", object, callback, "text")
    };
    fabric.CurvedText.async = false
})(typeof exports !== "undefined" ? exports : this);
/*jQuery Mobile Events*/
"use strict";
!function (e) {
    e.attrFn = e.attrFn || {};
    var t = "ontouchstart" in window, a = {
        tap_pixel_range: 5,
        swipe_h_threshold: 50,
        swipe_v_threshold: 50,
        taphold_threshold: 750,
        doubletap_int: 500,
        shake_threshold: 15,
        touch_capable: t,
        orientation_support: "orientation" in window && "onorientationchange" in window,
        startevent: t ? "touchstart" : "mousedown",
        endevent: t ? "touchend" : "mouseup",
        moveevent: t ? "touchmove" : "mousemove",
        tapevent: t ? "tap" : "click",
        scrollevent: t ? "touchmove" : "scroll",
        hold_timer: null,
        tap_timer: null
    };
    e.touch = {}, e.isTouchCapable = function () {
        return a.touch_capable
    }, e.getStartEvent = function () {
        return a.startevent
    }, e.getEndEvent = function () {
        return a.endevent
    }, e.getMoveEvent = function () {
        return a.moveevent
    }, e.getTapEvent = function () {
        return a.tapevent
    }, e.getScrollEvent = function () {
        return a.scrollevent
    }, e.touch.setSwipeThresholdX = function (e) {
        if ("number" != typeof e) throw new Error("Threshold parameter must be a type of number");
        a.swipe_h_threshold = e
    }, e.touch.setSwipeThresholdY = function (e) {
        if ("number" != typeof e) throw new Error("Threshold parameter must be a type of number");
        a.swipe_v_threshold = e
    }, e.touch.setDoubleTapInt = function (e) {
        if ("number" != typeof e) throw new Error("Interval parameter must be a type of number");
        a.doubletap_int = e
    }, e.touch.setTapHoldThreshold = function (e) {
        if ("number" != typeof e) throw new Error("Threshold parameter must be a type of number");
        a.taphold_threshold = e
    }, e.touch.setTapRange = function (e) {
        if ("number" != typeof e) throw new Error("Ranger parameter must be a type of number");
        a.tap_pixel_range = threshold
    }, e.each(["tapstart", "tapend", "tapmove", "tap", "singletap", "doubletap", "taphold", "swipe", "swipeup", "swiperight", "swipedown", "swipeleft", "swipeend", "scrollstart", "scrollend", "orientationchange", "tap2", "taphold2"], function (t, a) {
        e.fn[a] = function (e) {
            return e ? this.on(a, e) : this.trigger(a)
        }, e.attrFn[a] = !0
    }), e.event.special.tapstart = {
        setup: function () {
            var t = this, o = e(t);
            o.on(a.startevent, function e(n) {
                if (o.data("callee", e), n.which && 1 !== n.which) return !1;
                var i = n.originalEvent, r = {
                    position: {
                        x: a.touch_capable ? i.touches[0].pageX : n.pageX,
                        y: a.touch_capable ? i.touches[0].pageY : n.pageY
                    },
                    offset: {
                        x: a.touch_capable ? Math.round(i.changedTouches[0].pageX - (o.offset() ? o.offset().left : 0)) : Math.round(n.pageX - (o.offset() ? o.offset().left : 0)),
                        y: a.touch_capable ? Math.round(i.changedTouches[0].pageY - (o.offset() ? o.offset().top : 0)) : Math.round(n.pageY - (o.offset() ? o.offset().top : 0))
                    },
                    time: Date.now(),
                    target: n.target
                };
                return w(t, "tapstart", n, r), !0
            })
        }, remove: function () {
            e(this).off(a.startevent, e(this).data.callee)
        }
    }, e.event.special.tapmove = {
        setup: function () {
            var t = this, o = e(t);
            o.on(a.moveevent, function e(n) {
                o.data("callee", e);
                var i = n.originalEvent, r = {
                    position: {
                        x: a.touch_capable ? i.touches[0].pageX : n.pageX,
                        y: a.touch_capable ? i.touches[0].pageY : n.pageY
                    },
                    offset: {
                        x: a.touch_capable ? Math.round(i.changedTouches[0].pageX - (o.offset() ? o.offset().left : 0)) : Math.round(n.pageX - (o.offset() ? o.offset().left : 0)),
                        y: a.touch_capable ? Math.round(i.changedTouches[0].pageY - (o.offset() ? o.offset().top : 0)) : Math.round(n.pageY - (o.offset() ? o.offset().top : 0))
                    },
                    time: Date.now(),
                    target: n.target
                };
                return w(t, "tapmove", n, r), !0
            })
        }, remove: function () {
            e(this).off(a.moveevent, e(this).data.callee)
        }
    }, e.event.special.tapend = {
        setup: function () {
            var t = this, o = e(t);
            o.on(a.endevent, function e(n) {
                o.data("callee", e);
                var i = n.originalEvent, r = {
                    position: {
                        x: a.touch_capable ? i.changedTouches[0].pageX : n.pageX,
                        y: a.touch_capable ? i.changedTouches[0].pageY : n.pageY
                    },
                    offset: {
                        x: a.touch_capable ? Math.round(i.changedTouches[0].pageX - (o.offset() ? o.offset().left : 0)) : Math.round(n.pageX - (o.offset() ? o.offset().left : 0)),
                        y: a.touch_capable ? Math.round(i.changedTouches[0].pageY - (o.offset() ? o.offset().top : 0)) : Math.round(n.pageY - (o.offset() ? o.offset().top : 0))
                    },
                    time: Date.now(),
                    target: n.target
                };
                return w(t, "tapend", n, r), !0
            })
        }, remove: function () {
            e(this).off(a.endevent, e(this).data.callee)
        }
    }, e.event.special.taphold = {
        setup: function () {
            var t, o = this, n = e(o), i = {x: 0, y: 0}, r = 0, s = 0;
            n.on(a.startevent, function e(p) {
                if (p.which && 1 !== p.which) return !1;
                n.data("tapheld", !1), t = p.target;
                var h = p.originalEvent, c = Date.now();
                a.touch_capable ? h.touches[0].pageX : p.pageX, a.touch_capable ? h.touches[0].pageY : p.pageY, a.touch_capable ? (h.touches[0].pageX, h.touches[0].target.offsetLeft) : p.offsetX, a.touch_capable ? (h.touches[0].pageY, h.touches[0].target.offsetTop) : p.offsetY;
                i.x = p.originalEvent.targetTouches ? p.originalEvent.targetTouches[0].pageX : p.pageX, i.y = p.originalEvent.targetTouches ? p.originalEvent.targetTouches[0].pageY : p.pageY, r = i.x, s = i.y;
                var u = n.parent().data("threshold") ? n.parent().data("threshold") : n.data("threshold"),
                    f = void 0 !== u && !1 !== u && parseInt(u) ? parseInt(u) : a.taphold_threshold;
                return a.hold_timer = window.setTimeout(function () {
                    var u = i.x - r, f = i.y - s;
                    if (p.target == t && (i.x == r && i.y == s || u >= -a.tap_pixel_range && u <= a.tap_pixel_range && f >= -a.tap_pixel_range && f <= a.tap_pixel_range)) {
                        n.data("tapheld", !0);
                        for (var l = Date.now() - c, g = p.originalEvent.targetTouches ? p.originalEvent.targetTouches : [p], d = [], v = 0; v < g.length; v++) {
                            var _ = {
                                position: {
                                    x: a.touch_capable ? h.changedTouches[v].pageX : p.pageX,
                                    y: a.touch_capable ? h.changedTouches[v].pageY : p.pageY
                                },
                                offset: {
                                    x: a.touch_capable ? Math.round(h.changedTouches[v].pageX - (n.offset() ? n.offset().left : 0)) : Math.round(p.pageX - (n.offset() ? n.offset().left : 0)),
                                    y: a.touch_capable ? Math.round(h.changedTouches[v].pageY - (n.offset() ? n.offset().top : 0)) : Math.round(p.pageY - (n.offset() ? n.offset().top : 0))
                                },
                                time: Date.now(),
                                target: p.target,
                                duration: l
                            };
                            d.push(_)
                        }
                        var T = 2 == g.length ? "taphold2" : "taphold";
                        n.data("callee1", e), w(o, T, p, d)
                    }
                }, f), !0
            }).on(a.endevent, function e() {
                n.data("callee2", e), n.data("tapheld", !1), window.clearTimeout(a.hold_timer)
            }).on(a.moveevent, function e(t) {
                n.data("callee3", e), r = t.originalEvent.targetTouches ? t.originalEvent.targetTouches[0].pageX : t.pageX, s = t.originalEvent.targetTouches ? t.originalEvent.targetTouches[0].pageY : t.pageY
            })
        }, remove: function () {
            e(this).off(a.startevent, e(this).data.callee1).off(a.endevent, e(this).data.callee2).off(a.moveevent, e(this).data.callee3)
        }
    }, e.event.special.doubletap = {
        setup: function () {
            var t, o, n = this, i = e(n), r = null, s = !1;
            i.on(a.startevent, function t(n) {
                return (!n.which || 1 === n.which) && (i.data("doubletapped", !1), n.target, i.data("callee1", t), o = n.originalEvent, r || (r = {
                    position: {
                        x: a.touch_capable ? o.touches[0].pageX : n.pageX,
                        y: a.touch_capable ? o.touches[0].pageY : n.pageY
                    },
                    offset: {
                        x: a.touch_capable ? Math.round(o.changedTouches[0].pageX - (i.offset() ? i.offset().left : 0)) : Math.round(n.pageX - (i.offset() ? i.offset().left : 0)),
                        y: a.touch_capable ? Math.round(o.changedTouches[0].pageY - (i.offset() ? i.offset().top : 0)) : Math.round(n.pageY - (i.offset() ? i.offset().top : 0))
                    },
                    time: Date.now(),
                    target: n.target,
                    element: n.originalEvent.srcElement,
                    index: e(n.target).index()
                }), !0)
            }).on(a.endevent, function p(h) {
                var c = Date.now(), u = c - (i.data("lastTouch") || c + 1);
                if (window.clearTimeout(t), i.data("callee2", p), u < a.doubletap_int && e(h.target).index() == r.index && u > 100) {
                    i.data("doubletapped", !0), window.clearTimeout(a.tap_timer);
                    var f = {
                        position: {
                            x: a.touch_capable ? h.originalEvent.changedTouches[0].pageX : h.pageX,
                            y: a.touch_capable ? h.originalEvent.changedTouches[0].pageY : h.pageY
                        },
                        offset: {
                            x: a.touch_capable ? Math.round(o.changedTouches[0].pageX - (i.offset() ? i.offset().left : 0)) : Math.round(h.pageX - (i.offset() ? i.offset().left : 0)),
                            y: a.touch_capable ? Math.round(o.changedTouches[0].pageY - (i.offset() ? i.offset().top : 0)) : Math.round(h.pageY - (i.offset() ? i.offset().top : 0))
                        },
                        time: Date.now(),
                        target: h.target,
                        element: h.originalEvent.srcElement,
                        index: e(h.target).index()
                    }, l = {firstTap: r, secondTap: f, interval: f.time - r.time};
                    s || (w(n, "doubletap", h, l), r = null), s = !0, window.setTimeout(function () {
                        s = !1
                    }, a.doubletap_int)
                } else i.data("lastTouch", c), t = window.setTimeout(function () {
                    r = null, window.clearTimeout(t)
                }, a.doubletap_int, [h]);
                i.data("lastTouch", c)
            })
        }, remove: function () {
            e(this).off(a.startevent, e(this).data.callee1).off(a.endevent, e(this).data.callee2)
        }
    }, e.event.special.singletap = {
        setup: function () {
            var t = this, o = e(t), n = null, i = null, r = {x: 0, y: 0};
            o.on(a.startevent, function e(t) {
                return (!t.which || 1 === t.which) && (i = Date.now(), n = t.target, o.data("callee1", e), r.x = t.originalEvent.targetTouches ? t.originalEvent.targetTouches[0].pageX : t.pageX, r.y = t.originalEvent.targetTouches ? t.originalEvent.targetTouches[0].pageY : t.pageY, !0)
            }).on(a.endevent, function e(s) {
                if (o.data("callee2", e), s.target == n) {
                    var p = s.originalEvent.changedTouches ? s.originalEvent.changedTouches[0].pageX : s.pageX,
                        h = s.originalEvent.changedTouches ? s.originalEvent.changedTouches[0].pageY : s.pageY;
                    a.tap_timer = window.setTimeout(function () {
                        var e = r.x - p, n = r.y - h;
                        if (!o.data("doubletapped") && !o.data("tapheld") && (r.x == p && r.y == h || e >= -a.tap_pixel_range && e <= a.tap_pixel_range && n >= -a.tap_pixel_range && n <= a.tap_pixel_range)) {
                            var c = s.originalEvent, u = {
                                position: {
                                    x: a.touch_capable ? c.changedTouches[0].pageX : s.pageX,
                                    y: a.touch_capable ? c.changedTouches[0].pageY : s.pageY
                                },
                                offset: {
                                    x: a.touch_capable ? Math.round(c.changedTouches[0].pageX - (o.offset() ? o.offset().left : 0)) : Math.round(s.pageX - (o.offset() ? o.offset().left : 0)),
                                    y: a.touch_capable ? Math.round(c.changedTouches[0].pageY - (o.offset() ? o.offset().top : 0)) : Math.round(s.pageY - (o.offset() ? o.offset().top : 0))
                                },
                                time: Date.now(),
                                target: s.target
                            };
                            u.time - i < a.taphold_threshold && w(t, "singletap", s, u)
                        }
                    }, a.doubletap_int)
                }
            })
        }, remove: function () {
            e(this).off(a.startevent, e(this).data.callee1).off(a.endevent, e(this).data.callee2)
        }
    }, e.event.special.tap = {
        setup: function () {
            var t, o, n = this, i = e(n), r = !1, s = null, p = {x: 0, y: 0};
            i.on(a.startevent, function e(a) {
                return i.data("callee1", e), (!a.which || 1 === a.which) && (r = !0, p.x = a.originalEvent.targetTouches ? a.originalEvent.targetTouches[0].pageX : a.pageX, p.y = a.originalEvent.targetTouches ? a.originalEvent.targetTouches[0].pageY : a.pageY, t = Date.now(), s = a.target, o = a.originalEvent.targetTouches ? a.originalEvent.targetTouches : [a], !0)
            }).on(a.endevent, function e(h) {
                i.data("callee2", e);
                var c = h.originalEvent.targetTouches ? h.originalEvent.changedTouches[0].pageX : h.pageX,
                    u = h.originalEvent.targetTouches ? h.originalEvent.changedTouches[0].pageY : h.pageY, f = p.x - c,
                    l = p.y - u;
                if (s == h.target && r && Date.now() - t < a.taphold_threshold && (p.x == c && p.y == u || f >= -a.tap_pixel_range && f <= a.tap_pixel_range && l >= -a.tap_pixel_range && l <= a.tap_pixel_range)) {
                    for (var g = h.originalEvent, d = [], v = 0; v < o.length; v++) {
                        var _ = {
                            position: {
                                x: a.touch_capable ? g.changedTouches[v].pageX : h.pageX,
                                y: a.touch_capable ? g.changedTouches[v].pageY : h.pageY
                            },
                            offset: {
                                x: a.touch_capable ? Math.round(g.changedTouches[v].pageX - (i.offset() ? i.offset().left : 0)) : Math.round(h.pageX - (i.offset() ? i.offset().left : 0)),
                                y: a.touch_capable ? Math.round(g.changedTouches[v].pageY - (i.offset() ? i.offset().top : 0)) : Math.round(h.pageY - (i.offset() ? i.offset().top : 0))
                            },
                            time: Date.now(),
                            target: h.target
                        };
                        d.push(_)
                    }
                    var T = 2 == o.length ? "tap2" : "tap";
                    w(n, T, h, d)
                }
            })
        }, remove: function () {
            e(this).off(a.startevent, e(this).data.callee1).off(a.endevent, e(this).data.callee2)
        }
    }, e.event.special.swipe = {
        setup: function () {
            var t, o = e(this), n = !1, i = !1, r = {x: 0, y: 0}, s = {x: 0, y: 0};
            o.on(a.startevent, function i(p) {
                (o = e(p.currentTarget)).data("callee1", i), r.x = p.originalEvent.targetTouches ? p.originalEvent.targetTouches[0].pageX : p.pageX, r.y = p.originalEvent.targetTouches ? p.originalEvent.targetTouches[0].pageY : p.pageY, s.x = r.x, s.y = r.y, n = !0;
                var h = p.originalEvent;
                t = {
                    position: {
                        x: a.touch_capable ? h.touches[0].pageX : p.pageX,
                        y: a.touch_capable ? h.touches[0].pageY : p.pageY
                    },
                    offset: {
                        x: a.touch_capable ? Math.round(h.changedTouches[0].pageX - (o.offset() ? o.offset().left : 0)) : Math.round(p.pageX - (o.offset() ? o.offset().left : 0)),
                        y: a.touch_capable ? Math.round(h.changedTouches[0].pageY - (o.offset() ? o.offset().top : 0)) : Math.round(p.pageY - (o.offset() ? o.offset().top : 0))
                    },
                    time: Date.now(),
                    target: p.target
                }
            }), o.on(a.moveevent, function p(h) {
                var c;
                (o = e(h.currentTarget)).data("callee2", p), s.x = h.originalEvent.targetTouches ? h.originalEvent.targetTouches[0].pageX : h.pageX, s.y = h.originalEvent.targetTouches ? h.originalEvent.targetTouches[0].pageY : h.pageY;
                var u = o.parent().data("xthreshold") ? o.parent().data("xthreshold") : o.data("xthreshold"),
                    f = o.parent().data("ythreshold") ? o.parent().data("ythreshold") : o.data("ythreshold"),
                    l = void 0 !== u && !1 !== u && parseInt(u) ? parseInt(u) : a.swipe_h_threshold,
                    g = void 0 !== f && !1 !== f && parseInt(f) ? parseInt(f) : a.swipe_v_threshold;
                if (r.y > s.y && r.y - s.y > g && (c = "swipeup"), r.x < s.x && s.x - r.x > l && (c = "swiperight"), r.y < s.y && s.y - r.y > g && (c = "swipedown"), r.x > s.x && r.x - s.x > l && (c = "swipeleft"), void 0 != c && n) {
                    r.x = 0, r.y = 0, s.x = 0, s.y = 0, n = !1;
                    var d = h.originalEvent, v = {
                        position: {
                            x: a.touch_capable ? d.touches[0].pageX : h.pageX,
                            y: a.touch_capable ? d.touches[0].pageY : h.pageY
                        },
                        offset: {
                            x: a.touch_capable ? Math.round(d.changedTouches[0].pageX - (o.offset() ? o.offset().left : 0)) : Math.round(h.pageX - (o.offset() ? o.offset().left : 0)),
                            y: a.touch_capable ? Math.round(d.changedTouches[0].pageY - (o.offset() ? o.offset().top : 0)) : Math.round(h.pageY - (o.offset() ? o.offset().top : 0))
                        },
                        time: Date.now(),
                        target: h.target
                    }, w = Math.abs(t.position.x - v.position.x), _ = Math.abs(t.position.y - v.position.y), T = {
                        startEvnt: t,
                        endEvnt: v,
                        direction: c.replace("swipe", ""),
                        xAmount: w,
                        yAmount: _,
                        duration: v.time - t.time
                    };
                    i = !0, o.trigger("swipe", T).trigger(c, T)
                }
            }), o.on(a.endevent, function r(s) {
                var p = "";
                if ((o = e(s.currentTarget)).data("callee3", r), i) {
                    var h = o.data("xthreshold"), c = o.data("ythreshold"),
                        u = void 0 !== h && !1 !== h && parseInt(h) ? parseInt(h) : a.swipe_h_threshold,
                        f = void 0 !== c && !1 !== c && parseInt(c) ? parseInt(c) : a.swipe_v_threshold,
                        l = s.originalEvent, g = {
                            position: {
                                x: a.touch_capable ? l.changedTouches[0].pageX : s.pageX,
                                y: a.touch_capable ? l.changedTouches[0].pageY : s.pageY
                            },
                            offset: {
                                x: a.touch_capable ? Math.round(l.changedTouches[0].pageX - (o.offset() ? o.offset().left : 0)) : Math.round(s.pageX - (o.offset() ? o.offset().left : 0)),
                                y: a.touch_capable ? Math.round(l.changedTouches[0].pageY - (o.offset() ? o.offset().top : 0)) : Math.round(s.pageY - (o.offset() ? o.offset().top : 0))
                            },
                            time: Date.now(),
                            target: s.target
                        };
                    t.position.y > g.position.y && t.position.y - g.position.y > f && (p = "swipeup"), t.position.x < g.position.x && g.position.x - t.position.x > u && (p = "swiperight"), t.position.y < g.position.y && g.position.y - t.position.y > f && (p = "swipedown"), t.position.x > g.position.x && t.position.x - g.position.x > u && (p = "swipeleft");
                    var d = Math.abs(t.position.x - g.position.x), v = Math.abs(t.position.y - g.position.y), w = {
                        startEvnt: t,
                        endEvnt: g,
                        direction: p.replace("swipe", ""),
                        xAmount: d,
                        yAmount: v,
                        duration: g.time - t.time
                    };
                    o.trigger("swipeend", w)
                }
                n = !1, i = !1
            })
        }, remove: function () {
            e(this).off(a.startevent, e(this).data.callee1).off(a.moveevent, e(this).data.callee2).off(a.endevent, e(this).data.callee3)
        }
    }, e.event.special.scrollstart = {
        setup: function () {
            var t, o, n = this, i = e(n);

            function r(e, a) {
                w(n, (t = a) ? "scrollstart" : "scrollend", e)
            }

            i.on(a.scrollevent, function e(a) {
                i.data("callee", e), t || r(a, !0), clearTimeout(o), o = setTimeout(function () {
                    r(a, !1)
                }, 50)
            })
        }, remove: function () {
            e(this).off(a.scrollevent, e(this).data.callee)
        }
    };
    var o, n, i, r, s = e(window), p = {0: !0, 180: !0};
    if (a.orientation_support) {
        var h = window.innerWidth || s.width(), c = window.innerHeight || s.height();
        i = h > c && h - c > 50, r = p[window.orientation], (i && r || !i && !r) && (p = {"-90": !0, 90: !0})
    }

    function u() {
        var e = o();
        e !== n && (n = e, s.trigger("orientationchange"))
    }

    e.event.special.orientationchange = {
        setup: function () {
            return !a.orientation_support && (n = o(), s.on("throttledresize", u), !0)
        }, teardown: function () {
            return !a.orientation_support && (s.off("throttledresize", u), !0)
        }, add: function (e) {
            var t = e.handler;
            e.handler = function (e) {
                return e.orientation = o(), t.apply(this, arguments)
            }
        }
    }, e.event.special.orientationchange.orientation = o = function () {
        var e = document.documentElement;
        return (a.orientation_support ? p[window.orientation] : e && e.clientWidth / e.clientHeight < 1.1) ? "portrait" : "landscape"
    }, e.event.special.throttledresize = {
        setup: function () {
            e(this).on("resize", d)
        }, teardown: function () {
            e(this).off("resize", d)
        }
    };
    var f, l, g, d = function () {
        l = Date.now(), (g = l - v) >= 250 ? (v = l, e(this).trigger("throttledresize")) : (f && window.clearTimeout(f), f = window.setTimeout(u, 250 - g))
    }, v = 0;

    function w(t, a, o, n) {
        var i = o.type;
        o.type = a, e.event.dispatch.call(t, o, n), o.type = i
    }

    e.each({
        scrollend: "scrollstart",
        swipeup: "swipe",
        swiperight: "swipe",
        swipedown: "swipe",
        swipeleft: "swipe",
        swipeend: "swipe",
        tap2: "tap",
        taphold2: "taphold"
    }, function (t, a) {
        e.event.special[t] = {
            setup: function () {
                e(this).on(a, e.noop)
            }
        }
    })
}(jQuery);