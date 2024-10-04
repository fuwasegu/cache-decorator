# fuwasegu/composer-project-template
Composer プロジェクトのテンプレートです．PHP8.1 以上を想定しています．
それ以下のバージョンに対応するパッケージを開発する場合は，それぞれの依存ライブラリのバージョンを調節してください．
このテンプレートには以下の 3 つの開発用ライブラリが含まれています
- php-cs-fixer (設定項目は yumemi-inc/php-cs-fixer-config を利用)
  - 自動フォーマッタ
- phpstan
  - 静的解析（型検査）ライブラリ
- phpunit
  - テストフレームワーク

また，Coveralls (https://coveralls.io/) を使う前提で，ci も完備しています
.github/workflows/ci.yml の on を適切に設定してください．

# .github/workflows/ci.yml で設定が必要な項目
## 必須
- on

## 任意
- jobs.build.strategy.matrix.php
  - 対応する PHP のバージョンに依る

## Coveralls （カバレッジ管理サービス）を使わない方
ci.yml を以下のものに書き換えてください
```yaml
name: CI

on: []

jobs:
  build:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php: [8.1, 8.2]

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          coverage: xdebug
      - name: Composer install
        run: composer install
      - name: Lint
        run: composer lint
      - name: Static Analysis
        run: composer stan
      - name: Test
        run: vendor/bin/phpunit tests/ 
```

# composer.json で設定が必要な項目
- name
- description
- authors
- keywords
- autoload のパス
- autoload-dev のパス

