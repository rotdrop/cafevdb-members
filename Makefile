# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
SRCDIR = .
ABSSRCDIR = $(CURDIR)
#
# try to parse the info.xml if we can, only then fall-back to the directory name
#
APP_INFO = $(SRCDIR)/appinfo/info.xml
XPATH = $(shell which xpath 2> /dev/null)
ifneq ($(XPATH),)
APP_NAME = $(shell $(XPATH) -q -e '/info/id/text()' $(APP_INFO))
APP_VERSION = $(shell $(XPATH) -q -e '/info/version/text()' $(APP_INFO))
APP_NAMESPACE = $(shell $(XPATH) -q -e '/info/namespace/text()' $(APP_INFO))
WRAPPER_NAMESPACE_POSTFIX = $(shell $(XPATH) -q -e '/info/scopednamespace/text()' $(APP_INFO))
else
$(warning The xpath binary could not be found, falling back to using the CWD as app-name)
APP_NAME = $(notdir $(CURDIR))
APP_VERSION = unknown
APP_NAMESPACE = $(shell grep -F '<namespace>' $(APP_INFO)|sed -E 's|.*<namespace>([^<]*)</namespace>.*|\\1|g')
endif
DEV_LIB_DIR = $(ABSSRCDIR)/dev-scripts/lib
BUILDDIR = ./build
ABSBUILDDIR = $(CURDIR)/build
BUILD_TOOLS_DIR = $(BUILDDIR)/tools
DOWNLOADS_DIR = ./downloads
DOC_BUILD_DIR = $(ABSBUILDDIR)/artifacts/doc
EMACS = $(shell which emacs 2> /dev/null)

MAKEFILE_DEP = Makefile

SILENT = @

# make these overridable from the command line
RSYNC = $(shell which rsync 2> /dev/null)
PHP = $(shell which php 2> /dev/null)
NPM = $(shell which npm 2> /dev/null)
WGET = $(shell which wget 2> /dev/null)
OPENSSL = $(shell which openssl 2> /dev/null)
PHPUNIT = ./vendor/bin/phpunit
ORM_CLI=$(PHP) $(SRCDIR)/dev-scripts/orm-cmd.php
PHP_SCOPER = $(ABSSRCDIR)/vendor-bin/php-scoper/vendor/bin/php-scoper
TYPESCRIPT_CONVERTER = $(ABSSRCDIR)/dev-scripts/php-to-typescript.php
TS_TYPES_DIR = $(ABSBUILDDIR)/ts-types
TS_PHP_SOURCE_DIRS = lib
SCSS_VARIABLES_DIR = $(ABSBUILDDIR)/scss-variables
#
PHPUNIT=$(ABSSRCDIR)/vendor-bin/phpunit/vendor/bin/phpunit
# PHPCOVERAGE = -d extension=pcov.so -d pcov.directory=$(ABSSRCDIR)/lib
PHPCOVERAGE = -d zend_extension=xdebug.so -d xdebug.mode=coverage
PHING=$(ABSSRCDIR)/vendor-bin/phing/vendor/bin/phing

COMPOSER_SYSTEM = $(shell which composer 2> /dev/null)
ifeq (, $(COMPOSER_SYSTEM))
COMPOSER = $(PHP) $(BUILD_TOOLS_DIR)/composer.phar
else
COMPOSER = $(COMPOSER_SYSTEM)
endif
COMPOSER_OPTIONS = --prefer-dist

ifeq ($(PHP),)
$(error PHP binary is needed, but could not be found and was not specified on the command-line)
endif
ifeq ($(NPM),)
$(error NPM binary is needed, but could not be found and was not specified on the command-line)
endif
ifeq ($(COMPOSER),)
$(error COMPOSER binary is needed, but could not be found and was not specified on the command-line)
endif
ifeq ($(WGET),)
$(error WGET binary is needed, but could not be found and was not specified on the command-line)
endif

