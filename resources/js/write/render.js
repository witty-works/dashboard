// Everything the page shows from the API or the user goes in as text: the
// issues come from the API, the rewrite from a language model.

/** The issues Witty found in a rewrite, as a list of their texts. */
export function renderIssues(container, results) {
    const items = (results ?? []).map((result) => {
        const item = document.createElement('li');
        item.textContent = result.text;
        return item;
    });
    if (items.length === 0) {
        container.replaceChildren();
        return;
    }
    const list = document.createElement('ul');
    list.replaceChildren(...items);
    container.replaceChildren(list);
}

export function showStatus(element, text) {
    element.textContent = text;
}

/**
 * The session has ended: the text stays in the editor, so the user can copy
 * it, and the page asks for a reload.
 */
export function showReload(status, handle, message) {
    handle?.editor?.setEditable?.(false);
    showStatus(status, message);
}

/** Replace the editor's text with plain text, paragraph by paragraph. */
export function setEditorText(handle, text) {
    const paragraphs = text.split(/\n{2,}|\n/).filter((line, index, all) => line !== '' || all.length === 1);
    handle.editor.commands.setContent({
        type: 'doc',
        content: paragraphs.map((paragraph) => ({
            type: 'paragraph',
            content: paragraph ? [{type: 'text', text: paragraph}] : [],
        })),
    });
}
