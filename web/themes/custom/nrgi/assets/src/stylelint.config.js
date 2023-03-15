/**
 * @param {String} block
 * @param {Object} [presetOptions]
 * @param {String} [presetOptions.namespace]
 * @returns {RegExp}
 */
function bemSelector (block, presetOptions) {
    const ns        = (presetOptions && presetOptions.namespace) ? `${presetOptions.namespace}-` : '';
    const WORD      = '[.a-z0-9]+(?:-[a-z0-9]+)*';
    const element   = `(?:__${WORD})?`;
    const modifier  = `(?:--${WORD})?`; // Enforce double hyphen BEM style, only one modifier allowed.
    const attribute = '(?:\\[.+\\])?';

    // Custom
    const scssInterpolation = '#{|.+#{';

    return new RegExp(`${scssInterpolation}|^\\.${ns}${block}${element}${modifier}${attribute}$`);
}

module.exports = {
    extends: 'stylelint-config-standard-scss',
    plugins: [
        'stylelint-selector-bem-pattern',
    ],
    rules  : {
        // Stylelint rules
        'color-hex-case'                         : 'upper',
        'color-hex-length'                       : 'long',
        'declaration-colon-space-before'         : null,
        'declaration-empty-line-before'          : 'never',
        'selector-pseudo-element-colon-notation' : 'single',
        'scss/dollar-variable-colon-space-before': null,

        // BEM Linter plugin options.
        'plugin/selector-bem-pattern': {
            'preset': 'bem',

            // Components
            // 'implicitComponents': true,
            'componentSelectors': bemSelector,
            // 'componentName'     : '/^[-_a-zA-Z0-9]+$/',

            // Utilities
            'implicitUtilities': 'utilities.css',
            'utilitySelectors' : '^\\.u-[a-z]+$',

            // Ignore selectors
            ignoreSelectors: [
                'has-background',
                'has-image',
                'has-icon',
                'is-open',
                'is-closed',
                'ignore',
            ],
        },
    },
};
