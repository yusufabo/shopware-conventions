import template from './conventions-product-label-detail.html.twig';

const { Mixin } = Shopware;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder'),
    ],

    data() {
        return {
            productLabel: null,
            isLoading: false,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },

    computed: {
        productLabelRepository() {
            return this.repositoryFactory.create('product_label');
        },

        identifier() {
            return this.placeholder(this.productLabel, 'name');
        },

        // productLabelNameError, productLabelColorError, ... are filled from the API
        // validation errors (DAL "Required" flags and ProductLabelValidator)
        ...mapPropertyErrors('productLabel', [
            'name',
            'color',
            'priority',
            'validFrom',
            'validTo',
        ]),
    },

    watch: {
        '$route.params.id'() {
            this.loadEntityData();
        },
    },

    created() {
        this.loadEntityData();
    },

    methods: {
        async loadEntityData() {
            this.isLoading = true;

            try {
                this.productLabel = await this.productLabelRepository.get(this.$route.params.id);
            } finally {
                this.isLoading = false;
            }
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        abortOnLanguageChange() {
            return this.productLabelRepository.hasChanges(this.productLabel);
        },

        onChangeLanguage() {
            this.loadEntityData();
        },

        async onSave() {
            this.isLoading = true;

            try {
                await this.productLabelRepository.save(this.productLabel);

                this.createNotificationSuccess({
                    message: this.$tc('conventions-product-label.detail.messageSaveSuccess'),
                });

                await this.loadEntityData();
            } catch (error) {
                // Field errors are put into the error store by the repository,
                // which feeds sw-error-summary and the :error props of the fields
                this.createNotificationError({
                    message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'),
                });

                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        onClickSave() {
            // Errors are already shown, avoid an unhandled promise rejection
            this.onSave().catch(() => {});
        },

        onCancel() {
            this.$router.push({ name: 'conventions.product.label.list' });
        },
    },
};
