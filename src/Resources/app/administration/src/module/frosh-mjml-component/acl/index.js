Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: null,
    key: 'frosh_mjml_component',
    roles: {
        viewer: {
            privileges: [
                'frosh_mjml_component:read',
                'frosh_mjml_component_translation:read',
            ],
            dependencies: [],
        },
        editor: {
            privileges: [
                'frosh_mjml_component:update',
                'frosh_mjml_component_translation:read',
                'frosh_mjml_component_translation:create',
                'frosh_mjml_component_translation:update',
                'frosh_mjml_component_translation:delete',
            ],
            dependencies: ['frosh_mjml_component.viewer'],
        },
        creator: {
            privileges: [
                'frosh_mjml_component:create',
                'frosh_mjml_component_translation:create',
            ],
            dependencies: [
                'frosh_mjml_component.viewer',
                'frosh_mjml_component.editor',
            ],
        },
        deleter: {
            privileges: [
                'frosh_mjml_component:delete',
                'frosh_mjml_component_translation:delete',
            ],
            dependencies: ['frosh_mjml_component.viewer'],
        },
    },
});
