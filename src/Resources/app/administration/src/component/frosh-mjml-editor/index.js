import { formatMjml } from './format-mjml';
import template from './frosh-mjml-editor.html.twig';
import './frosh-mjml-editor.scss';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('frosh-mjml-editor', {
    template,

    props: {
        value: {
            type: String,
            required: false,
            default: '',
        },

        label: {
            type: String,
            required: false,
            default: '',
        },

        disabled: {
            type: Boolean,
            required: false,
            default: false,
        },

        fragment: {
            type: Boolean,
            required: false,
            default: false,
        },

        mailTemplateTypeId: {
            type: String,
            required: false,
            default: null,
        },

        completerFunction: {
            type: Function,
            required: false,
            default: null,
        },

        editorConfig: {
            type: Object,
            required: false,
            default: () => ({}),
        },
    },

    data() {
        return {
            previewHtml: '',
            isLoadingPreview: false,
            showPreview: true,
            componentPickerKey: 0,
            basePickerKey: 0,
        };
    },

    computed: {
        httpClient() {
            return Shopware.Application.getContainer('init').httpClient;
        },

        componentCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addFilter(
                Criteria.not('AND', [Criteria.equals('type', 'layout')])
            );
            criteria.addSorting(Criteria.sort('technicalName', 'ASC'));
            return criteria;
        },

        baseCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('type', 'layout'));
            criteria.addSorting(Criteria.sort('technicalName', 'ASC'));
            return criteria;
        },

        content: {
            get() {
                return this.value || '';
            },
            set(value) {
                this.$emit('update:value', value);
            },
        },
    },

    watch: {
        value() {
            this.schedulePreview();
        },
    },

    created() {
        this.previewTimer = null;
    },

    mounted() {
        this.schedulePreview();
    },

    beforeUnmount() {
        if (this.previewTimer) {
            clearTimeout(this.previewTimer);
        }

        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
    },

    methods: {
        aceEditor() {
            return this.$refs.codeEditor?.editor ?? null;
        },

        onEditorReady() {
            this.$nextTick(() => {
                this.aceEditor()?.resize();
            });

            if (this.resizeObserver || typeof ResizeObserver === 'undefined') {
                return;
            }

            this.resizeObserver = new ResizeObserver(() => {
                this.aceEditor()?.resize();
            });
            this.resizeObserver.observe(this.$el);
        },

        schedulePreview() {
            if (!this.showPreview) {
                return;
            }

            if (this.previewTimer) {
                clearTimeout(this.previewTimer);
            }
            this.previewTimer = setTimeout(() => this.renderPreview(), 400);
        },

        togglePreview() {
            this.showPreview = !this.showPreview;

            this.$nextTick(() => {
                this.aceEditor()?.resize();
            });

            if (this.showPreview) {
                this.schedulePreview();
            }
        },

        renderPreview() {
            const content = this.previewContent();
            if (!content.trim()) {
                this.previewHtml = '';
                return;
            }

            this.isLoadingPreview = true;
            this.httpClient
                .post(
                    '_action/frosh-mjml/preview',
                    {
                        content,
                        mailTemplateTypeId: this.mailTemplateTypeId,
                        data: this.previewData(content),
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                        },
                    }
                )
                .then((response) => {
                    this.previewHtml = response.data.html || '';
                })
                .catch(() => {
                    const message = this.$tc(
                        'frosh-mjml-component.editor.previewError'
                    );
                    this.previewHtml = `<p style="color:#c00;font-family:sans-serif;padding:16px">${message}</p>`;
                })
                .finally(() => {
                    this.isLoadingPreview = false;
                });
        },

        previewData(source) {
            if (this.mailTemplateTypeId) {
                return {};
            }

            const placeholder = 'Placeholder';
            const data = {};
            const pattern = /\{\{\s*([a-zA-Z_]\w*)/g;
            let match;
            while ((match = pattern.exec(source)) !== null) {
                data[match[1]] = placeholder;
            }
            return data;
        },

        previewContent() {
            const source = this.value || '';
            if (!this.fragment || /<mjml[\s>]/i.test(source)) {
                return source;
            }
            return `<mjml><mj-body>\n${source}\n</mj-body></mjml>`;
        },

        optionLabel(item) {
            return item.translated?.label || item.label || item.technicalName;
        },

        onPickComponent(id, item) {
            if (item) {
                this.insertComponent(item);
            }
            this.componentPickerKey += 1;
        },

        onPickBase(id, item) {
            if (item) {
                this.applyBase(item);
            }
            this.basePickerKey += 1;
        },

        insertComponent(item) {
            const componentContent =
                item.translated?.mjmlContent ?? item.mjmlContent ?? '';
            const usesContentSlot = /\{\{\s*content\b/.test(componentContent);

            const variables = this.extractComponentVars(componentContent);

            if (!usesContentSlot) {
                const argsPart = variables.length
                    ? `, { ${variables.map((name) => `${name}: ''`).join(', ')} }`
                    : '';
                this.insertSnippet(
                    `{{ mjml_component('${item.technicalName}'${argsPart}) }}`
                );
                return;
            }

            const withPart = variables.length
                ? ` with { ${variables.map((name) => `${name}: ''`).join(', ')} }`
                : '';
            const snippet = `{% mjml '${item.technicalName}'${withPart} %}\n  \n{% endmjml %}`;

            this.insertSnippet(snippet, snippet.indexOf('\n  \n') + 3);
        },

        extractComponentVars(content) {
            const variables = new Set();
            const pattern = /\{\{\s*([a-zA-Z_]\w*)/g;
            let match;
            while ((match = pattern.exec(content)) !== null) {
                if (match[1] !== 'content') {
                    variables.add(match[1]);
                }
            }
            return [...variables];
        },

        insertSnippet(snippet, cursorOffset = null) {
            const ace = this.aceEditor();
            if (!ace) {
                this.content = this.content
                    ? `${this.content}\n${snippet}`
                    : snippet;
                return;
            }

            if (cursorOffset === null) {
                ace.insert(snippet);
            } else {
                ace.insert(snippet.slice(0, cursorOffset));
                const cursor = ace.getCursorPosition();
                ace.insert(snippet.slice(cursorOffset));
                ace.moveCursorToPosition(cursor);
                ace.clearSelection();
            }

            ace.focus();
        },

        formatCode() {
            this.content = formatMjml(this.content);
            this.aceEditor()?.focus();
        },

        applyBase(layout) {
            const technicalName = layout.technicalName;
            const baseSource =
                layout.translated?.mjmlContent ?? layout.mjmlContent ?? '';
            const baseBlocks = [
                ...baseSource.matchAll(/\{%\s*block\s+([\w-]+)\s*%\}/g),
            ].map((match) => match[1]);

            const current = this.content;
            const existingBlocks = this.parseTemplateBlocks(current);
            const hasMatchingBlock = Object.keys(existingBlocks).some((name) =>
                baseBlocks.includes(name)
            );

            const leftover =
                Object.keys(existingBlocks).length === 0
                    ? this.stripDocumentWrapper(current)
                    : Object.values(existingBlocks)
                          .filter((value) => value.trim())
                          .join('\n');

            const extendsLine = `{% extends mjml_base('${technicalName}') %}`;

            let next;
            if (baseBlocks.length === 0) {
                next = `${extendsLine}\n`;
            } else {
                let seeded = false;
                const rendered = baseBlocks.map((name, index) => {
                    let content = existingBlocks[name] ?? '';
                    if (
                        !content.trim() &&
                        index === 0 &&
                        !hasMatchingBlock &&
                        !seeded &&
                        leftover.trim()
                    ) {
                        content = leftover;
                        seeded = true;
                    }
                    return `{% block ${name} %}\n${content.trim()}\n{% endblock %}`;
                });
                next = `${extendsLine}\n${rendered.join('\n')}\n`;
            }

            this.content = next;
            this.aceEditor()?.focus();
        },

        parseTemplateBlocks(source) {
            const blocks = {};
            const pattern =
                /\{%\s*block\s+([\w-]+)\s*%\}([\s\S]*?)\{%\s*endblock\s*%\}/g;
            let match;
            while ((match = pattern.exec(source)) !== null) {
                blocks[match[1]] = match[2].trim();
            }
            return blocks;
        },

        stripDocumentWrapper(source) {
            let inner = source.replace(/\{%\s*extends[^%]*%\}/g, '').trim();
            if (/<mjml[\s>]/i.test(inner)) {
                inner = inner
                    .replace(/<\/?mjml[^>]*>/gi, '')
                    .replace(/<\/?mj-body[^>]*>/gi, '')
                    .trim();
            }
            return inner;
        },
    },
});
