module.exports = {
  extends: [
    '@nextcloud',
  ],
  rules: {
    'no-tabs': ['error', { allowIndentationTabs: false }],
    indent: ['error', 2],
    'no-mixed-spaces-and-tabs': 'error',
    'vue/html-indent': ['error', 2],
    // Do allow line-break before closing brackets
    'vue/html-closing-bracket-newline': ['error', { singleline: 'never', multiline: 'always' }],
    // space before self-closing elements
    'vue/html-closing-bracket-spacing': 'error',
  },
  overrides: [
    {
      files: ['src/toolkit/**'],
      rules: {
        semi: ['error', 'always'],
      },
    },
  ],
}
