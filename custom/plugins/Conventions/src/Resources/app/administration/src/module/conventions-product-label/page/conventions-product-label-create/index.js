import template from './conventions-product-label-create.html.twig';

export default {
    template,

    methods: {
        loadEntityData() {
            // New entities are always created in the system language, like in core modules
            const context = Shopware.Context.api;
            if (context.languageId !== context.systemLanguageId) {
                context.languageId = context.systemLanguageId;
            }

            this.productLabel = this.productLabelRepository.create();
            this.productLabel.color = '#FF0000';
            this.productLabel.priority = 0;
            this.productLabel.active = true;

            return Promise.resolve();
        },

        async onSave() {
            this.isLoading = true;

            try {
                await this.productLabelRepository.save(this.productLabel);

                this.createNotificationSuccess({
                    message: this.$tc('conventions-product-label.detail.messageSaveSuccess'),
                });

                this.$router.push({
                    name: 'conventions.product.label.detail',
                    params: { id: this.productLabel.id },
                });
            } catch (error) {
                this.createNotificationError({
                    message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'),
                });

                throw error;
            } finally {
                this.isLoading = false;
            }
        },
    },
};
