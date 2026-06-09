import './acl';
import defaultSearchConfiguration from './default-search-configuration';

const { Module, Component } = Shopware;

Component.register('frosh-mjml-component-list', () => import('./page/list'));
Component.register(
    'frosh-mjml-component-detail',
    () => import('./page/detail')
);

Module.register('frosh-mjml-component', {
    type: 'plugin',
    name: 'frosh-mjml-component',
    title: 'frosh-mjml-component.general.title',
    description: 'frosh-mjml-component.general.description',
    color: '#ff6d1a',
    icon: 'regular-content',
    entity: 'frosh_mjml_component',

    defaultSearchConfiguration,

    routes: {
        list: {
            component: 'frosh-mjml-component-list',
            path: 'list',
            meta: {
                parentPath: 'sw.settings.index',
                privilege: 'frosh_mjml_component.viewer',
            },
        },
        detail: {
            component: 'frosh-mjml-component-detail',
            path: 'detail/:id',
            props: {
                default: (route) => ({ componentId: route.params.id }),
            },
            meta: {
                parentPath: 'frosh.mjml.component.list',
                privilege: 'frosh_mjml_component.viewer',
            },
        },
        create: {
            component: 'frosh-mjml-component-detail',
            path: 'create',
            props: {
                default: () => ({ componentId: null }),
            },
            meta: {
                parentPath: 'frosh.mjml.component.list',
                privilege: 'frosh_mjml_component.creator',
            },
        },
    },

    settingsItem: [
        {
            group: 'plugins',
            to: 'frosh.mjml.component.list',
            icon: 'regular-content',
            name: 'frosh-mjml-component',
            label: 'frosh-mjml-component.general.title',
            privilege: 'frosh_mjml_component.viewer',
        },
    ],
});
