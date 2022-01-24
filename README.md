# framework_cli
```
php.ini 파일에 phar.readonly를 off 해야함.

mkdir build
chmod -R 777 build
php build.php

ls -al ./build/cafelatte.phar



```


# php.ini
```
php.ini 

disable_function = shell_exec 제거

find ./generated-reversed-database/schema.xml -name "*.*" -exec sed -i "s/defaultPhpNamingMethod="underscore"/defaultPhpNamingMethod="underscore" namespace="PhpFramework\\\Model\\\CafelatteWww\"/g" {} \;
find ./generated-reversed-database/schema.xml -name "*.*" -exec sed -i "s/defaultPhpNamingMethod=\"underscore\"/defaultPhpNamingMethod=\"underscore\" namespace=\"PhpFramework\\\Model\"/g" {} \;
find ./generated-reversed-database/schema.xml -name "*.*" -exec sed -i "s/defaultPhpNamingMethod=\"underscore\"/defaultPhpNamingMethod="underscore" namespace="PhpFramework\\\Model\\\CafelatteWww"/g" {} \;
```