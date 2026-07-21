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

    data() {
        return {
            froshMjmlHasParentLanguage: false,
            froshMjmlInheritedEnabled: null,
        };
    },

    computed: {
        froshMjmlConfig() {
            return this.mailTemplate?.extensions?.froshMjml ?? null;
        },

        froshMjmlEnabled() {
            const config = this.froshMjmlConfig;
            if (!config) {
                return false;
            }

            if (config.enabled !== null && config.enabled !== undefined) {
                return config.enabled;
            }

            return (
                this.froshMjmlInheritedEnabled ??
                config.translated?.enabled ??
                false
            );
        },
    },

    watch: {
        mailTemplate: {
            immediate: true,
            handler() {
                if (!this.mailTemplate?.id) {
                    return;
                }

                if (!this.mailTemplate.extensions.froshMjml) {
                    const config = this.repositoryFactory
                        .create('frosh_mjml_mail_template')
                        .create(Shopware.Context.api);
                    config.mjmlContent = '';
                    this.mailTemplate.extensions.froshMjml = config;
                }

                this.loadFroshMjmlInheritance();
            },
        },

        async 'mailTemplate.extensions.froshMjml.enabled'(enabled) {
            const config = this.froshMjmlConfig;

            if (
                !enabled ||
                !config ||
                config.mjmlContent ||
                config.translated?.mjmlContent
            ) {
                return;
            }

            config.mjmlContent = await this.buildInitialMjmlContent();
        },
    },

    methods: {
        async loadFroshMjmlInheritance() {
            const { languageId, systemLanguageId } = Shopware.Context.api;
            this.froshMjmlHasParentLanguage = languageId !== systemLanguageId;

            if (!this.froshMjmlHasParentLanguage) {
                this.froshMjmlInheritedEnabled = null;
                return;
            }

            const language = await this.repositoryFactory
                .create('language')
                .get(languageId, Shopware.Context.api);

            const criteria = new Criteria(1, 1);
            criteria.addFilter(
                Criteria.equals('mailTemplateId', this.mailTemplate.id)
            );

            const inheritedLanguageContext = {
                ...Shopware.Context.api,
                languageId: language?.parentId ?? systemLanguageId,
            };

            const config = (
                await this.repositoryFactory
                    .create('frosh_mjml_mail_template')
                    .search(criteria, inheritedLanguageContext)
            ).first();

            this.froshMjmlInheritedEnabled =
                config?.translated?.enabled ?? config?.enabled ?? false;
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