PHPDOC = /opt/phpDocumentor/bin/phpdoc
export PHPDOC_PLANTUML_BIN = $(shell which plantuml 2> /dev/null)
# The default of phpdoc -Playout=smetana sometimes errors out, here it
# seems to work ...
export PHPDOC_PLANTUML_ARGUMENTS = -Playout=smetana
# The plantuml default layout engine dot takes a huge amount of time,
# more than 2 hours ...
export PHPDOC_PLANTUML_TIMEOUTS_SECONDS = 120
# ... we therefore disable graphs by default.
PHPDOC_GRAPHS ?= true
PHPDOC_TEMPLATE =

MAKE_HELP_DIR = $(SRCDIR)/dev-scripts/MakeHelp
include $(MAKE_HELP_DIR)/MakeHelp.mk

APPSTORE_BUILD_DIR = $(BUILDDIR)/artifacts/appstore
APPSTORE_COMPRESSION = z
APPSTORE_PACKAGE_FILE := $(APPSTORE_BUILD_DIR)/$(APP_NAME).tar
ifeq ($(APPSTORE_COMPRESSION),z)
  APPSTORE_PACKAGE_FILE := $(APPSTORE_PACKAGE_FILE).gz
else ifeq ($(APPSTORE_COMPRESSION),J)
  APPSTORE_PACKAGE_FILE := $(APPSTORE_PACKAGE_FILE).xz
endif
APPSTORE_SIGN_DIR = $(APPSTORE_BUILD_DIR)/sign
BUILD_CERT_DIR = $(BUILD_TOOLS_DIR)/certificates
CERT_DIR = $(HOME)/.nextcloud/certificates
OCC = $(CURDIR)/../../occ

#@@ The default rule.
all: help
.PHONY: all

#@@ Build the distribution assets (minified, without debugging info)
build: dev-setup npm-build
.PHONY: build

#@@ Build the development assets (include debugging information)
dev: dev-setup npm-dev
.PHONY: dev

#@private
dev-setup: dev-setup-php package-lock.json
.PHONY: dev-setup

#@private
dev-setup-php: app-toolkit composer.lock namespace-wrapper
.PHONY: dev-setup-php

include $(DEV_LIB_DIR)/makefile/composer.mk

$(PHING) $(PHPUNIT): composer.lock
	if ! [ -x $@ ]; then $(COMPOSER) bin phpunit install; else touch $@; fi

#@private
php-scoper-install: $(PHP_SCOPER)
.PHONY: php-scoper-install

$(PHP_SCOPER): composer.lock
	if ! [ -x "$@" ]; then $(COMPOSER) bin php-scoper install; else touch "$@"; fi

composer-wrapped.lock: composer-wrapped.json Makefile
	rm -f composer-wrapped.lock

$(BUILDDIR)/vendor-wrapped: composer-wrapped.lock
	mkdir -p $(BUILDDIR)
	ln -fs ../vendor $(BUILDDIR)
	rm -rf $(BUILDDIR)/vendor-wrapped
	ln -sf ../composer-patches $(BUILDDIR)
	env COMPOSER="$(ABSSRCDIR)/composer-wrapped.json" $(COMPOSER) -d$(BUILDDIR) install $(COMPOSER_OPTIONS)
	env COMPOSER="$(ABSSRCDIR)/composer-wrapped.json" $(COMPOSER) -d$(BUILDDIR) update $(COMPOSER_OPTIONS)

$(BUILDDIR)/vendor-wrapped/autoload.php: $(BUILDDIR)/vendor-wrapped composer-wrapped.json $(MAKEFILE_DEP)
	env COMPOSER="$(ABSSRCDIR)/composer-wrapped.json" $(COMPOSER) -d$(BUILDDIR) dump-autoload

