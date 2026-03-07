# プロジェクトのセットアップとDocker管理のためのMakefile

# 変数
DOCKER_COMPOSE = docker compose

# デフォルトターゲット
.PHONY: up
up:
	$(DOCKER_COMPOSE) up -d

.PHONY: setup
setup: build up app/setup

.PHONY: down
down:
	$(DOCKER_COMPOSE) down

.PHONY: build
build:
	$(DOCKER_COMPOSE) build
	$(MAKE) -C submission-executer build

.PHONY: app/setup
app/setup:
	docker compose exec app make setup
