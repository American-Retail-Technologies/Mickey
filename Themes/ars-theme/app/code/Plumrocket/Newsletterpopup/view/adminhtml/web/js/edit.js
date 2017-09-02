/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

require([
    'jquery',
    'tinymce',
    'jquery/ui',
    'mage/adminhtml/events',
    'mage/adminhtml/browser',
    'mage/backend/tabs',
    'Plumrocket_Newsletterpopup/js/codemirror',
    'domReady!',
], function (pjQuery) {
    'use strict';

    pjQuery('#popup_success_page option[value=__none__]').attr('disabled', 'disabled');

    /*var checkPopupMethod = function()
    {
        var method = pjQuery('#popup_display_popup').val();
        var $delayTime = pjQuery("#popup_delay_time").parent().parent();
        var $pageScroll = pjQuery("#popup_page_scroll").parent().parent();
        var $cssSelector = pjQuery("#popup_css_selector").parent().parent();
        if (method == 'after_time_delay') {
            $delayTime.show();
            $pageScroll.hide();
            $cssSelector.hide();
        } else if(method == 'on_page_scroll') {
            $delayTime.hide();
            $pageScroll.show();
            $cssSelector.hide();
        } else if(method == 'on_mouseover' || method == 'on_click') {
            $delayTime.hide();
            $pageScroll.hide();
            $cssSelector.show();
        } else {
            $delayTime.hide();
            pjQuery("#popup_delay_time").val('');
            $pageScroll.hide();
            pjQuery("#popup_page_scroll").val('');
            $cssSelector.hide();
            pjQuery("#popup_css_selector").val('');
        }
    }*/

    /*var addDelimiter = function(name)
    {
        pjQuery('#note_' + name).removeClass('note').text('').css('margin-bottom', '40px');
    }*/

    var oOptions = {
        method: "POST",
        parameters: Form.serialize("edit_form"),
        asynchronous: true,
        onFailure: function (oXHR) {
            // $('loading-mask').hide();
        },  /*
        onLoading: function (oXHR) {
            $('feedback').update('Sending data ... <img src="images/loading_indicator.gif" title="Loading..." alt="Loading..." border="0" />');
        },*/
        onSuccess: function (oXHR) {
            // $('loading-mask').hide();
            var x = window.open(window.prnewsletterpopupOptions.previewUrl, '_blank');
            x.document.open();
            x.document.write(oXHR.responseText);
            x.document.close();
        }
    };

    window.previewPopup = function () {
        // we stop the default submit behaviour
        if ('tinymce' in window) {
            var obj = tinymce.get('popup_text_description');
            if (obj) {
                pjQuery('#popup_text_description').val(obj.getContent());
            }

            var obj = tinymce.get('popup_text_success');
            if (obj) {
                pjQuery('#popup_text_success').val(obj.getContent());
            }
        }

        prepareCodeAndStyle();
        var oRequest = new Ajax.Updater({success: oOptions.onSuccess.bindAsEventListener(oOptions)}, window.prnewsletterpopupOptions.previewUrl, oOptions);
    }

    window.previewTemplate = function () {
        prepareCodeAndStyle();
        var oRequest = new Ajax.Updater({success: oOptions.onSuccess.bindAsEventListener(oOptions)}, window.prnewsletterpopupOptions.previewUrl, oOptions);
    }

    var getSelectionStart = function (editor) {

        var start = 0;
        var cursor = editor.getCursor();
        var line = cursor.line;
        var offset = cursor.ch;
        var lines = editor.lineCount();
        var i = 0;
        for (i = 0; i < lines; i++) {
            if (i == line) {
                start += offset;
                return start;
            }
            start += editor.lineInfo(i).text.length + 1;
        }
        return start;
    }

    window.cmSyncSelectionByEditor = function (textarea, editor) {

        var pos = getSelectionStart(editor);
        pjQuery(textarea).attr('disabled', false);
        pjQuery(textarea).prop('selectionStart', pos);
        pjQuery(textarea).prop('selectionEnd', pos);
    }

    window.cmSyncChangesByTextarea = function (textarea, editor) {

/*console.log('cmSyncChangesByTextarea1');
        if (window.cmSyncChangesByEditorProcess) {
            return;
        }
console.log('cmSyncChangesByTextarea2');*/
        pjQuery(textarea).attr('disabled', false);
        editor.setValue(pjQuery(textarea).val());
        editor.refresh();
    }

    window.cmSyncChangesByEditor = function (textarea, editor) {

        pjQuery(textarea).attr('disabled', false);
        // window.cmSyncChangesByEditorProcess = true;
        pjQuery(textarea).val(editor.getValue());
        // window.cmSyncChangesByEditorProcess = false;
        editor.refresh();
    }

    var prepareCodeAndStyle = function () {

        pjQuery('#edit_form .base64_hidden').remove();
        pjQuery('#edit_form').append(pjQuery('<input type="hidden" class="base64_hidden"/>').attr('name','code_base64').val(Base64.encode(pjQuery('#template_code').val())));
        pjQuery('#edit_form').append(pjQuery('<input type="hidden" class="base64_hidden"/>').attr('name','style_base64').val(Base64.encode(pjQuery('#template_style').val())));
        if (pjQuery('#template_code').val() != '') {
            pjQuery('#template_code').attr('disabled', true);
        }
        if (pjQuery('#template_style').val() != '') {
            pjQuery('#template_style').attr('disabled', true);
        }
    }

    /*window.saveAndContinueEdit = function(urlTemplate)
    {
        var tabsIdValue = window.prnewsletterpopupOptions.tabsIdValue;
        var tabsBlockPrefix = window.prnewsletterpopupOptions.tabsBlockPrefix;
        if (tabsIdValue.startsWith(tabsBlockPrefix)) {
            tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length)
        }
        var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/);
        var url = template.evaluate({tab_id:tabsIdValue});
        editForm.submit(url);
    }*/

    // Initialization.
    pjQuery('#popup_coupon_code').change(function () {
        var id = pjQuery('#popup_coupon_code').val();

        if (pjQuery('#popup_start_date').val() || pjQuery('#popup_end_date').val()) {
} else {
            if (id in window.prnewsletterpopupOptions.coupons_date) {
                var dates = window.prnewsletterpopupOptions.coupons_date[id];

                pjQuery('#popup_start_date').val(dates.from_date);
                pjQuery('#popup_end_date').val(dates.to_date);
            }
        }

        if (parseInt(id) > 0) {
            pjQuery('#popup_code_container').hide();
            pjQuery('#popup_coupon_fieldset').find('input, select').removeAttr('disabled');
        } else {
            pjQuery('#popup_code_container').show();
            pjQuery('#popup_coupon_fieldset').find('input, select').attr('disabled', 'disabled');
        }
    });

    var _checkEnable = function () {
        var $chk = pjQuery(this);
        if (! $chk.is(':checked')) {
            $chk.parents('tr').addClass('not-active');
        } else {
            $chk.parents('tr').removeClass('not-active');
        }
    }

    // pjQuery('.form-list .grid table.data tbody input.checkbox').click(_checkEnable).each(_checkEnable);
    pjQuery('#popup_signup_fieldset table.data-grid tbody input.checkbox').click(_checkEnable).each(_checkEnable);
    pjQuery('#popup_mailchimp_fieldset table.data-grid tbody input.checkbox').click(_checkEnable).each(_checkEnable);

    varienGlobalEvents.attachEventHandler('formSubmit', function () {
        if (typeof codeEditor != 'undefined') {
            cmSyncChangesByEditor('#template_code', codeEditor);
        }
        if (typeof styleEditor != 'undefined') {
            cmSyncChangesByEditor('#template_style', styleEditor);
        }

        prepareCodeAndStyle();
    });

    pjQuery('#edit_tabs').on('tabscreate tabsactivate', function () {
        if (typeof codeEditor != 'undefined') {
            codeEditor.refresh();
        }
        if (typeof styleEditor != 'undefined') {
            styleEditor.refresh();
        }
    });

    /*pjQuery('#template_code,#template_style').bind('DOMSubtreeModified', function() {
console.log('DOMSubtreeModified');
        cmSyncChangesByTextarea('#template_code', codeEditor);
        cmSyncChangesByTextarea('#template_style', styleEditor);
    });*/

    // varienGlobalEvents.attachEventHandler('tinymceChange', function() {
    // pjQuery('#template_code').on('focus', function() {
    // jQuery( "body" ).on('dblclick', , function() { alert('aaaaaaa'); });
    /*pjQuery('body').on('dblclick', '[data-row=file].selected', function() {
        alert('qqq');
        cmSyncChangesByTextarea('#template_code', codeEditor);
        cmSyncChangesByTextarea('#template_style', styleEditor);
    });*/

    pjQuery('#choose_template,#template_id_picker .template-current').on('click', function (e) {
        pjQuery('#template_id_picker .template-list').toggle();
        e.stopPropagation();
        e.preventDefault();
    });

    pjQuery('#template_id_picker').on('click', 'li .list-table-td,li button.select_template', function () {
        var $el = pjQuery(this).parents('li');
        pjQuery('#template_id_picker li').removeClass('active');
        $el.addClass('active');
        pjQuery('#popup_template_id').val($el.data('id'));
        pjQuery('#template_id_picker .template-current').html($el.html());

        // pjQuery('#loading-mask').show();
        new Ajax.Request(pjQuery('#template_id_picker').data('action'), {
            method: "get",
            parameters: {'id': $el.data('id')},

            onSuccess: function successFunc(transport)
            {
                var data = transport.responseText.evalJSON();
                if (data.code || data.style) {
                    codeEditor.setValue(data.code);
                    cmSyncChangesByEditor('#template_code', codeEditor);
                    styleEditor.setValue(data.style);
                    cmSyncChangesByEditor('#template_style', styleEditor);
                }

                for (var i in data) {
                    var $editor = tinyMCE.get('popup_'+ i);
                    if ($editor != undefined) {
                        $editor.setContent(data[i]? data[i] : '');
                        continue;
                    }
                    var $field = pjQuery('#edit_tabs_labels_section_content #popup_' + i + ',#edit_tabs_display_section_content #popup_'+ i);
                    if ($field.length) {
                        $field.val(data[i]);
                    }
                }

                if (data.signup_fields) {
                    var $fieldsArea = pjQuery('#popup_signup_fieldset table.data-grid tbody tr');
                    $fieldsArea.find('input[type=checkbox][name!="signup_fields[email][enable]"]').prop('checked', false);
                    for (var field in data.signup_fields) {
                        $fieldsArea.find('input[name="signup_fields['+ field +'][enable]"]').prop('checked', data.signup_fields[field]['enable']);
                        $fieldsArea.find('input[name="signup_fields['+ field +'][label]"]').val(data.signup_fields[field]['label']);
                        $fieldsArea.find('input[name="signup_fields['+ field +'][sort_order]"]').val(data.signup_fields[field]['sort_order']);
                    }
                    // pjQuery('.form-list .grid table.data tbody input.checkbox').each(_checkEnable);
                    pjQuery('#popup_signup_fieldset table.data-grid tbody input.checkbox').each(_checkEnable);
                    // pjQuery('#popup_mailchimp_fieldset table.data-grid tbody input.checkbox').each(_checkEnable);
                }
            },
            onFailure:  function () {},
            onComplete: function () {
                pjQuery('#template_id_picker .template-list').hide();
            }
        });


        /*pjQuery.get(pjQuery('#template_id_picker').data('action'), {'id': $el.data('id')}, function(data) {
            if(data.code || data.style) {
                codeEditor.setValue(data.code);
                cmSyncChangesByEditor('#template_code', codeEditor);
                styleEditor.setValue(data.style);
                cmSyncChangesByEditor('#template_style', styleEditor);
            }

            for(var i in data) {
                var $editor = tinyMCE.get('popup_'+ i);
                if($editor != undefined) {
                    $editor.setContent(data[i]? data[i] : '');
                    continue;
                }
                var $field = pjQuery('#edit_tabs_labels_section_content #popup_'+ i);
                if($field.length) {
                    $field.val(data[i]);
                }
            }

            if(data.signup_fields) {
                var $fieldsArea = pjQuery('#popup_signup_fieldset table.data-grid tbody tr');
                $fieldsArea.find('input[type=checkbox][name!="signup_fields[email][enable]"]').prop('checked', false);
                for(var field in data.signup_fields) {
                    $fieldsArea.find('input[name="signup_fields['+ field +'][enable]"]').prop('checked', data.signup_fields[field]['enable']);
                    $fieldsArea.find('input[name="signup_fields['+ field +'][label]"]').val( data.signup_fields[field]['label'] );
                    $fieldsArea.find('input[name="signup_fields['+ field +'][sort_order]"]').val( data.signup_fields[field]['sort_order'] );
                }
                // pjQuery('.form-list .grid table.data tbody input.checkbox').each(_checkEnable);
                pjQuery('#popup_signup_fieldset table.data-grid tbody input.checkbox').each(_checkEnable);
                // pjQuery('#popup_mailchimp_fieldset table.data-grid tbody input.checkbox').each(_checkEnable);
            }

        }, 'json')
        .always(function() {
            // pjQuery('#loading-mask').hide();
            pjQuery('#template_id_picker .template-list').hide();
        });*/

    })
    .on('click', '.template-expand', function () {
        var $btn = pjQuery(this);
        var $list = $btn.parent().next('.template-wrapper').find('ul');
        var $shadow = $btn.parent().next('.template-wrapper').find('.shadow');
        $btn.toggleClass('template-minify');
        $list.toggleClass('expand-all');
        $shadow.toggleClass('shadow-hide');
    })
    // .find('li[data-id='+ pjQuery('#popup_template_id').val() +']').addClass('active').contents().clone().appendTo('.template-current');
    .find('li[data-id='+ pjQuery('#popup_template_id').val() +']').addClass('active');

    var _templateCurrentHtml = pjQuery('#template_id_picker li[data-id='+ pjQuery('#popup_template_id').val() +']').html();
    if (_templateCurrentHtml) {
        pjQuery('#template_id_picker .template-current').empty().html(_templateCurrentHtml);
    }

    pjQuery('#template_id_picker .template-list').on('click', function (e) {
        window.showTemplatePickerList = true;
    });

    pjQuery('html').on('click', function (e) {
        if (pjQuery('#template_id_picker .template-list').is(':visible') && e.target != pjQuery('#template_id_picker .template-list')[0] && pjQuery(e.target).parents('#template_id_picker .template-list')[0] != pjQuery('#template_id_picker .template-list')[0]) {
            if (!window.showTemplatePickerList) {
                pjQuery('#template_id_picker .template-list').hide();
            }
            window.showTemplatePickerList = false;
        }
    });
});