vendor-wrapped: $(MAKEFILE_DEP) $(PHP_SCOPER) scoper.inc.php $(BUILDDIR)/vendor-wrapped
	$(PHP_SCOPER) add-prefix -d$(BUILDDIR) --config=$(ABSSRCDIR)/scoper.inc.php --output-dir=$(ABSSRCDIR)/vendor-wrapped --force
# scoper does not handle symlinks
	cp -a $(BUILDDIR)/vendor-wrapped/bin $(ABSSRCDIR)/vendor-wrapped/
# scoper does not preserve executable bits
	find $(ABSSRCDIR)/vendor-wrapped -name bin -a -type d -exec chmod -R gu+x {} \;

vendor-wrapped/autoload.php: vendor-wrapped
	env COMPOSER="$(ABSSRCDIR)/composer-wrapped.json" $(COMPOSER) dump-autoload

namespace-wrapper: php-scoper-install vendor-wrapped/autoload.php
.PHONY: namespace-wrapper

APP_TOOLKIT_DIR = $(ABSSRCDIR)/php-toolkit
APP_TOOLKIT_DEST = $(ABSSRCDIR)/lib/Toolkit
APP_TOOLKIT_NS = CAFeVDBMembers
APP_WRAPPER_NS = $(WRAPPER_NAMESPACE_POSTFIX)

include $(APP_TOOLKIT_DIR)/tools/scopeme.mk
include $(DEV_LIB_DIR)/makefile/ts-app-config.mk
include $(DEV_LIB_DIR)/makefile/ts-types-files.mk

JS_FILES = $(shell find $(ABSSRCDIR)/src -name "*.js" -o -name "*.vue" -o -name "*.ts")\
  $(shell find $(ABSSRCDIR)/3rdparty/rotdrop-nextcloud-vue-components -name "*.js" -o -name "*.vue" -o -name "*.ts")

NPM_INIT_DEPS =\
 package-lock.json package.json webpack.config.js .eslintrc.js $(MAKEFILE_DEP)

WEBPACK_DEPS =\
 $(NPM_INIT_DEPS)\
 $(CSS_FILES)\
 $(JS_FILES)\
 $(TS_APP_CONFIG)\
 $(SCSS_APP_CONFIG)\
 ts-types-files\
 scss-variables

include $(DEV_LIB_DIR)/makefile/npm.mk

#@@ Run phpcs on the PHP code
phpcs: composer
	vendor/bin/phpcs -s --report=emacs --standard=$(SRCDIR)/.phpcs.xml lib/ appinfo/ templates/

#@@ Run phpmd on the PHP code
phpmd: composer
	vendor/bin/phpmd lib/,appinfo/,templates/ text $(SRCDIR)/.phpmd.xml

# what has to be copied to the appstore archive
APPSTORE_FILES =\
 appinfo\
 css\
 js\
 img\
 l10n\
 templates\
 lib\
 vendor\
 config\
 CHANGELOG.md\
 COPYING\
 README.md

# .htaccess is blacklisted by the app-store installer, so we have to remove it
APPSTORE_BLACKLISTED = foobar .git* .*keep .htaccess *~

#@private
appstore: COMPOSER_OPTIONS := $(COMPOSER_OPTIONS) --no-dev
#@@ Prepare appstore archive
appstore: clean dev-setup npm-build
	mkdir -p $(APPSTORE_SIGN_DIR)/$(APP_NAME)
	$(RSYNC) -a -L $(APPSTORE_BLACKLISTED:%=--exclude '%') $(APPSTORE_FILES) $(APPSTORE_SIGN_DIR)/$(APP_NAME)
	mkdir -p $(BUILD_CERT_DIR)
	$(SILENT)if [ -n "$$APP_PRIVATE_KEY" ]; then\
  echo "$$APP_PRIVATE_KEY" > $(BUILD_CERT_DIR)/$(APP_NAME).key;\
elif [ -f "$(CERT_DIR)/$(APP_NAME).key" ]; then\
  cp $(CERT_DIR)/$(APP_NAME).key $(BUILD_CERT_DIR)/$(APP_NAME).key;\
