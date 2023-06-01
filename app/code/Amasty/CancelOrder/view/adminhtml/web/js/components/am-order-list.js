/**
 *  Amasty Order List widget
 *
 *  @desc Amasty drag-n-drop functionality
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'collapsable'
], function ($) {
    'use strict';

    $.widget('mage.amOrderList', {
        options: {
            classes: {
                mageError: 'mage-error'
            },
            selectors: {
                items: '[data-amcorder-js="items"]',
                item: '[data-amcorder-js="item"]',
                input: '[data-amcorder-js="input"]',
                errorLabel: 'label.mage-error',
                addButton: '[data-amcorder-js="add"]',
                removeButton: '[data-amcorder-js="remove"]'
            },
            namePrefix: '',
            itemCount: null
        },
        nodes: {
            item: $('<li>', {
                'class': 'amcorder-item -draggable',
                'data-amcorder-js': 'item'
            }),
            label: $('<label>', {
                'class': 'amcorder-label'
            }),
            input: $('<input>', {
                'name': '',
                'class': 'required-entry',
                'data-amcorder-js': 'input',
                'type': 'text'
            }),
            removeButton: $('<button>', {
                'type': 'button',
                'class': 'amcorder-button -clear -delete',
                'data-amcorder-js': 'remove'
            }),
        },

        _create: function () {
            var self = this,
                options = self.options;

            self.items = self.element.find(options.selectors.items);
            self.addButton = self.element.find(options.selectors.addButton);

            options.itemCount = self.items.children().length;

            self.addButton.click(function () {
                self.addItem();
            });

            self.items.on('click', options.selectors.removeButton, function () {
                self.removeItem(this);
            });

            self.items.sortable({
                axis: 'y',
                items: options.selectors.item,
                tolerance: 'pointer',
                update: function () {
                    self._sorting();
                }
            });
        },

        /**
         * Removing target item from list
         *
         */
        removeItem: function (button) {
            var item = button.closest(this.options.selectors.item),
                index = $(item).index();

            this.options.itemCount--;

            item.remove();

            if (index !== this.options.itemCount) {
                this._sorting();
            }
        },

        /**
         * Adding new item to the bottom of the list
         *
         */
        addItem: function () {
            this.options.itemCount++;
            this.items.append(this._getItemNode());
        },

        /**
         * Setting all items names by self positions in the list
         *
         */
        _sorting: function () {
            var options = this.options;

            this.items.children().each(function (index, item) {
                var $item = $(item),
                    $input = $item.find(options.selectors.input);

                $item.find(options.selectors.errorLabel).remove();
                $input.attr({
                    'name': options.namePrefix.replace(/#/g, index + 1)
                });
                $input.removeClass(options.classes.mageError);

            });
        },

        /**
         *  Generate new item node with name of the position
         *
         *  @return {obj}
         */
        _getItemNode: function () {
            var item = this.nodes.item.clone(),
                label = this.nodes.label.clone(),
                input = this.nodes.input.clone(),
                removeButton = this.nodes.removeButton.clone();

            input.attr({
                'name': this.options.namePrefix.replace(/#/g, this.options.itemCount)
            });

            label.append(input);
            item.append(label, removeButton);

            return item
        }
    });

    return $.mage.amOrderList
});
