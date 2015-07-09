#/!bin/bash
wget --no-check-certificate https://scrutinizer-ci.com/ocular.phar
if [ "$TRAVIS_PHP_VERSION" != "hhvm" ] && [ "$TRAVIS_PHP_VERSION" != "nightly" ];
	if ["$@" != ''];
		then
	fi;
	then
		php ocular.phar code-coverage:upload --repository=g/$@ --revision=`git rev-parse HEAD` --format=php-clover ./tests/_output/coverage.clover;
fi;
