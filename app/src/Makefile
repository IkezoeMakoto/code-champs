# Laravelプロジェクトのセットアップと管理のためのMakefile

# 変数
ARTISAN = php artisan
COMPOSER = composer

# デフォルトターゲット
.PHONY: all
all: setup

# Laravelセットアップ関連のターゲット
.PHONY: setup
setup: env composer-install key-generate migrate seed

.PHONY: env
env:
	cp -n src/.env.example src/.env || true

.PHONY: composer-install
composer-install:
	$(COMPOSER) install

.PHONY: key-generate
key-generate:
	$(ARTISAN) key:generate

.PHONY: migrate
migrate:
	$(ARTISAN) migrate

.PHONY: seed
seed:
	$(ARTISAN) db:seed
