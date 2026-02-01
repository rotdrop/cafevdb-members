module.exports = {
  extends: [
    '@nextcloud',
    '@nextcloud/eslint-config/typescript',
  ],
  settings: {
    'import/resolver': {
      typescript: {},
      node: {
        extensions: [
          '.d.ts',
          '.js',
          '.jsx',
          '.ts',
          '.tsx',
          '.vue',
        ],
      },
    },
  },
  rules: {
    'no-tabs': [
      'error',
      {
        allowIndentationTabs: false,
      },
    ],
    indent: [
      'error',
      2,
    ],
    'no-mixed-spaces-and-tabs': 'error',
    'vue/html-indent': [
      'error',
      2,
    ],
    // Do allow line-break before closing brackets
    'vue/html-closing-bracket-newline': [
      'error',
      {
        multiline: 'always',
        singleline: 'never',
      },
    ],
    // space before self-closing elements
    'vue/html-closing-bracket-spacing': 'error',
  },
  overrides: [
    {
      files: [
        'build/ts-types/**',
        'src/toolkit/**',
        'webpack.config.js',
      ],
      rules: {
        semi: [
          'error',
          'always',
        ],
      },
    },
    {
      files: [
        '*.cts',
        '*.mts',
        '*.ts',
        '*.tsx',
        '*.vue',
      ],
      rules: {
        '@typescript-eslint/no-unused-vars': [
          'warn',
          {
            argsIgnorePattern: '^_',
          },
        ],
        // Note: you must disable the base rule as it can report incorrect errors
        'no-use-before-define': 'off',
        '@typescript-eslint/no-use-before-define': [
          'error',
          {
            functions: false,
            variables: false,
          },
        ],
        'import/no-unresolved': [
          'error',
          {
            ignore: [
              'cafevdbmembers',
            ],
          },
        ],
      },
    },
  ],
}
