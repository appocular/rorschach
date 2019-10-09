# I like the fish shell, and it has the very nice "string command", so
# why not use it?
SHELL=/usr/bin/fish

.PHONY: build release test watch-test

box.phar:
	wget https://github.com/humbug/box/releases/download/3.8.0/box.phar

build: box.phar
	php box.phar compile

release: test build
	@if string match -qrv '^[0-9]+\.[0-9]+\.[0-9]+$$' $(version); \
	  echo -e "\nPlease spercify version in X.Y.Z format\n"; \
          false; \
	end
	@if git rev-parse $(version) >/dev/null 2>&1; \
	  echo -e "\nVersion $(version) already exists\n"; \
          false; \
	end
	@echo "Updating readme"
	@sed -i -e 's/\\/[^/]*\\/rorschach.phar/\\/$(version)\\/rorshach.phar/' README.md
	@echo "Updating changlog"
	@sed -i -e '/## Unreleased/a \\\n## $(version) - $(shell date +%F)' CHANGELOG.md
	@echo "Tagging"
	@git add -u
	@git commit -m"Release $(version)"
	@git tag $(version)

test:
	@phpdbg -qrr ./vendor/bin/phpspec run -n -c .phpspec.coverage.yml

watch-test:
	while true; \
	  find . \( -name .git -o -name vendor \) -prune -o -name '*.php' -a -print | entr -cd make test; \
	end

clean:
	rm box.phar rorschach.phar
