module.exports = {
  extends: [
    '@nextcloud/stylelint-config',
  ],
  rules: {
    indentation: 2,
    'selector-pseudo-element-no-unknown': [
      true, {
        ignorePseudoElements: ['v-deep'],
      },
    ],
  },
}
