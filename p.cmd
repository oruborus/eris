@echo off
CLS
IF [%1] == [] GOTO ALL
"./vendor/bin/phpunit.bat" --testdox --filter %1 --no-logging
:ALL
"./vendor/bin/phpunit.bat" --testdox --coverage-text --coverage-html ./report && vendor\bin\phpstan analyse && vendor\bin\psalm --show-info=true
