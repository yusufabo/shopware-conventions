import template from './conventions-product-label-list.html.twig';
import './conventions-product-label-list.scss';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            productLabels: null,
            isLoading: false,
            sortBy: 'priority',
            sortDirection: 'DESC',
            deleteId: null,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        productLabelRepository() {
            return this.repositoryFactory.create('product_label');
        },

        productLabelCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            // "name" is translated, the DAL searches it in the selected language with fallback
            if (this.term) {
                criteria.addFilter(Criteria.contains('name', this.term));
            }

            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.sortBy === 'name'));

            return criteria;
        },

        columns() {
            return [
                {
                    property: 'name',
                    label: 'conventions-product-label.list.columnName',
                    routerLink: 'conventions.product.label.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                    primary: true,
                },
                {
                    property: 'color',
                    label: 'conventions-product-label.list.columnColor',
                    allowResize: true,
                },
                {
                    property: 'priority',
                    label: 'conventions-product-label.list.columnPriority',
                    inlineEdit: 'number',
                    allowResize: true,
                    align: 'right',
                },
                {
                    property: 'active',
                    label: 'conventions-product-label.list.columnActive',
                    inlineEdit: 'boolean',
                    allowResize: true,
                    align: 'center',
                },
                {
                    property: 'validFrom',
                    label: 'conventions-product-label.list.columnValidFrom',
                    allowResize: true,
                },
                {
                    property: 'validTo',
                    label: 'conventions-product-label.list.columnValidTo',
                    allowResize: true,
                },
            ];
        },

        dateFilter() {
            return Shopware.Filter.getByName('date');
        },
    },

    methods: {
        // Called by the listing mixin on page load and whenever term, page, sorting or limit change
        async getList() {
            this.isLoading = true;

            try {
                const result = await this.productLabelRepository.search(this.productLabelCriteria);

                this.total = result.total;
                this.productLabels = result;

                return result;
            } finally {
                this.isLoading = false;
            }
        },

        onChangeLanguage() {
            this.getList();
        },

        onDelete(id) {
            this.deleteId = id;
        },

        onCloseDeleteModal() {
            this.deleteId = null;
        },

        async onConfirmDelete(id) {
            this.deleteId = null;

            try {
                await this.productLabelRepository.delete(id);
                await this.getList();
            } catch {
                this.createNotificationError({
                    message: this.$tc('global.default.error'),
                });
            }
        },
    },
};
