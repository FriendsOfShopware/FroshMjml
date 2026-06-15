import template from './sw-mail-template-detail.html.twig';
import './sw-mail-template-detail.scss';
import {
    DEFAULT_TEMPLATE,
    extendsLayoutTemplate,
} from '../../component/frosh-mjml-editor/defaults';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-mail-template-detail', {
    template,

    watch: {
        'mailTemplate.id': {
            immediate: true,
            handler() {
                if (
                    this.mailTemplate &&
                    !this.mailTemplate.extensions.froshMjml
                ) {
                    const config = this.repositoryFactory
                        .create('frosh_mjml_mail_template')
                        .create(Shopware.Context.api);
                    config.enabled = false;
                    config.mjmlContent = '';
                    this.mailTemplate.extensions.froshMjml = config;
                }
            },
        },

        async 'mailTemplate.extensions.froshMjml.enabled'(enabled) {
            const config = this.mailTemplate?.extensions?.froshMjml;

            if (!enabled || !config || config.mjmlContent) {
                return;
            }

            config.mjmlContent = await this.buildInitialMjmlContent();
        },
    },

    methods: {
        onMjmlToggle(value) {
            const config = this.mailTemplate?.extensions?.froshMjml;
            if (config) {
                config.enabled = value;
            }
        },

        async buildInitialMjmlContent() {
            const layout = await this.findPreferredLayout();

            return layout
                ? extendsLayoutTemplate(layout.technicalName)
                : DEFAULT_TEMPLATE;
        },

        async findPreferredLayout() {
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('type', 'layout'));

            const layouts = await this.repositoryFactory
                .create('frosh_mjml_component')
                .search(criteria, Shopware.Context.api);

            if (layouts.total === 0) {
                return null;
            }

            return (
                layouts.find((layout) => layout.technicalName === 'default') ??
                layouts.first()
            );
        },
    },
});
