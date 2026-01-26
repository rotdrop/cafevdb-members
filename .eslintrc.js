module.exports = {
  extends: [
    '@nextcloud/eslint-config/typescript',
  ],
  settings: {
    'import/resolver': {
      typescript: {},
      node: {
        extensions: ['.js', '.jsx', '.d.ts', '.ts', '.tsx', '.vue'],
      },
    },
  },
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
      files: ['src/toolkit/**', 'webpack.config.js'],
      rules: {
        semi: ['error', 'always'],
      },
    },
    {
      files: ['*.ts', '*.cts', '*.mts', '*.tsx', '*.vue'],
      rules: {
        '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
        'import/no-unresolved': ['error', { ignore: ['cafevdbmembers'] }],
      },
    },
  ],
}
