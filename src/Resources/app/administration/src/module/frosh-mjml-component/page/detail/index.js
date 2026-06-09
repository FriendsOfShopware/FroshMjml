import template from './template.html.twig';
import './style.scss';
import { DEFAULT_FRAGMENT } from '../../../../component/frosh-mjml-editor/defaults';

const { Component, Mixin } = Shopware;
const { mapPropertyErrors } = Component.getComponentHelper();

export default {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [Mixin.getByName('placeholder'), Mixin.getByName('notification')],

    shortcuts: {
        'SYSTEMKEY+S': {
            active() {
                return this.allowSave;
            },
            method: 'onSave',
        },
        ESCAPE: 'onCancel',
    },

    props: {
        componentId: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            isLoading: false,
            component: null,
            isSaveSuccessful: false,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },

    computed: {
        ...mapPropertyErrors('component', ['technicalName']),

        repository() {
            return this.repositoryFactory.create('frosh_mjml_component');
        },

        isNewComponent() {
            return this.componentId === null;
        },

        identifier() {
            return this.component?.technicalName ?? '';
        },

        title() {
            return this.isNewComponent
                ? this.$tc('frosh-mjml-component.detail.titleNew')
                : this.identifier;
        },

        isFragment() {
            return this.component?.type !== 'layout';
        },

        typeOptions() {
            return [
                {
                    value: 'fragment',
                    label: this.$tc('frosh-mjml-component.detail.typeFragment'),
                },
                {
                    value: 'layout',
                    label: this.$tc('frosh-mjml-component.detail.typeLayout'),
                },
            ];
        },

        allowSave() {
            return this.isNewComponent
                ? this.acl.can('frosh_mjml_component.creator')
                : this.acl.can('frosh_mjml_component.editor');
        },

        tooltipSave() {
            if (this.allowSave) {
                return {
                    message: `${this.$device.getSystemKey()} + S`,
                    appearance: 'light',
                };
            }

            return {
                message: this.$tc('sw-privileges.tooltip.warning'),
                showOnDisabledElements: true,
            };
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (this.componentId) {
                this.loadEntityData();
                return;
            }

            this.component = this.repository.create();
            this.component.type = 'fragment';
            this.component.mjmlContent = DEFAULT_FRAGMENT;
        },

        loadEntityData() {
            this.isLoading = true;

            this.repository
                .get(this.componentId)
                .then((entity) => {
                    this.component = entity;
                    this.isLoading = false;
                })
                .catch(() => {
                    this.isLoading = false;
                    this.createNotificationError({
                        message: this.$tc(
                            'global.notification.notificationLoadingDataErrorMessage'
                        ),
                    });
                });
        },

        onSave() {
            if (!this.allowSave) {
                return;
            }

            this.isLoading = true;
            this.isSaveSuccessful = false;

            this.repository
                .save(this.component)
                .then(() => {
                    this.isLoading = false;
                    this.isSaveSuccessful = true;

                    if (this.isNewComponent) {
                        this.$router.push({
                            name: 'frosh.mjml.component.detail',
                            params: { id: this.component.id },
                        });
                        return;
                    }

                    this.loadEntityData();
                })
                .catch(() => {
                    this.isLoading = false;
                    this.createNotificationError({
                        message: this.$tc(
                            'frosh-mjml-component.detail.messageSaveError'
                        ),
                    });
                });
        },

        onCancel() {
            this.$router.push({ name: 'frosh.mjml.component.list' });
        },

        abortOnLanguageChange() {
            return this.repository.hasChanges(this.component);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            if (this.componentId) {
                this.loadEntityData();
            }
        },
    },
};
