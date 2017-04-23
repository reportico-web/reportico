/*
 * jdMenu 1.2.1 (2007-02-20)
 *
 * Copyright (c) 2006,2007 Jonathan Sharp (http://jdsharp.us)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://jdsharp.us/
 *
 * Built upon jQuery 1.1.1 (http://jquery.com)
 * This also requires the jQuery dimensions plugin
 */
(function($){
    // This method can be removed once it shows up in core
    if (!$.fn.ancestorsUntil) {
        $.fn.ancestorsUntil = function(match) {
            var a = [];
            $(this[0]).parents().each(function() {
                a.push(this);
                return !$(this).is(match);
            });
            return this.pushStack(a, arguments);
        };
    }
    
    // Settings
    var DELAY   = 150;
    var IFRAME  = false;
    if ( $.browser != undefined )
        IFRAME  = $.browser.msie;
    var CSSR    = 'jd_menu_flag_root';
    var CSSB    = 'jd_menu_hover_toolbar';
    var CSSH    = 'jd_menu_hover';
    
    // Public methods
    $.fn.jdMenu = function() {
        return this.each(function() {
            $(this).addClass(CSSR);
            addEvents(this);
        });
    };
    
    $.fn.jdMenuHide = function() {
        return this.each(function() {
            hide(this);
        });
    };
    
    // Private methods
    function addEvents(ul) {
        //$('> li', ul).hover(hoverOver,hoverOut).bind('click', click);
        $('> li', ul).hover(hoverOver,hoverOut);
    };
    
    function removeEvents(ul) {
        //$('> li', ul).unbind('mouseover').unbind('click', click);
        $('> li', ul).unbind('mouseover');
    };
    
    function hoverOver() {
        var c = $(this).parent().is('.' + CSSR) ? CSSB : CSSH;
        $(this).addClass(c).find('> a').addClass(c);
        
        if (this.$timer) {
            clearTimeout(this.$timer);
        }
        this.$size = $('> ul', this).size();
        if (this.$size > 0) {
            var ul = $('> ul', this)[0];
            if (!$(ul).is(':visible')) {
                this.$timer = setTimeout(function() {
                    if (!$(ul).is(':visible')) { 
                        show(ul); 
                    }
                }, DELAY);
            }
        }
    };
    
    function hoverOut() {
        $(this) .removeClass(CSSH).removeClass(CSSB)
            .find('> a')
                .removeClass(CSSH).removeClass(CSSB);
        
        if (this.$timer) {
            clearTimeout(this.$timer);
        }
        if ($(this).is(':visible') && this.$size > 0) {
            var ul = $('> ul', this)[0];
            this.$timer = setTimeout(function() {
                if ($(ul).is(':visible')) {
                    hide(ul);
                }
            }, DELAY);
        }
    };
    
    function show(ul) {
        // Hide any existing menues at the same level
        $(ul).parent().parent().find('> li > ul:visible').not(ul).each(function() {
            hide(this);
        });
        addEvents(ul);
        
        var o = $(ul).offset();
        var bt = o.borderTop;
        var bl = o.borderLeft;
        
        var x = 0, y = 0;
        var li = $(ul).parent();
        if ($(li).ancestorsUntil('ul.' + CSSR).filter('li').size() == 0) {
            x = $(li).offset($(li).parents('ul.' + CSSR)[0]).left;
            y = $(li).outerHeight();
        } else {
            x = $(li).outerWidth() - (3 * bl);
            y = $(li).offset($(li).parent()).top + bt;
        }
        $(ul).css({left: x + 'px', top: y + 'px'}).show();
        
        if (IFRAME && ($(ul).ancestorsUntil('ul.' + CSSR).filter('li').size() > 0)) {
            // TODO Add in the auto declaration?
            var w = $(ul).outerWidth(); // Needs to be before the frame is added
            var h = $(ul).outerHeight();
            if ($('> iframe', ul).size() == 0) {
                $(ul).append('</iframe>').prepend('<iframe style="position: absolute; z-index: -1;">');
            }
            $('> iframe', ul).css({     opacity:        '0',
                                        left:           -bl + 'px',
                                        top:            -bt + 'px',
                                        width:          w + 'px',
                                        height:         h + 'px'});
            if (!ul.style.width || ul.$auto) {
                ul.$auto = true;
                $(ul).css({ width:  w - (bl * 2) + 'px',
                            height: h - (bt * 2) + 'px',
                            zIndex: '100' });
            }
        }
    };
    
    function hide(ul, recurse) {
        $('> li > ul:visible', ul).each(function(){
            hide(this, false); 
        });
        if ($(ul).is('.' + CSSR)) {
            return;
        }
        
        removeEvents(ul);
        $(ul).hide();
        $('> iframe', ul).remove();
        
        // If true, hide all of our parent menues
        if (recurse == true) {
            $(ul).ancestorsUntil('ul.' + CSSR)
                    .removeClass(CSSH).removeClass(CSSB)
                .not('.' + CSSR).filter('ul')
                    .each(function() {
                        hide(this, false)
                });
        }
    };
        
    function click(e) {
        e.stopPropagation();
        if (this.$timer) {
            clearTimeout(this.$timer);
        }
        if (this.$size > 0) {
            var ul = $('> ul', this)[0];
            if (!$(ul).is(':visible')) {
                show(ul);
            }
        } else {
            if ($(e.target).is('li')) {
                var l = $('> a', this).get(0);
                if (l != undefined) {
                    if (!l.onclick) {
                        window.open(l.href, l.target || '_self');
                    } else {
                        $(l).click();
                    }
                }
            }
            
            var ul = $(this).parent();
            if (!$(ul).is('.' + CSSR)) {
                hide(ul, true);
            }
        }
    };
})(jQuery);

