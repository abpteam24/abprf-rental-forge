//=============================================================================Load initial=================//
(function ($) {
    "use strict";
    $(document).ready(function () {
        abprf_load_datepicker();
        abprf_load_slider();
        abprf_load_tabs();
        //=============//
        $('body').find('.abprf_area[data-bg-image],.abprf_area [data-image-href]').each(function () {
            abprf_spinner($(this));
        });
        abprf_load_bg_image();
        abprf_holder_remove($('.abprf_area.abprf_holder'));
    });
    $(window).on('resize', function () {
        abprf_load_bg_image();
    });
    //======================================================================Outer Close==========//
    $(document).click(function (e) {
        let target = $(e.target);
        if (target.closest('.abp_dropdown').length === 0) {
            $('body').find('.dropdown_list').slideUp(250);
        }
    });
}(jQuery));
//=============================================================================Slider=================//
function abprf_load_slider() {
    jQuery('div.abprf_slider').each(function () {
        abprf_slider_active(jQuery(this), 1);
    });
}
function abprf_slider_active(parent, activeItem) {
    let itemLength = parent.find('.slider_item_area').first().find('[data-slide-index]').length;
    let currentItem = abprf_get_slider_item(parent, activeItem);
    let activeCurrent = parseInt(parent.find('.slider_item_area').first().find('.slider_item.rf_active').data('slide-index'));
    let i = 1;
    for (i; i <= itemLength; i++) {
        let target = parent.find('.slider_item_area').first().find('[data-slide-index="' + i + '"]').first();
        if (i < currentItem && currentItem !== 1) {
            abprf_slider_class_control(target, currentItem, activeCurrent, 'prev_slider', 'next_slider');
        }
        if (i === currentItem) {
            parent.find('.slider_item_area').first().find('[data-slide-index="' + currentItem + '"]').removeClass('prev_slider next_slider').addClass('rf_active');
        }
        if (i > currentItem && currentItem !== itemLength) {
            abprf_slider_class_control(target, currentItem, activeCurrent, 'next_slider', 'prev_slider');
        }
        if (i === itemLength && itemLength > 1) {
            if (currentItem === 1) {
                target = parent.find('.slider_item_area').first().find('[data-slide-index="' + itemLength + '"]');
                abprf_slider_class_control(target, currentItem, activeCurrent, 'prev_slider', 'next_slider');
            }
            if (currentItem === itemLength) {
                target = parent.find('.slider_item_area').first().find('[data-slide-index="1"]');
                abprf_slider_class_control(target, currentItem, activeCurrent, 'next_slider', 'prev_slider');
            }
        }
    }
}
function abprf_slider_class_control(target, currentItem, activeCurrent, add_class, remove_class) {
    if (target.hasClass('rf_active')) {
        if (currentItem > activeCurrent) {
            target.removeClass('rf_active').addClass(add_class);
        } else {
            target.removeClass('rf_active').removeClass(remove_class).addClass(add_class);
        }
    } else if (target.hasClass(remove_class)) {
        target.removeClass(remove_class).delay(600).addClass(add_class);
    } else {
        if (!target.hasClass(add_class)) {
            target.addClass(add_class);
        }
    }
}
function abprf_get_slider_item(parent, activeItem) {
    let itemLength = parent.find('.slider_item_area').first().find('[data-slide-index]').length;
    activeItem = activeItem < 1 ? itemLength : activeItem;
    activeItem = activeItem > itemLength ? 1 : activeItem;
    return activeItem;
}
(function ($) {
    "use strict";
    $(document).on('click', '.abprf_slider [data-slide-target]', function () {
        if (!$(this).hasClass('rf_active')) {
            let activeItem = $(this).data('slide-target');
            let parent = $(this).closest('.abprf_slider');
            abprf_slider_active(parent, activeItem);
            parent.find('[data-slide-target]').removeClass('rf_active');
            $(this).addClass('rf_active');
        }
    });
    $(document).on('click', '.abprf_slider .icon_direction', function () {
        let parent = $(this).closest('.abprf_slider');
        let activeItem = parseInt(parent.find('.slider_item_area').first().find('.slider_item.rf_active').data('slide-index'));
        if ($(this).hasClass('next_item')) {
            ++activeItem;
        } else {
            --activeItem;
        }
        abprf_slider_active(parent, activeItem);
    });
    $(document).on('click', '.abprf_slider [data-target-popup]', function () {
        let target = $(this).data('target-popup');
        let activeItem = $(this).data('slide-index');
        $('body').addClass('_stop_scroll').find('[data-popup="' + target + '"]').addClass('in').promise().done(function () {
            abprf_slider_active($(this), activeItem);
            abprf_load_bg_image();
        });
    });
    $(document).on('click', '.abprf_slider .popup_close', function () {
        $(this).closest('[data-popup]').removeClass('in');
        $('body').removeClass('_stop_scroll');
    });
}(jQuery));
//=============================================================================Change icon and text=================//
function abprf_data_change($this) {
    abprf_load_bg_image();
    abprf_class_change($this);
    abprf_icon_change($this);
    abprf_text_change($this);
    abprf_input_value_change($this);
}
function abprf_icon_change(currentTarget) {
    let openIcon = currentTarget.data('open-icon');
    let closeIcon = currentTarget.data('close-icon');
    if (openIcon || closeIcon) {
        if (currentTarget.hasClass('rf_active')) {
            currentTarget.find('[data-icon]').removeClass(closeIcon).addClass(openIcon);
        } else {
            currentTarget.find('[data-icon]').removeClass(openIcon).addClass(closeIcon);
        }
        // currentTarget.find('[data-icon]').toggleClass(closeIcon).toggleClass(openIcon);
    }
}
function abprf_text_change(currentTarget) {
    let openText = currentTarget.data('open-text');
    openText = openText ? openText.toString() : '';
    let closeText = currentTarget.data('close-text');
    closeText = closeText ? closeText : '';
    if (openText || closeText) {
        let text = currentTarget.find('[data-text]').html();
        text = text ? text.toString() : ''
        if (text !== openText) {
            currentTarget.find('[data-text]').html(openText);
        } else {
            currentTarget.find('[data-text]').html(closeText);
        }
    }
}
function abprf_class_change(currentTarget) {
    let clsName = currentTarget.data('add-class');
    if (clsName) {
        if (currentTarget.find('[data-class]').length > 0) {
            currentTarget.find('[data-class]').toggleClass(clsName);
        } else {
            currentTarget.toggleClass(clsName);
        }
    }
}
function abprf_input_value_change(currentTarget) {
    currentTarget.find('[data-value]').each(function () {
        let value = jQuery(this).val();
        if (value) {
            jQuery(this).val('');
        } else {
            jQuery(this).val(jQuery(this).data('value'));
        }
        jQuery(this).trigger('change');
    });
}
(function ($) {
    "use strict";
    $(document).on('click', 'div.abprf_area .load_more [data-read]', function (e) {
        e.stopPropagation();
        let parent = $(this).closest('.load_more');
        parent.find('[data-content]').toggleClass('_d_none')
    });
    $(document).on('click', '.abprf_area [data-all-change]', function () {
        abprf_data_change($(this));
    });
    $(document).on('click', '.abprf_area [data-icon-change]', function () {
        abprf_icon_change($(this));
    });
    $(document).on('click', '.abprf_area [data-text-change]', function () {
        abprf_text_change($(this));
    });
    $(document).on('click', '.abprf_area [data-class-change]', function () {
        abprf_class_change($(this));
    });
    $(document).on('click', '.abprf_area [data-value-change]', function () {
        abprf_input_value_change($(this));
    });
    $(document).on('keyup change', '.abprf_area [data-input-text]', function () {
        let input_value = $(this).val();
        let input_id = $(this).attr('data-input-text');
        $(".abprf_area [data-input-change='" + input_id + "']").each(function () {
            $(this).html(input_value);
        });
    });
    $(document).on('keyup change', '.abprf_area [data-target-same-input]', function () {
        let input_value = $(this).val();
        let input_id = $(this).data('target-same-input');
        $(".abprf_area [data-same-input='" + input_id + "']").each(function () {
            $(this).val(input_value);
        });
    });
    $(document).on('click', '.abprf_area [data-href]', function () {
        let href = $(this).data('href');
        if (href) {
            window.location.href = href;
        }
    });
    $(document).on('click', '.abprf_area .date_close_icon', function (e) {
        e.preventDefault();
        let parent = $(this).closest('label');
        parent.find('input[type="text"]').datepicker("setDate", '');
        parent.find('input[type="hidden"]').val('').trigger('change');
    });
    $(document).on('click', '.abprf_area .time_close_icon', function (e) {
        e.preventDefault();
        let parent = $(this).closest('label');
        parent.find('input[type="time"]').val('').trigger('rf_trigger');
    });
}(jQuery));
//==============================================================================Collapse & Tabs & Modal / Popup=================//
function abprf_load_tabs() {
    jQuery('div.abprf_area .abprf_tabs').each(function () {
        let tab_lists = jQuery(this).find('.tab_lists:first');
        let activeTab = tab_lists.find('[data-tabs-target].rf_active');
        let targetTab = activeTab.length > 0 ? activeTab : tab_lists.find('[data-tabs-target]').first();
        targetTab.trigger('click');
    });
}
function abprf_target_close(close_id) {
    jQuery('body').find('[data-close="' + close_id + '"]').slideUp(250);
    return true;
}
function abprf_target_open(close_id) {
    jQuery('body').find('[data-close="' + close_id + '"]').slideDown(250);
    return true;
}
function abprf_popup_close() {
    jQuery('body').find('.popup_close').trigger('click');
}
(function ($) {
    "use strict";
    $(document).on('click', 'div.abprf_area [data-tabs-target]', function () {
        if (!$(this).hasClass('rf_active')) {
            let tabsTarget = $(this).data('tabs-target');
            let parent = $(this).closest('.abprf_tabs');
            parent.height(parent.height());
            let tab_lists = $(this).closest('.tab_lists');
            let tab_content = parent.find('.tab_content:first');
            tab_lists.find('[data-tabs-target].rf_active').each(function () {
                $(this).removeClass('rf_active').promise().done(function () {
                    abprf_data_change($(this))
                });
            });
            $(this).addClass('rf_active').promise().done(function () {
                abprf_data_change($(this))
            });
            tab_content.children('[data-tabs="' + tabsTarget + '"]').slideDown(350);
            tab_content.children('[data-tabs].rf_active').slideUp(350).removeClass('rf_active').promise().done(function () {
                tab_content.children('[data-tabs="' + tabsTarget + '"]').addClass('rf_active').promise().done(function () {
                    abprf_load_bg_image();
                    parent.height('auto');
                });
            });
        }
    });
    //================//
    $(document).on('click', 'div.abprf_area [data-target-popup]', function () {
        let $this = $(this);
        let target = $this.attr('data-active-popup', '').data('target-popup');
        $('body').addClass('_stop_scroll').find('[data-popup="' + target + '"]').addClass('in').promise().done(function () {
            abprf_load_bg_image();
            $this.trigger('rf_trigger');
            return true;
        });
    });
    $(document).on('click', 'div.abprf_popup  .popup_close', function () {
        $(this).closest('[data-popup]').removeClass('in');
        $('body').removeClass('_stop_scroll').find('[data-active-popup]').removeAttr('data-active-popup');
        return true;
    });
    //================//
    $(document).on('click', 'div.abprf_area [data-collapse-target]', function () {
        let currentTarget = $(this);
        let target_id = currentTarget.attr('data-collapse-target');
        let close_id = currentTarget.attr('data-close-target');
        let target = $('[data-collapse="' + target_id + '"]');
        if (target_close(close_id, target_id) && collapse_close_inside(currentTarget) && target_collapse(target, currentTarget)) {
            abprf_data_change(currentTarget);
        }
    });
    $(document).on('change', '.abprf_area select[data-collapse-target]', function () {
        let currentTarget = $(this);
        let value = currentTarget.val();
        currentTarget.find('option').each(function () {
            if ($(this).attr('data-option-target-multi')) {
                let target_ids = $(this).data('option-target-multi');
                target_ids = target_ids.toString().split(" ");
                target_ids.forEach(function (target_id) {
                    let target = get_collapse_target(currentTarget, target_id);
                    target.slideUp(350).removeClass('rf_active');
                });
            } else {
                let target = get_collapse_target($(this));
                target.slideUp('fast').removeClass('rf_active');
            }
        }).promise().done(function () {
            currentTarget.find('option').each(function () {
                let current_value = $(this).val();
                if (current_value === value) {
                    if ($(this).attr('data-option-target-multi')) {
                        let target_ids = $(this).data('option-target-multi');
                        target_ids = target_ids.toString().split(" ");
                        target_ids.forEach(function (target_id) {
                            let target = get_collapse_target(currentTarget, target_id);
                            target.slideDown(350).removeClass('rf_active');
                        });
                    } else {
                        let target = get_collapse_target($(this));
                        target.slideDown(350).removeClass('rf_active');
                    }
                }
            });
        });
    });
    function get_collapse_target(current, id = '') {
        let target_id = id !== '' ? id : current.attr('data-option-target');
        if (current.closest('.data_single_collapse').length > 0) {
            return current.closest('.data_single_collapse').find('[data-collapse="' + target_id + '"]');
        } else {
            return $('[data-collapse="' + target_id + '"]');
        }
    }
    function target_close(close_id, target_id) {
        $('body').find('[data-close="' + close_id + '"]:not([data-collapse="' + target_id + '"])').slideUp(250);
        return true;
    }
    function target_collapse(target, $this) {
        if ($this.is('[type="radio"]')) {
            target.slideDown(250);
        } else {
            target.each(function () {
                $(this).stop(true, true).slideToggle(250, function () {
                    $(this).toggleClass('rf_active');
                });
            });
        }
        return true;
    }
    function collapse_close_inside(currentTarget) {
        let parent_target_close = currentTarget.data('collapse-close-inside');
        if (parent_target_close) {
            $(parent_target_close).find('[data-collapse]').each(function () {
                if ($(this).hasClass('rf_active')) {
                    let collapse_id = $(this).data('collapse');
                    let target_collapse = $('[data-collapse-target="' + collapse_id + '"]');
                    if (collapse_id !== currentTarget.data('collapse-target')) {
                        $(this).slideUp(250).removeClass('rf_active');
                        let clsName = target_collapse.data('add-class');
                        if (clsName) {
                            target_collapse.removeClass(clsName);
                        }
                        abprf_text_change(target_collapse);
                        abprf_icon_change(target_collapse);
                    }
                }
            })
        }
        return true;
    }
}(jQuery));
//==============================================================================Form section ==============//
(function ($) {
    "use strict";
    //==============================================================================Qty inc dec================//
    $(document).on("click", "div.abprf_area .qty_decrease ,div.abprf_area .qty_increase", function () {
        let current = $(this);
        let target = current.closest('.qty_input').find('input');
        let currentValue = parseInt(target.val());
        let value = current.hasClass('qty_increase') ? (currentValue + 1) : ((currentValue - 1) > 0 ? (currentValue - 1) : 0);
        let min = parseInt(target.attr('data-min'));
        let max = parseInt(target.attr('data-max'));
        target.parents('.qty_input').find('.qty_increase , .qty_decrease').removeClass('_disabled');
        if (value < min || isNaN(value) || value === 0) {
            value = min;
            target.parents('.qty_input').find('.qty_decrease').addClass('_disabled');
        }
        if (value > max) {
            value = max;
            target.parents('.qty_input').find('.qty_increase').addClass('_disabled');
        }
        target.val(value).trigger('change').trigger('input');
    });
    //=======================================================Group checkbox ==============//
    $(document).on('click', 'div.abprf_area .custom_checkbox [data-checked]', function () {
        let $this = $(this);
        $this.toggleClass('rf_active').promise().done(function () {
            let parent = $(this).closest('.custom_checkbox');
            let value = '';
            let separator = ',';
            parent.find(' [data-checked]').each(function () {
                if ($(this).hasClass('rf_active')) {
                    let currentValue = $(this).attr('data-checked');
                    value = value + (value ? separator : '') + currentValue;
                }
            }).promise().done(function () {
                abprf_data_change($this);
                parent.find('input[type="hidden"]').val(value).trigger('rf_trigger');
            });
        });
    });
    //======================================================= radio========================//
    $(document).on('click', 'div.abprf_area  .custom_radio [data-radio]', function () {
        let parent = $(this).closest('.custom_radio');
        let $this = $(this);
        if (!$this.hasClass('rf_active')) {
            let value = $this.attr('data-radio');
            parent.find('.rf_active[data-radio]').each(function () {
                if ($(this).attr('data-close-target')) {
                    let close_id = $(this).attr('data-close-target');
                    abprf_target_close(close_id);
                }
                $(this).removeClass('rf_active');
                abprf_data_change($(this));
            }).promise().done(function () {
                if ($this.attr('data-close-target')) {
                    let close_id = $this.attr('data-close-target');
                    abprf_target_open(close_id);
                }
                $this.addClass('rf_active');
                abprf_data_change($this);
                parent.find('input[type="hidden"]').val(value).trigger('rf_trigger');
            });
        }
    });
    //=======================================================Switch button ==============//
    $(document).on('click', 'div.abprf_area  [data-switch]', function () {
        if ($(this).hasClass('rf_active')) {
            $(this).removeClass('rf_active').find('input[type="hidden"]').val('off').trigger('rf_trigger');
        } else {
            $(this).addClass('rf_active').find('input[type="hidden"]').val('on').trigger('rf_trigger');
        }
    });
    //=======================================================validation ==============//
    $(document).on('keyup change', 'div.abprf_area .validation_number', function () {
        let value = $(this).val();
        value = parseInt(value.replace(/\D/g, ''));
        if ($(this).attr('data-min') || $(this).attr('data-max')) {
            let min = parseInt($(this).attr('data-min'));
            let max = parseInt($(this).attr('data-max'));
            if ((min && value < min) || isNaN(value)) {
                value = min;
            }
            if (max && value > max) {
                value = max;
            }
        }
        $(this).val(value);
        return true;
    });
    $(document).on('keyup change', 'div.abprf_area .validation_price', function () {
        let n = $(this).val();
        $(this).val(n.replace(/[^\d.]/g, ''));
        return true;
    });
    $(document).on('keyup change', 'div.abprf_area .validation_id', function () {
        let n = $(this).val();
        $(this).val(n.replace(/[^\d_a-zA-Z]/g, ''));
        return true;
    });
    $(document).on('keyup change', 'div.abprf_area .validation_name', function () {
        let n = $(this).val();
        $(this).val(n.replace(/[@%'":;&_]/g, ''));
        return true;
    });
    $(document).on('keyup change', 'div.abprf_area [required]', function () {
        abprf_required($(this));
    });
    function abprf_required(input) {
        if (input.val() !== '') {
            input.removeClass('abprf_required');
            return true;
        } else {
            input.addClass('abprf_required');
            return false;
        }
    }
    //==============================================================================custom select ================//
    $(document).on("click", "div.abprf_area .abp_dropdown .dropdown_list li", function (e) {
        e.preventDefault();
        let current = $(this);
        let parent = $(this).closest('.abp_dropdown');
        let value = current.attr('data-value');
        let text = current.attr('data-text');
        parent.find('.dropdown_list').slideUp(250);
        parent.find('input[type="text"]').val(text);
        parent.find('input[type="hidden"]').val(value).trigger('rf_trigger');
    });
    $(document).on({
        keyup: function () {
            let input = $(this).val().toLowerCase();
            $(this).closest('.abp_dropdown').find('.dropdown_list li').each(function () {
                $(this).toggle($(this).attr('data-text').toLowerCase().indexOf(input) > -1);
            });
            $(this).closest('.abp_dropdown').find('.dropdown_list').slideDown(200);
        }, click: function () {
            let $this = $(this);
            let input = '';
            let target = $(this).closest('.abp_dropdown').find('.dropdown_list ');
            if (target.is(':visible')) {
                $('body').find('.abp_dropdown .dropdown_list').slideUp(250);
                let parent = $this.closest('.abp_dropdown');
                input = parent.find('input[type="text"]').val().toLowerCase();
            } else {
                $('body').find('.abp_dropdown .dropdown_list').slideUp(250);
                target.slideDown(250);
            }
            target.find('li').each(function () {
                let data = $(this).attr('data-text').toLowerCase();
                if (!input || input === data) {
                    $(this).slideDown('fast');
                }
            });
        }, blur: function (e) {
            let target = $(e.relatedTarget);
            let $this = $(this);
            let parent = $this.closest('.abp_dropdown');
            setTimeout(function () {
                if (target.closest('.abp_dropdown').length === 0) {
                    $('body').find('.dropdown_list').slideUp(250);
                    if ($this.hasClass('abprf_allow')) {
                        parent.find('input[type="hidden"]').val($this.val());
                        parent.find('input[type="text"]').val($this.val());
                    } else {
                        if (target.closest('.abp_dropdown').length === 0) {
                            let current_val = parent.find('input[type="text"]').val().toLowerCase();
                            let input = parent.find('input[type="hidden"]').val();
                            let exit = 0;
                            $this.closest('.abp_dropdown').find('.dropdown_list li').each(function () {
                                let data_value = $(this).attr('data-value');
                                let data_text = $(this).attr('data-text').toLowerCase();
                                if (input === data_value && current_val === data_text) {
                                    exit = 1;
                                }
                            }).promise().done(function () {
                                if (exit < 1) {
                                    parent.find('input[type="text"]').val('');
                                    parent.find('input[type="hidden"]').val('');
                                }
                            });
                        }
                    }
                }
            }, 200);
        }
    }, 'div.abprf_area .abp_dropdown input[type="text"]');
}(jQuery));
//================================================================================pagination=================//
(function ($) {
    "use strict";
    $(document).on('click', 'div.pagination_area  .live_pagination', function () {
        let pagination_page = parseInt($(this).attr('data-load-more')) + 1;
        let parent = $(this).closest('div.pagination_content_area');
        let page_item = parseInt(parent.find('input[name="page_item"]').val());
        let count = 0;
        let end_item = page_item * pagination_page + page_item;
        $(this).attr('data-load-more', pagination_page).promise().done(function () {
            console.log(pagination_page);
            console.log(page_item);
            console.log(end_item);
            parent.find('.pagination_item').each(function () {
                console.log(count);
                if (count < end_item) {
                    $(this).slideDown(250);
                }
                count++;
            });
        }).promise().done(function () {
            pagination_item(parent);
        }).promise().done(function () {
            abprf_load_bg_image();
        });
    });
    function pagination_item(parent) {
        if (parent.find('.pagination_item:hidden').length === 0) {
            parent.find('[data-load-more]').attr('disabled', 'disabled');
        } else {
            parent.find('[data-load-more]').removeAttr('disabled');
        }
    }
}(jQuery));
//================================================================================Load Bg Image=================//
function abprf_load_bg_image(body = jQuery('body')) {
    body.find('.abprf_area [data-bg-image]:visible').each(function () {
        let target = jQuery(this);
        let bg_url = target.data('bg-image');
        if (!bg_url || bg_url.width === 0 || bg_url.width === 'undefined') {
            bg_url = abprf_var.blank_image;
        }
        abprf_bg_img_resize(target, bg_url);
        target.css('background-image', 'url("' + bg_url + '")').promise().done(function () {
            abprf_spinner_remove(jQuery(this));
        });
    });
    body.find('.abprf_area [data-image-href]:visible').each(function () {
        let target = jQuery(this);
        let bg_url = target.data('image-href');
        target.attr('data-image-href', '');
        if (!bg_url || bg_url.width === 0 || bg_url.width === 'undefined') {
            bg_url = abprf_var.blank_image;
        }
        if (bg_url) {
            target.find('img').attr('src', bg_url).promise().done(function () {
                abprf_spinner_remove(target);
            });
        }
    });
    return true;
}
function abprf_bg_img_resize(target, bg_url) {
    let tmpImg = new Image();
    tmpImg.src = bg_url;
    jQuery(tmpImg).one('load', function () {
        let width = target.outerWidth();
        let imgWidth = tmpImg.width;
        let imgHeight = tmpImg.height;
        let height = imgHeight * (width / imgWidth);
        target.css({"min-height": height});
    });
}
//=================================================================================Date picker & Sticky & Price Format & Page Scroll==============//
function abprf_load_datepicker(parent = jQuery('.abprf_area')) {
    parent.find(".abp_datepicker.hasDatepicker").each(function () {
        jQuery(this).removeClass('hasDatepicker').attr('id', '').removeData('datepicker').unbind();
    }).promise().done(function () {
        parent.find(".abp_datepicker").datepicker({
            dateFormat: abprf_var.date_format, autoSize: true, changeMonth: true, changeYear: true, //showButtonPanel: true,
            onSelect: function (dateString, data) {
                let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
            }
        });
    });
}
function abprf_alert($this, attr = 'alert') {
    alert($this.data(attr));
}
function abprf_page_scroll(target) {
    jQuery('html, body').animate({
        scrollTop: target.offset().top -= 150
    }, 1000);
}
function abprf_toast_msg(msg, type = 'info') {
    const icons = {success: '✅', error: '❌', warn: '⚠️', info: 'ℹ️'};
    const el = jQuery(`<div class="toast_msg_box ${type}"><span>${icons[type] || 'ℹ️'}</span><span>${msg}</span></div>`);
    jQuery('div.abprf_area .toast_msg_area').append(el);
    setTimeout(() => el.fadeOut(300, () => el.remove()), 3400);
}
function abprf_wc_price_format(price) {
    if (typeof price === 'string') {
        price = Number(price);
    }
    price = price.toFixed(abprf_var.decimal_num);
    let total_part = price.toString().split(".");
    total_part[0] = total_part[0].replace(/\B(?=(\d{3})+(?!\d))/g, abprf_var.thousands_separator);
    price = total_part.join(abprf_var.currency_decimal);
    let price_text = '';
    if (abprf_var.currency_position === 'right') {
        price_text = price + abprf_var.currency_symbol;
    } else if (abprf_var.currency_position === 'right_space') {
        price_text = price + '&nbsp;' + abprf_var.currency_symbol;
    } else if (abprf_var.currency_position === 'left') {
        price_text = abprf_var.currency_symbol + price;
    } else {
        price_text = abprf_var.currency_symbol + '&nbsp;' + price;
    }
    if (abprf_var.currency_suffix) {
        price_text = price + '&nbsp;' + abprf_var.currency_suffix;
    }
    return price_text;
}
//======================================================================================Loader==============//
function abprf_holder(target) {
    target.addClass('abprf_holder');
}
function abprf_holder_remove(target) {
    target.each(function () {
        target.removeClass('abprf_holder');
    })
}
function abprf_spinner(target) {
    if (target.find('.abprf_spinner').length < 1) {
        target.addClass('_p_relative').append('<div class="abprf_spinner"></div>');
    }
}
function abprf_spinner_remove(target = jQuery('body')) {
    target.removeClass('_p_relative').find('.abprf_spinner').remove();
}