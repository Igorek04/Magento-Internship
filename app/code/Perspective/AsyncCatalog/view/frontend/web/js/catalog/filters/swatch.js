define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogFilterSwatches', (root, config) => ({

        swatchConfig: config.swatchConfig || {},

        init() {

        },

        isSwatchAttribute(attributeCode) {
            return !!this.swatchConfig[attributeCode];
        },

        getSwatchData(attributeCode, optionValue) {
            if (!this.swatchConfig[attributeCode]) return null;

            const options = this.swatchConfig[attributeCode].options;
            return options[optionValue] || null;
        },

        getSwatchStyle(attributeCode, optionValue) {
            const data = this.getSwatchData(attributeCode, optionValue);
            if (!data) return '';

            // type 1 = color
            if (data.type === 1) {
                return 'background: ' + data.value + ';';
            }

            // type 2 = image
            if (data.type === 2) {
                return 'background-image: url(' + data.value + '); background-size: cover;';
            }

            return '';
        },

        getSwatchClass(attributeCode, optionValue) {
            const data = this.getSwatchData(attributeCode, optionValue);
            if (!data) return 'text';

            if (data.type === 1) return 'color';
            if (data.type === 2) return 'image';
            return 'text';
        },

        getSwatchText(attributeCode, optionValue) {
            const data = this.getSwatchData(attributeCode, optionValue);
            if (!data) return '';

            if (data.type === 0) return data.value;

            return '';
        }

    }));
});
