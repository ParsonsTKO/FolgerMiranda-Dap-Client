# Initial variables
os ?= $(shell uname -s)

# Load custom setitngs
-include .env
export
PROVISION ?= docker
include etc/$(PROVISION)/makefile

install i: | build test open ## Perform install tasks (build, test, and open).. This is the default task

debug: | build xdebug test open ## Perform install tasks (build, updatedb, index, test, and open).

tag: ## Tag and push current branch. Usage make tag version=<semver>
	git tag -a $(version) -m "Version $(version)"
	git push origin $(version)

squash: branch := $(shell git rev-parse --abbrev-ref HEAD)
squash:
	git rebase -i $(shell git merge-base origin/$(branch) origin/master)
	git push -f

#publish: container ?= app
#publish: environment ?= Production
#publish: | build test release checknewrelease checkoutlatesttag ## Tag and deploy version. Registry authentication required. Usage: make publish
#	make deployimage
#	make deployservice
#	git checkout master

publish: container ?= app
publish: environment ?= Production
#publish: test release checkoutlatesttag deployimage
publish: ## Tag and deploy version. Registry authentication required. Usage: make publish
	make updateservice

preview review: container ?= app
preview review: version := $(shell git rev-parse --abbrev-ref HEAD)
preview review: | build test ## Tag, deploy and push image of the current branch. Update service. Registry authentication required. Usage: make review
	make deployimage
	make updateservice

push: branch := $(shell git rev-parse --abbrev-ref HEAD)
push: ## Review, add, commit and push changes using commitizen. Usage: make push
	git diff
	git add -A .
	@docker run --rm -it -e CUSTOM=true -v $(CURDIR):/app -v $(HOME)/.gitconfig:/root/.gitconfig aplyca/commitizen
	git pull origin $(branch)
	git push -u origin $(branch)

checknewrelease:
	git describe --tags --exact-match $(shell git rev-parse HEAD)

checkoutlatesttag:
	git fetch --prune origin "+refs/tags/*:refs/tags/*"
	git checkout $(shell git describe --always --abbrev=0 --tags)

test: | start codecept behat ## Run all tests.

h help: ## This help.
	@echo 'Usage: make <task>' 
	@echo 'Default task: install'
	@echo
	@echo 'Tasks:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9., _-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.DEFAULT_GOAL := install
.PHONY: all
