define(
    ['jquery', 'underscore', 'backbone', 'oro/translator', 'routing', 'oro/mediator', 'oro/loading-mask', 'pimee/catalogrule/itemview'],
    function ($, _, Backbone, __, Routing, mediator, LoadingMask, ItemView) {
        'use strict';

        var templates = {
            'conditions': {
                'field':
                    '<div class="rule-item rule-condition">' +
                        '<span class="rule-item-emphasize">if</span>' +
                        '<span class="condition-field">' +
                            '<%= rulePart.field %>' +
                            '<%= renderItemContext(rulePart.locale, rulePart.scope) %>' +
                        '</span>' +
                        '<span class="rule-item-emphasize rule-operator"><%= rulePart.operator %></span>' +
                        '<% if (rulePart.operator != \'EMPTY\') { %>' +
                            '<span class="condition-value"><%= rulePart.value %></span>' +
                        '<% } %>' +
                    '</div>'
            },
            'actions': {
                'set_value':
                    '<div class="rule-item rule-action">' +
                        '<span class="rule-item-emphasize">then</span>' +
                        '<span class="action-field"><%= rulePart.value %></span>' +
                        '<span class="rule-item-emphasize">is set into</span>' +
                        '<span class="action-field">' +
                            '<%= rulePart.field %>' +
                            '<%= renderItemContext(rulePart.locale, rulePart.scope) %>' +
                        '</span>' +
                    '</div>',
                'copy_value':
                    '<div class="rule-item rule-action">' +
                        '<span class="rule-item-emphasize">then</span>' +
                        '<span class="action-field">' +
                            '<%= rulePart.from_field %>' +
                            '<%= renderItemContext(rulePart.from_locale, rulePart.from_scope) %>' +
                        '</span>' +
                        '<span class="rule-item-emphasize">is copied into</span>' +
                        '<span class="action-field">' +
                            '<%= rulePart.to_field %>' +
                            '<%= renderItemContext(rulePart.to_locale, rulePart.to_scope) %>' +
                        '</span>' +
                    '</div>'
            }
        };

        var itemContextTemplate =
            '<% if (localeCountry || scope) { %>' +
                '<span class="rule-item-context">' +
                    '<% if (localeCountry) { %>' +
                        '<span class="locale">' +
                            '<span class="flag-language">' +
                                '<i class="flag flag-<%= localeCountry %>"></i>' +
                            '</span>' +
                            '<%= localeLanguage %>' +
                        '</span>' +
                    '<% } %>' +
                    '<% if (scope) { %>' +
                        '<span class="scope">' +
                            '<%= scope %>' +
                        '</span>' +
                    '<% } %>' +
                '</span>' +
            '<% } %>';

        return ItemView.extend({
            className: 'rule-row',
            template: _.template(
                '<!-- PimEnterprise/Bundle/CatalogRuleBundle/Resources/public/js/ruleitemview.js -->' +
                '<td class="rule-cell rule-code"><%= rule.code %></td>' +
                '<td class="rule-cell rule-conditions">' +
                '<%= conditions %>' +
                '</td>' +
                '<td class="rule-cell rule-actions">' +
                '<%= actions %>' +
                '</td>' +
                '<td class="rule-cell">' +
                    '<span class="btn delete-row"><i class="icon-trash"></i> Delete</span>' +
                '</td>'
            ),
            renderRuleParts: function(type) {
                var renderedRuleParts = '';

                for (var key in this.model.attributes[type]) {
                    renderedRuleParts += this.renderRulePart(this.model.attributes[type][key], type);
                }

                return renderedRuleParts;
            },
            renderRulePart: function(rulePart, type) {
                var rulePartType = rulePart.type ? rulePart.type : 'field';

                return _.template(templates[type][rulePartType])({
                    'rulePart': rulePart,
                    'renderItemContext': function(locale, scope) {
                        var localeCountry  = locale ? locale.split('_')[1].toLowerCase() : locale;
                        var localeLanguage = locale ? locale.split('_')[0].toLowerCase() : locale;

                        return _.template(itemContextTemplate)({'localeCountry': localeCountry, 'localeLanguage': localeLanguage, 'scope': scope});
                    }
                });
            },
            renderTemplate: function() {
                var renderedConditions = this.renderRuleParts('conditions');
                var renderedActions    = this.renderRuleParts('actions');

                return this.template({
                    'rule':       this.model.toJSON(),
                    'conditions': renderedConditions,
                    'actions':    renderedActions
                });
            },
        });
    }
);