fi
	$(SILENT)if [ -f $(BUILD_CERT_DIR)/$(APP_NAME).key ] && [ ! -f $(BUILD_CERT_DIR)/$(APP_NAME).crt ]; then\
  curl -L -o $(BUILD_CERT_DIR)/$(APP_NAME).crt\
 "https://github.com/nextcloud/app-certificate-requests/raw/master/$(APP_NAME)/$(APP_NAME).crt";\
  $(OPENSSL) x509 -in $(BUILD_CERT_DIR)/$(APP_NAME).crt -noout -text > /dev/null 2>&1 || rm -f $(BUILD_CERT_DIR)/$(APP_NAME).crt;\
fi
	$(SILENT)if [ -f $(BUILD_CERT_DIR)/$(APP_NAME).key ] && [ -f $(BUILD_CERT_DIR)/$(APP_NAME).crt ]; then\
  echo "Signing app files ...";\
  $(PHP) $(OCC) integrity:sign-app\
 --privateKey=$(ABSSRCDIR)/$(BUILD_CERT_DIR)/$(APP_NAME).key\
 --certificate=$(ABSSRCDIR)/$(BUILD_CERT_DIR)/$(APP_NAME).crt\
 --path=$(ABSSRCDIR)/$(APPSTORE_SIGN_DIR)/$(APP_NAME);\
  echo "... signing app files done";\
else\
  echo 'Cannot sign app-files, certificate "$(BUILD_CERT_DIR)/$(APP_NAME).crt" or private key "$(BUILD_CERT_DIR)/$(APP_NAME).key" not available.' 1>&2;\
fi
	tar -c$(APPSTORE_COMPRESSION)f $(APPSTORE_PACKAGE_FILE) -C $(APPSTORE_SIGN_DIR) $(APP_NAME)
	$(SILENT)if [ -f $(BUILD_CERT_DIR)/$(APP_NAME).key ] && [ -f $(BUILD_CERT_DIR)/$(APP_NAME).crt ]; then\
  echo "Signing package ...";\
  $(OPENSSL) dgst -sha512 -sign $(CERT_DIR)/$(APP_NAME).key $(APPSTORE_PACKAGE_FILE) | openssl base64; \
else\
  echo 'Cannot sign app-store package, certificate "$(BUILD_CERT_DIR)/$(APP_NAME).crt" or private key "$(BUILD_CERT_DIR)/$(APP_NAME).key" not available.' 1>&2;\
fi

.PHONY: appstore

#@@ Removes build files
clean: ## Tidy up local environment
	rm -rf $(BUILDDIR)
.PHONY: clean

#@@ Same as clean but also removes dependencies installed by composer, bower and npm
distclean: clean ## Clean even more, calls clean
	rm -rf vendor
	rm -rf vendor-bin/**/vendor
	rm -rf node_modules
	rm -rf lib/Toolkit/*
.PHONY: distclean

#@@ Almost everything but downloads
mostlyclean: webpack-clean distclean
	rm -f composer*.lock
	rm -f composer.json
	rm -f vendor-bin/**/composer.lock
	rm -f stamp.composer-core-versions
	rm -f package-lock.json
	rm -f *.html
	rm -f stats.json

#@@ Really delete everything but the bare source files
realclean: mostlyclean downloadsclean
.PHONY: realclean

#@@ Remove non-npm non-composer downloads
downloadsclean:
	rm -rf $(DOWNLOADS_DIR)
.PHONY: downloadsclean

PHPUNIT_OUTPUT=$(BUILDDIR)/artifacts/tests/phpunit
PHPUNIT_JUNIT_LOG=$(PHPUNIT_OUTPUT)/junit-log.xml
PHPUNIT_JUNIT_LOG_HTML=$(PHPUNIT_OUTPUT)/junit-log
PHING_BUILD_XML=$(PHPUNIT_OUTPUT)/phing-build.xml

