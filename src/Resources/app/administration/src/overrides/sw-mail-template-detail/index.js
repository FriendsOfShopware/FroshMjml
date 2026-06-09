import template from './sw-mail-template-detail.html.twig';
import './sw-mail-template-detail.scss';
import { DEFAULT_TEMPLATE } from '../../component/frosh-mjml-editor/defaults';

const { Component } = Shopware;

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

        'mailTemplate.extensions.froshMjml.enabled'(enabled) {
            const config = this.mailTemplate?.extensions?.froshMjml;

            if (enabled && config && !config.mjmlContent) {
                config.mjmlContent = DEFAULT_TEMPLATE;
            }
        },
    },
});
