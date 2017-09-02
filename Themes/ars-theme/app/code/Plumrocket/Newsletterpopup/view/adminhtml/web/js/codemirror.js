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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

define([
    'jquery',

    'Plumrocket_Newsletterpopup/js/codemirror/lib/codemirror',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/selection/active-line',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/edit/closebrackets',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/edit/closetag',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/edit/matchbrackets',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/edit/matchtags',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/fold/xml-fold',
    'Plumrocket_Newsletterpopup/js/codemirror/mode/xml/xml',
    'Plumrocket_Newsletterpopup/js/codemirror/mode/javascript/javascript',
    'Plumrocket_Newsletterpopup/js/codemirror/mode/css/css',
    'Plumrocket_Newsletterpopup/js/codemirror/mode/htmlmixed/htmlmixed',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/hint/show-hint',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/hint/anyword-hint',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/hint/xml-hint',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/hint/html-hint',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/mode/overlay',
    'Plumrocket_Newsletterpopup/js/codemirror/addon/display/fullscreen',

    'jquery/ui',
    'mage/adminhtml/events',
    'mage/adminhtml/browser',
    'mage/adminhtml/wysiwyg/tiny_mce/setup',
    'mage/adminhtml/wysiwyg/widget',

    'domReady!'
], function(pjQuery, CodeMirror) {
    'use strict';

    CodeMirror.commands.autocomplete = function(cm) {
        cm.showHint({hint: CodeMirror.hint.anyword});
    }

    CodeMirror.hint.anyword = function(cm) {
        var inner = {from: cm.getCursor(), to: cm.getCursor(), list: []};
        inner.list = window.prnewsletterpopupOptions.templatePlaceholders;
        return inner;
    };

    CodeMirror.defineMode('mustache', function(config, parserConfig) {
        var mustacheOverlay = {
            token: function(stream, state) {
                var ch;
                if (stream.match('{{')) {
                    while ((ch = stream.next()) != null)
                        if (ch == '}' && stream.next() == '}') {
                            stream.eat('}');
                            return 'mustache';
                        }
                }
                while (stream.next() != null && !stream.match('{{', false)) {}
                return null;
            }
        };
        return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || 'text/html'), mustacheOverlay);
    });

    window.codeEditor = CodeMirror.fromTextArea(document.getElementById('template_code'), {
        //mode: 'text/html',
        mode: 'mustache',
        theme: 'monokai',
        autoCloseBrackets: true,
        autoCloseTags: true,

        styleActiveLine: true,
        lineNumbers: true,
        lineWrapping: true,
        viewportMargin: Infinity,

        //matchTags: { bothTags: true },
        extraKeys: {
            'Ctrl-Space': 'autocomplete',
            'F1': 'autocomplete',
            'Ctrl-J': 'toMatchingTag',
            'F11': function(cm) {
                cm.setOption('fullScreen', !cm.getOption('fullScreen'));
            },
            'Esc': function(cm) {
                if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false);
            }
        },

        //value: document.documentElement.innerHTML
    });

    window.styleEditor = CodeMirror.fromTextArea(document.getElementById('template_style'), {
        mode: 'text/css',
        theme: 'monokai',
        autoCloseBrackets: true,

        styleActiveLine: true,
        lineNumbers: true,
        lineWrapping: true,
        viewportMargin: Infinity,

        //matchTags: { bothTags: true },
        extraKeys: {
            'Ctrl-Space': 'autocomplete',
            'F1': 'autocomplete',
            'Ctrl-J': 'toMatchingTag',
            'F11': function(cm) {
                cm.setOption('fullScreen', !cm.getOption('fullScreen'));
            },
            'Esc': function(cm) {
                if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false);
            }
        },

        //value: document.documentElement.innerHTML
    })

    codeEditor.on('change', function() { pjQuery('#template_code').val(codeEditor.getValue()); });
    styleEditor.on('change', function() { pjQuery('#template_style').val(styleEditor.getValue()); });

    return {
        CodeMirror: CodeMirror,
        codeEditor: codeEditor,
        styleEditor: styleEditor,
    };
});