#@@ Runs unit tests for PHP code
phpunit: dophpunit $(PHPUNIT_JUNIT_LOG_HTML)/index.html
.PHONY: phpunit

#@@ Post-process junit-log xml file
phpjunit-log: $(PHPUNIT_JUNIT_LOG_HTML)/index.html
.PHONY: phpjunit-log

dophpunit: $(PHPUNIT)
	$(PHP) $(PHPCOVERAGE) $(PHPUNIT)\
 -c phpunit.xml\
 --coverage-html $(PHPUNIT_OUTPUT)/code-coverage\
 --log-junit $(PHPUNIT_JUNIT_LOG)\
 --display-all-issues
#	$(PHPUNIT) -c phpunit.integration.xml
.PHONY: dophpunit

$(PHPUNIT_JUNIT_LOG): # dophpunit

$(PHING_BUILD_XML): $(SRCDIR)/vendor-bin/phing/phing-build.xml.in $(MAKEFILE_DEP)
	sed -e 's|%BASEDIR%|$(ABSSRCDIR)|g' -e 's|%INFILE%|$(PHPUNIT_JUNIT_LOG)|g' -e 's|%OUTPUTDIR%|$(PHPUNIT_OUTPUT)/junit-log|g' < $< > $@

$(PHPUNIT_JUNIT_LOG_HTML)/index.html: $(PHING_BUILD_XML) $(PHPUNIT_JUNIT_LOG) $(PHING)
	mkdir -p $(PHPUNIT_JUNIT_LOG_HTML)
	$(PHING) -f $(PHING_BUILD_XML)

#@@ Runs phpunit with a filter given by the PHPUNITTEST variable which should be specified on the command line.
phpunitfilter:
	@if [ -z "$(PHPUNITTEST)" ]; then echo "Please add PHPUNITTEST=FILTER_EXPRESSION to the make command line" 1>&2; exit 1; fi
	$(PHP) $(PHPCOVERAGE) $(PHPUNIT) -c phpunit.xml --no-coverage --display-all-issues --filter "$(PHPUNITTEST)"
.PHONY: phpunitfilter

#@private
run-tide:
	$(EMACS) --batch --file $(SRCDIR)/src/main.ts  -l $(DEV_LIB_DIR)/scripts/tide-project-errors.el|tee tide-errors.log
.PHONY: run-tide

#@@ Runs the Emacs Tide IDE in batch mode and diagnoses TypeScript errors.
tide: dev-setup ts-app-config ts-types-files run-tide
.PHONY: tide

.PHONY: verifydb
verifydb: $(ABSSRCDIR)/vendor
	$(ORM_CLI) orm:validate-schema

###############################################################################
#
# START DOCS

#@@ Build the documentation. May take a long time
doc: phpdoc # doxygen jsdoc
.PHONY: doc

PHPDOC_HTML = $(DOC_BUILD_DIR)/phpdoc/

#@@ Run phpDocumentor
phpdoc: $(PHPDOC_HTML)/index.html
.PHONY: phpdoc

#@private
$(PHPDOC_HTML)/index.html: $(APP_BUILD_HASH) $(MAKEFILE_DEP)
	$(MAKE) dev-setup-php
	rm -rf $(PHPDOC_HTML)
	mkdir -p $(PHPDOC_HTML)
	$(PHPDOC) run \
 $(PHPDOC_TEMPLATE) \
 -d $(SRCDIR)/lib \
 -d $(SRCDIR)/php-toolkit \
 -d $(SRCDIR)/tests/phpunit \
 --defaultpackagename $(APP_NAME) \
 --force \
 --parseprivate \
 --visibility api,public,protected,private,internal \
 --sourcecode \
 --setting graphs.enabled=$(PHPDOC_GRAPHS) \
 --cache-folder $(ABSBUILDDIR)/phpdoc/cache \
 -t $(PHPDOC_HTML)
