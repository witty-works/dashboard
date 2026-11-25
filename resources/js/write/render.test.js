// @vitest-environment happy-dom
import {describe, expect, it} from 'vitest';

import {renderIssues, setEditorText, showReload, showStatus} from './render.js';

describe('renderIssues', () => {
    it('issues are text, not HTML', () => {
        const container = document.createElement('div');
        renderIssues(container, [
            {text: '<img src=x onerror=alert(1)>', category: 'gender-orientation'},
        ]);
        expect(container.querySelector('img')).toBeNull();
        expect(container.textContent).toContain('<img src=x onerror=alert(1)>');
    });

    it('lists each issue once and clears the previous list', () => {
        const container = document.createElement('div');
        renderIssues(container, [{text: 'guys'}, {text: 'chairman'}]);
        renderIssues(container, [{text: 'guys'}]);
        expect(container.querySelectorAll('li')).toHaveLength(1);
    });
});

describe('showStatus', () => {
    it('sets text, not HTML', () => {
        const element = document.createElement('p');
        showStatus(element, '<b>no</b>');
        expect(element.querySelector('b')).toBeNull();
        expect(element.textContent).toBe('<b>no</b>');
    });
});

const stubEditor = (text) => {
    const editor = {
        text,
        content: null,
        commands: {
            setContent: (doc) => {
                editor.content = doc;
            },
        },
        setEditable: (editable) => {
            editor.editable = editable;
        },
        editable: true,
    };
    return editor;
};

describe('the reload state', () => {
    it('test_refresh_failure_keeps_the_text_and_asks_to_reload', () => {
        const status = document.createElement('p');
        const editor = stubEditor('My draft');
        showReload(status, {editor}, 'Your session has ended.');
        expect(editor.content).toBeNull();
        expect(editor.text).toBe('My draft');
        expect(status.textContent).toBe('Your session has ended.');
    });
});

describe('setEditorText', () => {
    it('puts plain text in as paragraphs, never as HTML', () => {
        const editor = stubEditor('');
        setEditorText({editor}, 'First <b>line</b>\n\nSecond');
        expect(editor.content).toEqual({
            type: 'doc',
            content: [
                {type: 'paragraph', content: [{type: 'text', text: 'First <b>line</b>'}]},
                {type: 'paragraph', content: [{type: 'text', text: 'Second'}]},
            ],
        });
    });
});
