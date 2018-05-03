
.PHONY: test

test:
	@phpdbg -qrr ./vendor/bin/phpspec run -c .phpspec.coverage.yml
