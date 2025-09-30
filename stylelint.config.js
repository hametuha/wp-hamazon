module.exports = {
	extends: [
		'@wordpress/stylelint-config/scss',
	],
	rules: {
		'value-keyword-case': [ 'lower', {
			ignoreProperties: [ 'font-family' ],
		} ],
		'selector-class-pattern': null,
	},
};
