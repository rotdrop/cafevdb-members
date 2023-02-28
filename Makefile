# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
app_name=$(notdir $(CURDIR))
SRCDIR=.
ABSSRCDIR=$(CURDIR)
BUILDDIR=./build
ABSBUILDDIR=$(CURDIR)/build
build_tools_directory=$(BUILDDIR)/tools
COMPOSER_SYSTEM=$(shell which composer 2> /dev/null)
ifeq (, $(COMPOSER_SYSTEM))
COMPOSER_TOOL=php $(build_tools_directory)/composer.phar
else
COMPOSER_TOOL=$(COMPOSER_SYSTEM)
endif
COMPOSER_OPTIONS=--prefer-dist

MAKE_HELP_DIR = $(SRCDIR)/dev-scripts/MakeHelp
include $(MAKE_HELP_DIR)/MakeHelp.mk

all: dev-setup lint build-js-production test

# Dev env management
dev-setup: clean clean-dev app-toolkit composer npm-init

composer.json: composer.json.in
	cp composer.json.in composer.json

stamp.composer-core-versions: composer.lock
	date > stamp.composer-core-versions

composer.lock: DRY:=
composer.lock: composer.json composer.json.in
	rm -f composer.lock
	$(COMPOSER_TOOL) install $(COMPOSER_OPTIONS)
	env DRY=$(DRY) dev-scripts/tweak-composer-json.sh || {\
 rm -f composer.lock;\
 $(COMPOSER_TOOL) install $(COMPOSER_OPTIONS);\
}

.PHONY: comoser-download
composer-download:
	mkdir -p $(build_tools_directory)
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar $(build_tools_directory)

# Installs and updates the composer dependencies. If composer is not installed
# a copy is fetched from the web
.PHONY: composer
composer: stamp.composer-core-versions
	$(COMPOSER_TOOL) install $(COMPOSER_OPTIONS)

.PHONY: composer-suggest
composer-suggest:
	@echo -e "\n*** Regular Composer Suggestions ***\n"
	$(COMPOSER_TOOL) suggest --all

#
# Another namespace wrapper, but less complicated, in order to
# decouple our shared Nextcloud traits collection from other apps.
#

APP_TOOLKIT_DIR = $(ABSSRCDIR)/php-toolkit
APP_TOOLKIT_DEST = $(ABSSRCDIR)/lib/Toolkit
APP_TOOLKIT_NS = CAFeVDBMembers

include $(APP_TOOLKIT_DIR)/tools/scopeme.mk

npm-init: package.json webpack.config.js Makefile
	{ [ -d package-lock.json ] && [ test -d node_modules ]; } || npm install
	npm ci
	touch package-lock.json

npm-update:
	npm update

# Building
build-js:
	npm run dev

build-js-production:
	npm run build

watch-js:
	npm run watch

serve-js:
	npm run serve

# Linting
lint:
	npm run lint

lint-fix:
	npm run lint:fix

# Style linting
stylelint:
	npm run stylelint

stylelint-fix:
	npm run stylelint:fix

$(SRCDIR)/vendor/bin/phpcs: composer

PHPCS_IGNORE=lib/Database/ORM/Proxies/

.PHONY: phpcs
phpcs: $(SRCDIR)/vendor/bin/phpcs
	$(SRCDIR)/vendor/bin/phpcs --ignore=$(PHPCS_IGNORE) -v  --standard=.phpcs.xml lib/ templates/

.PHONY: phpcs-errors
phpcs-errors: $(SRCDIR)/vendor/bin/phpcs
	$(SRCDIR)/vendor/bin/phpcs --ignore=$(PHPCS_IGNORE) -n --standard=.phpcs.xml lib/ templates/|grep FILE:|awk '{ print $$2; }'

#@@ Removes WebPack builds
webpack-clean:
	rm -rf ./js/*
	rm -rf ./css/*
.PHONY: webpack-clean

#@@ Removes build files
clean: ## Tidy up local environment
	rm -rf $(BUILDDIR)
.PHONY: clean

#@@ Same as clean but also removes dependencies installed by composer, bower and npm
distclean: clean ## Clean even more, calls clean
	rm -rf vendor*
	rm -rf node_modules
	rm -rf lib/Toolkit/*
.PHONY: distclean

#@@ Really delete everything but the bare source files
realclean: distclean
	rm -f composer*.lock
	rm -f composer.json
	rm -f stamp.composer-core-versions
	rm -f package-lock.json
	rm -f *.html
	rm -f stats.json
.PHONY: realclean

clean-dev:
	rm -rf node_modules

# Tests
test:
	./vendor/phpunit/phpunit/phpunit -c phpunit.xml
	./vendor/phpunit/phpunit/phpunit -c phpunit.integration.xml
