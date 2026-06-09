import template from './template.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export default {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [Mixin.getByName('listing'), Mixin.getByName('notification')],

    data() {
        return {
            isLoading: false,
            items: null,
            total: 0,
            sortBy: 'technicalName',
            showDeleteModal: false,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('frosh_mjml_component');
        },

        columns() {
            return [
                {
                    property: 'technicalName',
                    dataIndex: 'technicalName',
                    label: 'frosh-mjml-component.list.columnTechnicalName',
                    routerLink: 'frosh.mjml.component.detail',
                    primary: true,
                },
                {
                    property: 'label',
                    dataIndex: 'label',
                    label: 'frosh-mjml-component.list.columnLabel',
                    sortable: false,
                },
                {
                    property: 'type',
                    dataIndex: 'type',
                    label: 'frosh-mjml-component.list.columnType',
                },
            ];
        },

        criteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);

            this.sortBy.split(',').forEach((sorting) => {
                criteria.addSorting(
                    Criteria.sort(
                        sorting,
                        this.sortDirection,
                        this.naturalSorting
                    )
                );
            });

            return criteria;
        },
    },

    methods: {
        async getList() {
            this.isLoading = true;

            try {
                const response = await this.repository.search(this.criteria);

                this.total = response.total;
                this.items = response;
            } finally {
                this.isLoading = false;
            }
        },

        updateTotal({ total }) {
            this.total = total;
        },

        onDelete(id) {
            this.showDeleteModal = id;
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onConfirmDelete(id) {
            this.showDeleteModal = false;

            return this.repository.delete(id).then(() => {
                this.getList();
            });
        },
    },
};
